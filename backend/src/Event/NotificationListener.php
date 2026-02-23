<?php
declare(strict_types=1);

namespace App\Event;

use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Locator\LocatorAwareTrait;
use App\Utility\WebSocketClient;

/**
 * Notification Event Listener
 * 
 * Listens for post/comment events and creates/removes notifications accordingly.
 */
class NotificationListener implements EventListenerInterface
{
    use LocatorAwareTrait;

    private WebSocketClient $wsClient;

    public function __construct()
    {
        $this->wsClient = new WebSocketClient();
    }

    /**
     * Register which events this listener handles
     */
    public function implementedEvents(): array
    {
        return [
            'Model.Post.liked' => 'onPostLiked',
            'Model.Post.unliked' => 'onPostUnliked',
            'Model.Post.commented' => 'onPostCommented',
            'Model.Comment.liked' => 'onCommentLiked',
            'Model.Comment.unliked' => 'onCommentUnliked',
            'Model.Comment.deleted' => 'onCommentDeleted',
            'Model.Friendship.requested' => 'onFriendshipRequested',
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
            /** @var \App\Model\Entity\Post $post */
            $post = $postsTable->get($postId);

            if ($post->user_id != $userId) {
                // Don't notify users about their own actions
                $notification = $notificationsTable->createNotification([
                    'user_id' => $post->user_id,
                    'type' => 'post_liked',
                    'actor_id' => $userId,
                    'target_type' => 'post',
                    'target_id' => $postId,
                    'is_read' => false
                ]);

                if ($notification) {
                    // Emit real-time notification via WebSocket
                    $this->emitNotificationToUser($notification, $post->user_id);
                }
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

            // Get the post owner to emit count update
            $postsTable = $this->fetchTable('Posts');
            /** @var \App\Model\Entity\Post $post */
            $post = $postsTable->get($postId);
            $this->emitNotificationCountToUser($post->user_id);
        } catch (\Exception $e) {
            error_log('Failed to delete post_liked notification: ' . $e->getMessage());
        }
    }

    /**
     * Create notification when a comment is liked
     */
    public function onCommentLiked(EventInterface $event): void
    {
        $commentId = $event->getData('comment_id');
        $userId = $event->getData('user_id'); // person who liked

        $commentsTable = $this->fetchTable('Comments');
        $notificationsTable = $this->fetchTable('Notifications');

        try {
            /** @var \App\Model\Entity\Comment $comment */
            $comment = $commentsTable->get($commentId);

            if ($comment->user_id != $userId) {
                $notification = $notificationsTable->createNotification([
                    'user_id' => $comment->user_id,
                    'type' => 'comment_liked',
                    'actor_id' => $userId,
                    'target_type' => 'comment',
                    'target_id' => $commentId,
                    'is_read' => false
                ]);

                if ($notification) {
                    // Emit real-time notification via WebSocket
                    $this->emitNotificationToUser($notification, $comment->user_id);
                }
            }
        } catch (\Exception $e) {
            error_log('Failed to create comment_liked notification: ' . $e->getMessage());
        }
    }

    /**
     * Remove notification when a comment is unliked
     */
    public function onCommentUnliked(EventInterface $event): void
    {
        $commentId = $event->getData('comment_id');
        $userId = $event->getData('user_id');

        $notificationsTable = $this->fetchTable('Notifications');

        try {
            $notificationsTable->deleteAll([
                'actor_id' => $userId,
                'type' => 'comment_liked',
                'target_type' => 'comment',
                'target_id' => $commentId
            ]);

            // Get the comment owner to emit count update
            $commentsTable = $this->fetchTable('Comments');
            /** @var \App\Model\Entity\Comment $comment */
            $comment = $commentsTable->get($commentId);
            $this->emitNotificationCountToUser($comment->user_id);
        } catch (\Exception $e) {
            error_log('Failed to delete comment_liked notification: ' . $e->getMessage());
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
            /** @var \App\Model\Entity\Post $post */
            $post = $postsTable->get($postId);

            if ($post->user_id != $userId) {
                $notification = $notificationsTable->createNotification([
                    'user_id' => $post->user_id,
                    'type' => 'post_commented',
                    'actor_id' => $userId,
                    'target_type' => 'post',
                    'target_id' => $postId,
                    'is_read' => false
                ]);

                if ($notification) {
                    // Emit real-time notification via WebSocket
                    $this->emitNotificationToUser($notification, $post->user_id);
                }
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

            // Get the post owner to emit count update
            $postsTable = $this->fetchTable('Posts');
            /** @var \App\Model\Entity\Post $post */
            $post = $postsTable->get($postId);
            $this->emitNotificationCountToUser($post->user_id);
        } catch (\Exception $e) {
            error_log('Failed to delete post_commented notification: ' . $e->getMessage());
        }
    }

    /**
     * Create notification when a friend request is sent
     */
    public function onFriendshipRequested(EventInterface $event): void
    {
        $requesterId = $event->getData('requester_id');
        $addresseeId = $event->getData('addressee_id');
        $friendshipId = $event->getData('friendship_id');

        $notificationsTable = $this->fetchTable('Notifications');

        try {
            // Create notification for the addressee (person receiving the request)
            $notification = $notificationsTable->createNotification([
                'user_id' => $addresseeId,
                'type' => 'friend_request',
                'actor_id' => $requesterId,
                'target_type' => 'friendship',
                'target_id' => $friendshipId,
                'is_read' => false
            ]);

            if ($notification) {
                // Emit real-time notification via WebSocket
                $this->emitNotificationToUser($notification, $addresseeId);
            }
        } catch (\Exception $e) {
            error_log('Failed to create friend_request notification: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Emit notification to user via WebSocket
     */
    private function emitNotificationToUser($notification, int $userId): void
    {
        try {
            // Load actor information to include in the notification
            $usersTable = $this->fetchTable('Users');
            $actor = null;

            if (!empty($notification->actor_id)) {
                $actor = $usersTable->find()
                    ->select(['id', 'username', 'full_name', 'profile_photo_path'])
                    ->where(['id' => $notification->actor_id])
                    ->first();
            }

            $payload = [
                'id' => $notification->id,
                'type' => $notification->type,
                'actor_id' => $notification->actor_id,
                'target_type' => $notification->target_type,
                'target_id' => $notification->target_id,
                'message' => $notification->message,
                'is_read' => $notification->is_read,
                'created_at' => $notification->created_at,
                'actor' => $actor ? [
                    'id' => $actor->id,
                    'username' => $actor->username,
                    'full_name' => $actor->full_name,
                    'profile_photo' => $actor->profile_photo_path ? (preg_match('/^https?:\/\//', $actor->profile_photo_path) ? $actor->profile_photo_path : '/img/profiles/' . $actor->profile_photo_path) : '',
                ] : null,
            ];

            // Emit to WebSocket server
            $this->wsClient->emitNotification($userId, $payload);

            // Also emit updated count
            $this->emitNotificationCountToUser($userId);
        } catch (\Exception $e) {
            error_log('Failed to emit notification via WebSocket: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Emit notification count to user via WebSocket
     */
    private function emitNotificationCountToUser(int $userId): void
    {
        try {
            $notificationsTable = $this->fetchTable('Notifications');
            $count = $notificationsTable->getUnreadCount($userId);
            $this->wsClient->emitNotificationCount($userId, $count);
        } catch (\Exception $e) {
            error_log('Failed to emit notification count via WebSocket: ' . $e->getMessage());
        }
    }
}
