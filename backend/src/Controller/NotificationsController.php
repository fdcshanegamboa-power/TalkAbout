<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Notifications Controller
 * 
 * Handles all notification-related actions including:
 * - Fetching notifications (all and unread)
 * - Marking notifications as read
 * - Getting unread count
 */
class NotificationsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * Helper method to get authenticated user ID
     * 
     * @return int|null
     */
    private function getAuthenticatedUserId(): ?int
    {
        $identity = $this->Authentication->getIdentity();
        
        if (!$identity) {
            return null;
        }

        if (method_exists($identity, 'getIdentifier')) {
            return $identity->getIdentifier();
        } elseif (method_exists($identity, 'get')) {
            return $identity->get('id');
        } elseif (isset($identity->id)) {
            return $identity->id;
        }

        return null;
    }

    /**
     * API: Get all notifications for the logged-in user
     * 
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        $userId = $this->getAuthenticatedUserId();
        
        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $notifications = $this->Notifications
            ->find('byUser', ['user_id' => $userId])
            ->limit(50) // Limit to last 50 notifications
            ->toArray();

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'notifications' => $notifications
        ]));
    }

    /**
     * API: Get unread notifications only
     * 
     * @return \Cake\Http\Response|null
     */
    public function unread()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        $userId = $this->getAuthenticatedUserId();
        
        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $notifications = $this->Notifications
            ->find('unread', ['user_id' => $userId])
            ->toArray();

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'notifications' => $notifications,
            'count' => count($notifications)
        ]));
    }

    /**
     * API: Get unread notification count
     * 
     * @return \Cake\Http\Response|null
     */
    public function count()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        $userId = $this->getAuthenticatedUserId();
        
        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $count = $this->Notifications->getUnreadCount($userId);

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'count' => $count
        ]));
    }

    /**
     * API: Mark a notification as read
     * 
     * @param int|null $id Notification ID
     * @return \Cake\Http\Response|null
     */
    public function markAsRead($id = null)
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is('post')) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

        $userId = $this->getAuthenticatedUserId();
        
        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        if (empty($id)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Notification ID is required'
            ]));
        }

        $result = $this->Notifications->markAsRead((int)$id, $userId);

        if ($result) {
            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Notification marked as read'
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to mark notification as read or notification not found'
            ]));
        }
    }

    /**
     * API: Mark all notifications as read for the logged-in user
     * 
     * @return \Cake\Http\Response|null
     */
    public function markAllAsRead()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is('post')) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

        $userId = $this->getAuthenticatedUserId();
        
        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $count = $this->Notifications->markAllAsRead($userId);

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'message' => 'All notifications marked as read',
            'count' => $count
        ]));
    }

    /**
     * API: Delete a notification
     * 
     * @param int|null $id Notification ID
     * @return \Cake\Http\Response|null
     */
    public function delete($id = null)
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is('post')) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

        $userId = $this->getAuthenticatedUserId();
        
        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        if (empty($id)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Notification ID is required'
            ]));
        }

        $notification = $this->Notifications->find()
            ->where([
                'id' => $id,
                'user_id' => $userId
            ])
            ->first();

        if (!$notification) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Notification not found'
            ]));
        }

        if ($this->Notifications->delete($notification)) {
            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Notification deleted'
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to delete notification'
            ]));
        }
    }
}
