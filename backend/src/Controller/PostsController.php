<?php
declare(strict_types=1);

namespace App\Controller;

class PostsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        // Allow unauthenticated users to view posts
        $this->Authentication->addUnauthenticatedActions(['view']);
    }

    /**
     * View a single post (public page)
     *
     * @param int|null $id Post ID
     * @return \
     */
    public function view($id = null)
    {
        if (empty($id)) {
            $this->render('not_found');
            return;
        }

        try {
            $postsTable = $this->getTableLocator()->get('Posts');
            $post = $postsTable->find()
                ->where(['Posts.id' => $id, 'Posts.deleted_at IS' => null])
                ->contain(['Users', 'PostImages'])
                ->first();

            if (!$post) {
                $this->render('not_found');
                return;
            }

            // Prepare images
            $images = [];
            if (!empty($post->post_images)) {
                foreach ($post->post_images as $img) {
                    $images[] = preg_match('/^https?:\/\//', $img->image_path) ? $img->image_path : '/img/posts/' . $img->image_path;
                }
            }

            // Load comments (with author data) and likes count
            $commentsTable = $this->getTableLocator()->get('Comments');
            $likesTable = $this->getTableLocator()->get('Likes');

            // Get current user id (needed to compute whether the current user liked comments)
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

            $commentsQuery = $commentsTable->find()
                ->where(['Comments.post_id' => $post->id, 'Comments.deleted_at IS' => null])
                ->contain(['Users'])
                ->order(['Comments.created_at' => 'DESC']);

            $comments = [];
            foreach ($commentsQuery as $c) {
                // Get like count for this comment
                $likeCount = $likesTable->find()
                    ->where([
                        'target_type' => 'comment',
                        'target_id' => $c->id
                    ])
                    ->count();

                // Check if current user liked this comment
                $commentLiked = false;
                if ($currentUserId) {
                    $commentLiked = $likesTable->exists([
                        'user_id' => $currentUserId,
                        'target_type' => 'comment',
                        'target_id' => $c->id
                    ]);
                }

                $comments[] = [
                    'user_id' => $c->user_id ?? ($c->user->id ?? null),
                    'id' => $c->id,
                    'author' => $c->user->full_name ?? $c->user->username,
                    'profile_photo' => $c->user->profile_photo_path ? (preg_match('/^https?:\/\//', $c->user->profile_photo_path) ? $c->user->profile_photo_path : '/img/profiles/' . $c->user->profile_photo_path) : null,
                    'initial' => strtoupper(substr(($c->user->full_name ?? $c->user->username ?? 'U'), 0, 1)),
                    'content_text' => $c->content_text,
                    'content_image_path' => $c->content_image_path,
                    'created_at' => $c->created_at,
                    'likes' => (int)$likeCount,
                    'liked' => (bool)$commentLiked
                ];
            }

            $likesCount = $likesTable->find()
                ->where(['target_type' => 'post', 'target_id' => $post->id])
                ->count();

            // Determine if the current user has liked this post

            $userLiked = false;
            if ($currentUserId) {
                $userLiked = $likesTable->exists([
                    'user_id' => $currentUserId,
                    'target_type' => 'post',
                    'target_id' => $post->id
                ]);
            }

            // Expose to view
            $author = $post->user;
            $this->set(compact('post', 'author', 'images', 'comments', 'likesCount', 'userLiked'));
        } catch (\Exception $e) {
            $this->render('not_found');
            return;
        }
    }

    /**
     * API: Get all posts for the feed
     */
    public function getPosts()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

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

        // Get pagination parameters from query string
        $offset = (int) $this->request->getQuery('offset', 0);
        $limit = (int) $this->request->getQuery('limit', 15);
        $limit = min($limit, 50); // Cap at 50 posts per request

        $postsTable = $this->getTableLocator()->get('Posts');
        $likesTable = $this->getTableLocator()->get('Likes');
        $commentsTable = $this->getTableLocator()->get('Comments');
        $friendshipsTable = $this->getTableLocator()->get('Friendships');
        
        // Get friend IDs for current user (if authenticated)
        $friendIds = [];
        if ($currentUserId) {
            $friendships = $friendshipsTable->getFriends($currentUserId)->toArray();
            foreach ($friendships as $friendship) {
                $friendIds[] = ($friendship->requester_id == $currentUserId) 
                    ? $friendship->addressee_id 
                    : $friendship->requester_id;
            }
        }
        
        // Get posts with user info and images, ordered by most recent first
        // Filter: public posts OR friends-only posts from friends OR own posts
        $query = $postsTable->find()
            ->contain(['Users', 'PostImages'])
            ->where(['Posts.deleted_at IS' => null])
            ->where(function ($exp) use ($currentUserId, $friendIds) {
                $conditions = $exp->eq('Posts.visibility', 'public');
                
                if ($currentUserId) {
                    // Show user's own posts regardless of visibility
                    $conditions = $exp->or([
                        $conditions,
                        ['Posts.user_id' => $currentUserId]
                    ]);
                    
                    // Show friends-only posts from friends
                    if (!empty($friendIds)) {
                        $conditions = $exp->or([
                            $conditions,
                            [
                                'Posts.visibility' => 'friends',
                                'Posts.user_id IN' => $friendIds
                            ]
                        ]);
                    }
                }
                
                return $conditions;
            });
        
        // Get total count for pagination
        $totalCount = $query->count();
        
        // Apply pagination
        $posts = $query
            ->order(['Posts.created_at' => 'DESC'])
            ->limit($limit)
            ->offset($offset)
            ->all();

        $result = [];
        foreach ($posts as $post) {
            $user = $post->user;
            $authorName = $user->full_name ?? $user->username ?? 'Unknown';
            $initial = strtoupper(substr($authorName, 0, 1));
            
            // Calculate relative time
            $createdAt = $post->created_at;
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

            // Collect all images
            $images = [];
            if (!empty($post->post_images)) {
                foreach ($post->post_images as $img) {
                    $images[] = preg_match('/^https?:\/\//', $img->image_path) ? $img->image_path : '/img/posts/' . $img->image_path;
                }
            }

            // Get like count for this post
            $likeCount = $likesTable->find()
                ->where([
                    'target_type' => 'post',
                    'target_id' => $post->id
                ])
                ->count();

            // Check if current user liked this post
            $userLiked = false;
            if ($currentUserId) {
                $userLiked = $likesTable->exists([
                    'user_id' => $currentUserId,
                    'target_type' => 'post',
                    'target_id' => $post->id
                ]);
            }

            // Get comment count for this post
            $commentCount = $commentsTable->find()
                ->where([
                    'post_id' => $post->id,
                    'deleted_at IS' => null
                ])
                ->count();

            $result[] = [
                'id' => $post->id,
                'user_id' => $user->id,
                'username' => $user->username ?? '',
                'author' => $authorName,
                'about' => $user->about ?? '',
                'initial' => $initial,
                'profile_photo' => $user->profile_photo_path ? (preg_match('/^https?:\/\//', $user->profile_photo_path) ? $user->profile_photo_path : '/img/profiles/' . $user->profile_photo_path) : '',
                'text' => $post->content_text ?? '',
                'images' => $images,
                'time' => $timeAgo,
                'likes' => $likeCount,
                'liked' => $userLiked,
                'comments' => $commentCount,
                'visibility' => $post->visibility ?? 'public',
            ];
        }

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'posts' => $result,
            'pagination' => [
                'offset' => $offset,
                'limit' => $limit,
                'total' => $totalCount,
                'hasMore' => ($offset + $limit) < $totalCount
            ]
        ]));
    }

    /**
     * API: Create a new post
     */
    public function createPost()
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

        $postsTable = $this->getTableLocator()->get('Posts');
        $postImagesTable = $this->getTableLocator()->get('PostImages');
        $usersTable = $this->getTableLocator()->get('Users');
        
        $contentText = $this->request->getData('content_text');
        $imageFiles = $this->request->getData('images');
        $visibility = $this->request->getData('visibility') ?? 'public';

        if (empty($contentText) && empty($imageFiles)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post must have text or images'
            ]));
        }

        // Validate visibility
        if (!in_array($visibility, ['public', 'friends'])) {
            $visibility = 'public';
        }

        $post = $postsTable->newEmptyEntity();
        $post->user_id = $userId;
        $post->content_text = $contentText;
        $post->visibility = $visibility;

        if (!$postsTable->save($post)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to create post'
            ]));
        }

        $uploadedImages = [];
        
        if (!empty($imageFiles) && is_array($imageFiles)) {
            if (!is_dir(WWW_ROOT . 'img' . DS . 'posts')) {
                mkdir(WWW_ROOT . 'img' . DS . 'posts', 0755, true);
            }

            $displayOrder = 0;
            foreach ($imageFiles as $imageFile) {
                if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
                    $fileType = $imageFile->getClientMediaType();
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        continue; // Skip invalid types
                    }

                    $fileSize = $imageFile->getSize();
                    if ($fileSize > 5 * 1024 * 1024) {
                        continue; // Skip files too large
                    }

                    $extension = pathinfo($imageFile->getClientFilename(), PATHINFO_EXTENSION);
                    $newFilename = uniqid('post_' . $post->id . '_') . '.' . $extension;
                    $targetPath = WWW_ROOT . 'img' . DS . 'posts' . DS . $newFilename;

                    try {
                        $imageFile->moveTo($targetPath);
                        
                        $postImage = $postImagesTable->newEmptyEntity();
                        $postImage->post_id = $post->id;
                        $postImage->image_path = $newFilename;
                        $postImage->display_order = $displayOrder++;
                        $postImagesTable->save($postImage);
                        
                        $uploadedImages[] = '/img/posts/' . $newFilename;
                    } catch (\Exception $e) {

                        continue;
                    }
                }
            }
        }
        
        $user = $usersTable->get($userId);
        $authorName = $user->full_name ?? $user->username ?? 'You';
        $initial = strtoupper(substr($authorName, 0, 1));

        // Broadcast new post to all connected users via WebSocket
        try {
            $wsClient = new \App\Utility\WebSocketClient();
            $broadcastResult = $wsClient->broadcastNewPost(
                $post->id,
                (int)$userId,
                $authorName
            );
            error_log("WebSocket broadcast new post - Post ID: {$post->id}, Author: {$authorName}, Result: " . ($broadcastResult ? 'success' : 'failed'));
        } catch (\Exception $e) {
            // WebSocket broadcast is not critical, continue
            error_log("Failed to broadcast new post: " . $e->getMessage());
        }

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'post' => [
                'id' => $post->id,
                'user_id' => $user->id,
                'username' => $user->username ?? '',
                'author' => $authorName,
                'about' => $user->about ?? '',
                'initial' => $initial,
                'profile_photo' => $user->profile_photo_path ? (preg_match('/^https?:\/\//', $user->profile_photo_path) ? $user->profile_photo_path : '/img/profiles/' . $user->profile_photo_path) : '',
                'text' => $post->content_text ?? '',
                'images' => $uploadedImages,
                'time' => 'Just now',
                'likes' => 0,
                'liked' => false,
                'visibility' => $post->visibility ?? 'public',
            ]
        ]));
    }

    /**
     * API: Update a post
     */
    public function updatePost()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is(['post', 'put', 'patch'])) {
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

        $postId = $this->request->getData('post_id');
        $contentText = $this->request->getData('content_text');
        $visibility = $this->request->getData('visibility');
        $imagesToDelete = $this->request->getData('images_to_delete');
        $newImages = $this->request->getData('new_images');

        if (empty($postId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post ID is required'
            ]));
        }

        $postsTable = $this->getTableLocator()->get('Posts');
        $postImagesTable = $this->getTableLocator()->get('PostImages');
        
        try {
            $post = $postsTable->get($postId);
            
            // Verify the post belongs to the current user
            if ($post->get('user_id') != $userId) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'You can only edit your own posts'
                ]));
            }

            // Update text content
            $post->content_text = $contentText;
            
            // Update visibility if provided
            if ($visibility !== null && in_array($visibility, ['public', 'friends'])) {
                $post->visibility = $visibility;
            }
            
            if (!$postsTable->save($post)) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to update post'
                ]));
            }

            // Handle image deletions
            if (!empty($imagesToDelete)) {
                $imagesToDeleteArray = is_string($imagesToDelete) ? json_decode($imagesToDelete, true) : $imagesToDelete;
                
                if (is_array($imagesToDeleteArray)) {
                    foreach ($imagesToDeleteArray as $imagePath) {
                        // Check if this is a URL or a local file path
                        $isUrl = preg_match('/^https?:\/\//', $imagePath);
                        
                        if ($isUrl) {
                            // For URLs, match the full image_path directly
                            $imageRecord = $postImagesTable->find()
                                ->where([
                                    'post_id' => $postId,
                                    'image_path' => $imagePath
                                ])
                                ->first();
                            
                            if ($imageRecord) {
                                $postImagesTable->delete($imageRecord);
                                // Don't try to delete physical file for URLs
                            }
                        } else {
                            // For local files, extract filename from path (e.g., "/img/posts/filename.jpg" -> "filename.jpg")
                            $filename = basename($imagePath);
                            
                            // Find and delete from database
                            $imageRecord = $postImagesTable->find()
                                ->where([
                                    'post_id' => $postId,
                                    'image_path' => $filename
                                ])
                                ->first();
                            
                            if ($imageRecord) {
                                $postImagesTable->delete($imageRecord);
                                
                                // Delete physical file only for local images
                                $filePath = WWW_ROOT . 'img' . DS . 'posts' . DS . $filename;
                                if (file_exists($filePath)) {
                                    unlink($filePath);
                                }
                            }
                        }
                    }
                }
            }

            // Handle new image uploads
            $uploadedImages = [];
            if (!empty($newImages) && is_array($newImages)) {
                // Create directory if it doesn't exist
                if (!is_dir(WWW_ROOT . 'img' . DS . 'posts')) {
                    mkdir(WWW_ROOT . 'img' . DS . 'posts', 0755, true);
                }

                // Get max display order
                $maxOrder = $postImagesTable->find()
                    ->where(['post_id' => $postId])
                    ->select(['max_order' => $postImagesTable->find()->func()->max('display_order')])
                    ->first();
                $displayOrder = ($maxOrder && isset($maxOrder->max_order)) ? $maxOrder->max_order + 1 : 0;

                foreach ($newImages as $imageFile) {
                    if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
                        $fileType = $imageFile->getClientMediaType();
                        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        
                        if (!in_array($fileType, $allowedTypes)) {
                            continue;
                        }

                        $fileSize = $imageFile->getSize();
                        if ($fileSize > 5 * 1024 * 1024) {
                            continue;
                        }

                        $extension = pathinfo($imageFile->getClientFilename(), PATHINFO_EXTENSION);
                        $newFilename = uniqid('post_' . $postId . '_') . '.' . $extension;
                        $targetPath = WWW_ROOT . 'img' . DS . 'posts' . DS . $newFilename;

                        try {
                            $imageFile->moveTo($targetPath);
                            
                            // Save to post_images table
                            $postImage = $postImagesTable->newEmptyEntity();
                            $postImage->post_id = $postId;
                            $postImage->image_path = $newFilename;
                            $postImage->display_order = $displayOrder++;
                            $postImagesTable->save($postImage);
                            
                            $uploadedImages[] = '/img/posts/' . $newFilename;
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }
            }

            // Get all current images for the post
            $currentImages = $postImagesTable->find()
                ->where(['post_id' => $postId])
                ->order(['display_order' => 'ASC'])
                ->all();
            
            $allImages = [];
            foreach ($currentImages as $img) {
                $allImages[] = preg_match('/^https?:\/\//', $img->image_path) ? $img->image_path : '/img/posts/' . $img->image_path;
            }

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'post' => [
                    'id' => $post->id,
                    'text' => $post->get('content_text'),
                    'visibility' => $post->get('visibility') ?? 'public',
                    'images' => $allImages,
                    'time' => 'Just now'
                ]
            ]));
        } catch (\Exception $e) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post not found: ' . $e->getMessage()
            ]));
        }
    }

    /**
     * API: Delete a post (soft delete)
     */
    public function deletePost()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is(['post', 'delete'])) {
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

        $postId = $this->request->getData('post_id');

        if (empty($postId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post ID is required'
            ]));
        }

        $postsTable = $this->getTableLocator()->get('Posts');
        
        try {
            $post = $postsTable->get($postId);
            
            if ($post->get('user_id') != $userId) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'You can only delete your own posts'
                ]));
            }

            $post->deleted_at = new \DateTime();
            
            if ($postsTable->save($post)) {
                return $this->response->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Post deleted successfully'
                ]));
            } else {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to delete post'
                ]));
            }
        } catch (\Exception $e) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post not found'
            ]));
        }
    }
}
