<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\Event;

/**
 * Comments Controller
 */
class CommentsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        // Allow public read access to comments
        $this->Authentication->addUnauthenticatedActions(['getComments']);
    }

    /**
     * API: Add a comment to a post
     */
    public function addComment()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is('post')) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

        // Get authenticated user ID
        $identity = $this->Authentication->getIdentity();
        $userId = null;
        if ($identity) {
            if (method_exists($identity, 'getIdentifier')) {
                $userId = $identity->getIdentifier();
            } elseif (method_exists($identity, 'get')) {
                $userId = $identity->get('id');
            } elseif (isset($identity->id)) {
                $userId = $identity->id;
            }
        }

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $postId = $this->request->getData('post_id');
        $contentText = $this->request->getData('content_text');
        $imageFile = $this->request->getData('image');

        // Validation
        if (empty($postId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post ID is required'
            ]));
        }

        if (empty($contentText) && empty($imageFile)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Comment must have text or an image'
            ]));
        }

        $commentsTable = $this->getTableLocator()->get('Comments');
        $postsTable = $this->getTableLocator()->get('Posts');

        // Verify post exists
        $post = $postsTable->find()
            ->where(['id' => $postId, 'deleted_at IS' => null])
            ->first();

        if (!$post) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post not found'
            ]));
        }

        // Create comment
        $comment = $commentsTable->newEmptyEntity();
        $comment->post_id = $postId;
        $comment->user_id = $userId;
        $comment->content_text = $contentText;

        // Handle image upload (limit 1 image per comment)
        $uploadedImagePath = null;
        if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
            // Create directory if it doesn't exist
            if (!is_dir(WWW_ROOT . 'img' . DS . 'comments')) {
                mkdir(WWW_ROOT . 'img' . DS . 'comments', 0755, true);
            }

            $fileType = $imageFile->getClientMediaType();
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($fileType, $allowedTypes)) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Invalid image type. Only JPEG, PNG, GIF, and WebP are allowed.'
                ]));
            }

            $fileSize = $imageFile->getSize();
            if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Image size must be less than 5MB'
                ]));
            }

            $extension = pathinfo($imageFile->getClientFilename(), PATHINFO_EXTENSION);
            $newFilename = uniqid('comment_') . '.' . $extension;
            $targetPath = WWW_ROOT . 'img' . DS . 'comments' . DS . $newFilename;

            try {
                $imageFile->moveTo($targetPath);
                $uploadedImagePath = $newFilename;
                $comment->content_image_path = $uploadedImagePath;
            } catch (\Exception $e) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to upload image: ' . $e->getMessage()
                ]));
            }
        }

        if ($commentsTable->save($comment)) {
            // Dispatch event for notification
            $event = new Event('Model.Post.commented', $this, [
                'post_id' => $postId,
                'user_id' => $userId,
                'comment_id' => $comment->id
            ]);
            $this->getEventManager()->dispatch($event);

            // Get comment count
            $commentCount = $commentsTable->find()
                ->where([
                    'post_id' => $postId,
                    'deleted_at IS' => null
                ])
                ->count();

            // Get user info for the response
            $usersTable = $this->getTableLocator()->get('Users');
            $user = $usersTable->get($userId);
            $authorName = $user->full_name ?? $user->username ?? 'Unknown';
            $initial = strtoupper(substr($authorName, 0, 1));

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Comment added',
                'comment' => [
                    'id' => $comment->id,
                    'user_id' => $userId,
                    'author' => $authorName,
                    'initial' => $initial,
                    'profile_photo' => $user->profile_photo_path ?? '',
                    'content_text' => $comment->content_text,
                    'content_image_path' => $uploadedImagePath,
                    'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                    'time' => 'Just now',
                    'likes' => 0,
                    'liked' => false
                ],
                'comment_count' => $commentCount
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to add comment'
            ]));
        }
    }

    /**
     * API: Get comments for a post
     */
    public function getComments()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        // Get postId from passed arguments or request params
        $postId = null;
        $passedArgs = $this->request->getParam('pass');
        if (!empty($passedArgs) && isset($passedArgs[0])) {
            $postId = $passedArgs[0];
        }

        if (empty($postId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post ID is required'
            ]));
        }

        // Get current user ID
        $identity = $this->Authentication->getIdentity();
        $currentUserId = null;
        if ($identity) {
            if (method_exists($identity, 'getIdentifier')) {
                $currentUserId = $identity->getIdentifier();
            } elseif (method_exists($identity, 'get')) {
                $currentUserId = $identity->get('id');
            } elseif (isset($identity->id)) {
                $currentUserId = $identity->id;
            }
        }

        $commentsTable = $this->getTableLocator()->get('Comments');
        $usersTable = $this->getTableLocator()->get('Users');
        $likesTable = $this->getTableLocator()->get('Likes');

        // Get comments with user info, ordered by most recent first
        $comments = $commentsTable->find()
            ->contain(['Users'])
            ->where([
                'Comments.post_id' => $postId,
                'Comments.deleted_at IS' => null
            ])
            ->order(['Comments.created_at' => 'DESC'])
            ->all();

        $result = [];
        foreach ($comments as $comment) {
            $user = $comment->user;
            $authorName = $user->full_name ?? $user->username ?? 'Unknown';
            $initial = strtoupper(substr($authorName, 0, 1));
            
            // Calculate relative time
            $createdAt = $comment->created_at;
            $now = new \DateTime();
            $diff = $now->diff($createdAt);
            
            if ($diff->days > 0) {
                $timeAgo = $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
            } elseif ($diff->h > 0) {
                $timeAgo = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
            } elseif ($diff->i > 0) {
                $timeAgo = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
            } else {
                $timeAgo = 'Just now';
            }

            // Get like count for this comment
            $likeCount = $likesTable->find()
                ->where([
                    'target_type' => 'comment',
                    'target_id' => $comment->id
                ])
                ->count();

            // Check if current user liked this comment
            $userLiked = false;
            if ($currentUserId) {
                $userLiked = $likesTable->find()
                    ->where([
                        'user_id' => $currentUserId,
                        'target_type' => 'comment',
                        'target_id' => $comment->id
                    ])
                    ->count() > 0;
            }

            $result[] = [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'author' => $authorName,
                'initial' => $initial,
                'profile_photo' => $user->profile_photo_path ?? '',
                'content_text' => $comment->content_text ?? '',
                'content_image_path' => $comment->content_image_path ?? null,
                'time' => $timeAgo,
                'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                'likes' => $likeCount,
                'liked' => $userLiked
            ];
        }

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'comments' => $result,
            'count' => count($result)
        ]));
    }

    /**
     * API: Like a comment
     */
    public function likeComment()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is('post')) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

        $identity = $this->Authentication->getIdentity();
        $userId = null;
        if ($identity) {
            if (method_exists($identity, 'getIdentifier')) {
                $userId = $identity->getIdentifier();
            } elseif (method_exists($identity, 'get')) {
                $userId = $identity->get('id');
            } elseif (isset($identity->id)) {
                $userId = $identity->id;
            }
        }

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $commentId = $this->request->getData('comment_id');

        if (empty($commentId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Comment ID is required'
            ]));
        }

        $likesTable = $this->getTableLocator()->get('Likes');
        $commentsTable = $this->getTableLocator()->get('Comments');

        // Verify comment exists
        $comment = $commentsTable->find()
            ->where(['id' => $commentId, 'deleted_at IS' => null])
            ->first();

        if (!$comment) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Comment not found'
            ]));
        }

        // Check if already liked
        $existingLike = $likesTable->find()
            ->where([
                'user_id' => $userId,
                'target_type' => 'comment',
                'target_id' => $commentId
            ])
            ->first();

        if ($existingLike) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Comment already liked'
            ]));
        }

        // Create like
        $like = $likesTable->newEmptyEntity();
        $like->user_id = $userId;
        $like->target_type = 'comment';
        $like->target_id = $commentId;

        if ($likesTable->save($like)) {
            // Get updated like count
            $likeCount = $likesTable->find()
                ->where([
                    'target_type' => 'comment',
                    'target_id' => $commentId
                ])
                ->count();

            // Dispatch event so listeners (e.g. notifications) can react
            $event = new Event('Model.Comment.liked', $this, [
                'comment_id' => $commentId,
                'post_id' => $comment->post_id ?? null,
                'user_id' => $userId
            ]);
            $this->getEventManager()->dispatch($event);

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Comment liked',
                'likes' => $likeCount
            ]));
        }

        return $this->response->withStringBody(json_encode([
            'success' => false,
            'message' => 'Failed to like comment'
        ]));
    }

    /**
     * API: Unlike a comment
     */
    public function unlikeComment()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is('post')) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

        $identity = $this->Authentication->getIdentity();
        $userId = null;
        if ($identity) {
            if (method_exists($identity, 'getIdentifier')) {
                $userId = $identity->getIdentifier();
            } elseif (method_exists($identity, 'get')) {
                $userId = $identity->get('id');
            } elseif (isset($identity->id)) {
                $userId = $identity->id;
            }
        }

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $commentId = $this->request->getData('comment_id');

        if (empty($commentId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Comment ID is required'
            ]));
        }

        $likesTable = $this->getTableLocator()->get('Likes');

        // Find and delete the like
        $like = $likesTable->find()
            ->where([
                'user_id' => $userId,
                'target_type' => 'comment',
                'target_id' => $commentId
            ])
            ->first();

        if (!$like) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Like not found'
            ]));
        }

        if ($likesTable->delete($like)) {
            // Get updated like count
            $likeCount = $likesTable->find()
                ->where([
                    'target_type' => 'comment',
                    'target_id' => $commentId
                ])
                ->count();
            // Dispatch event for notification cleanup
            $event = new Event('Model.Comment.unliked', $this, [
                'comment_id' => $commentId,
                'post_id' => $comment->post_id ?? null,
                'user_id' => $userId
            ]);
            $this->getEventManager()->dispatch($event);

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Comment unliked',
                'likes' => $likeCount
            ]));
        }

        return $this->response->withStringBody(json_encode([
            'success' => false,
            'message' => 'Failed to unlike comment'
        ]));
    }
}
