<?php
declare(strict_types=1);

namespace App\Event;

use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Notification Event Listener
 * 
 * Listens for post/comment events and creates/removes notifications accordingly.
 */
class NotificationListener implements EventListenerInterface
{
    use LocatorAwareTrait;

    /**
     * Register which events this listener handles
     */
    public function implementedEvents(): array
    {
        return [
            'Model.Post.liked' => 'onPostLiked',
            'Model.Post.unliked' => 'onPostUnliked',
            'Model.Post.commented' => 'onPostCommented',
            'Model.Comment.deleted' => 'onCommentDeleted',
        ];
    }

    /**
     * Create notification when a post is liked
     */
    public function onPostLiked(EventInterface $event): void
    {
        // Extract data passed from the controller
        $postId = $event->getData('post_id');
        $userId = $event->getData('user_id'); // The person who liked

        // Get table instances using LocatorAwareTrait
        $postsTable = $this->fetchTable('Posts');
        $notificationsTable = $this->fetchTable('Notifications');

        try {
            $post = $postsTable->get($postId);

            if ($post->user_id != $userId) {
                // Don't notify users about their own actions
                $notificationsTable->createNotification([
                    'user_id' => $post->user_id,
                    'type' => 'post_liked',
                    'actor_id' => $userId,
                    'target_type' => 'post',
                    'target_id' => $postId,
                    'is_read' => false
                ]);
            }
        } catch (\Exception $e) {
            error_log('Failed to create post_liked notification: ' . $e->getMessage());
        }
    }

    /**
     * Remove notification when a post is unliked
     */
    public function onPostUnliked(EventInterface $event): void
    {
        $postId = $event->getData('post_id');
        $userId = $event->getData('user_id');

        $notificationsTable = $this->fetchTable('Notifications');

        try {
            $notificationsTable->deleteAll([
                'actor_id' => $userId,
                'type' => 'post_liked',
                'target_type' => 'post',
                'target_id' => $postId
            ]);
        } catch (\Exception $e) {
            error_log('Failed to delete post_liked notification: ' . $e->getMessage());
        }
    }

    /**
     * Create notification when a post is commented on
     */
    public function onPostCommented(EventInterface $event): void
    {
        $postId = $event->getData('post_id');
        $userId = $event->getData('user_id');
        $commentId = $event->getData('comment_id');

        $postsTable = $this->fetchTable('Posts');
        $notificationsTable = $this->fetchTable('Notifications');

        try {
            $post = $postsTable->get($postId);

            if ($post->user_id != $userId) {
                $notificationsTable->createNotification([
                    'user_id' => $post->user_id,
                    'type' => 'post_commented',
                    'actor_id' => $userId,
                    'target_type' => 'post',
                    'target_id' => $postId,
                    'is_read' => false
                ]);
            }
        } catch (\Exception $e) {
            error_log('Failed to create post_commented notification: ' . $e->getMessage());
        }
    }

    /**
     * Remove notification when a comment is deleted
     */
    public function onCommentDeleted(EventInterface $event): void
    {
        $postId = $event->getData('post_id');
        $userId = $event->getData('user_id');

        $notificationsTable = $this->fetchTable('Notifications');

        try {
            $notificationsTable->deleteAll([
                'actor_id' => $userId,
                'type' => 'post_commented',
                'target_type' => 'post',
                'target_id' => $postId
            ]);
        } catch (\Exception $e) {
            error_log('Failed to delete post_commented notification: ' . $e->getMessage());
        }
    }
}
