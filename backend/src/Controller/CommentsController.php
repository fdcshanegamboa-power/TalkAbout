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

        // Validation
        if (empty($postId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post ID is required'
            ]));
        }

        if (empty($contentText)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Comment text is required'
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

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Comment added',
                'comment' => [
                    'id' => $comment->id,
                    'content_text' => $comment->content_text,
                    'created_at' => $comment->created_at
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
     * API: Delete a comment
     */
    public function deleteComment($id = null)
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

        if (empty($id)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Comment ID is required'
            ]));
        }

        $commentsTable = $this->getTableLocator()->get('Comments');
        
        // Find comment and verify ownership
        $comment = $commentsTable->find()
            ->where([
                'id' => $id,
                'user_id' => $userId,
                'deleted_at IS' => null
            ])
            ->first();

        if (!$comment) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Comment not found or you do not have permission to delete it'
            ]));
        }

        // Soft delete (set deleted_at timestamp)
        $comment->deleted_at = new \DateTime();
        
        if ($commentsTable->save($comment)) {
            // Dispatch event for notification cleanup
            $event = new Event('Model.Comment.deleted', $this, [
                'post_id' => $comment->post_id,
                'user_id' => $userId,
                'comment_id' => $comment->id
            ]);
            $this->getEventManager()->dispatch($event);

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Comment deleted'
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to delete comment'
            ]));
        }
    }
}
