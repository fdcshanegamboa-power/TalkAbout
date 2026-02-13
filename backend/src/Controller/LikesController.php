<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\Event;

class LikesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * API: Like a post
     */
    public function likePost()
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

        $postId = $this->request->getData('post_id');

        if (empty($postId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post ID is required'
            ]));
        }

        $likesTable = $this->getTableLocator()->get('Likes');
        
        // Check if already liked
        $existingLike = $likesTable->find()
            ->where([
                'user_id' => $userId,
                'target_type' => 'post',
                'target_id' => $postId
            ])
            ->first();

        if ($existingLike) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post already liked'
            ]));
        }

        // Create new like
        $like = $likesTable->newEmptyEntity();
        $like->user_id = $userId;
        $like->target_type = 'post';
        $like->target_id = $postId;

        if ($likesTable->save($like)) {
            // Dispatch event for notification
            $event = new Event('Model.Post.liked', $this, [
                'post_id' => $postId,
                'user_id' => $userId
            ]);
            $this->getEventManager()->dispatch($event);

            // Get updated like count
            $likeCount = $likesTable->find()
                ->where([
                    'target_type' => 'post',
                    'target_id' => $postId
                ])
                ->count();

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'likes' => $likeCount
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to like post'
            ]));
        }
    }

    /**
     * API: Unlike a post
     */
    public function unlikePost()
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

        $postId = $this->request->getData('post_id');

        if (empty($postId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Post ID is required'
            ]));
        }

        $likesTable = $this->getTableLocator()->get('Likes');
        
        // Find the like
        $like = $likesTable->find()
            ->where([
                'user_id' => $userId,
                'target_type' => 'post',
                'target_id' => $postId
            ])
            ->first();

        if (!$like) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Like not found'
            ]));
        }

        if ($likesTable->delete($like)) {
            // Dispatch event for notification cleanup
            $event = new Event('Model.Post.unliked', $this, [
                'post_id' => $postId,
                'user_id' => $userId
            ]);
            $this->getEventManager()->dispatch($event);

            // Get updated like count
            $likeCount = $likesTable->find()
                ->where([
                    'target_type' => 'post',
                    'target_id' => $postId
                ])
                ->count();

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'likes' => $likeCount
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to unlike post'
            ]));
        }
    }
}
