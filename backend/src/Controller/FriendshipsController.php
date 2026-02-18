<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\Event;
use Cake\Core\Configure;

class FriendshipsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * Friends list view page
     */
    public function friends()
    {
        $identity = $this->Authentication->getIdentity();
        $userId = $this->_getUserId($identity);

        if (empty($userId)) {
            return $this->redirect(['controller' => 'Sessions', 'action' => 'login']);
        }

        $this->set('currentUserId', $userId);
    }

    /**
     * API: Send a friend request
     */
    public function sendRequest()
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
        $userId = $this->_getUserId($identity);

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $addresseeId = $this->request->getData('addressee_id');

        if (empty($addresseeId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Addressee ID is required'
            ]));
        }

        // Prevent self-friending
        if ($userId == $addresseeId) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'You cannot send a friend request to yourself'
            ]));
        }

        $friendshipsTable = $this->getTableLocator()->get('Friendships');

        // Check if friendship already exists
        $existingFriendship = $friendshipsTable->getFriendship($userId, $addresseeId);

        if ($existingFriendship) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Friendship request already exists',
                'status' => $existingFriendship->status
            ]));
        }

        // Create new friend request
        $friendship = $friendshipsTable->newEmptyEntity();
        $friendship->requester_id = $userId;
        $friendship->addressee_id = $addresseeId;
        $friendship->status = 'pending';

        if ($friendshipsTable->save($friendship)) {
            // Dispatch event for notification
            $event = new Event('Model.Friendship.requested', $this, [
                'requester_id' => $userId,
                'addressee_id' => $addresseeId,
                'friendship_id' => $friendship->id
            ]);
            $this->getEventManager()->dispatch($event);

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Friend request sent successfully',
                'friendship_id' => $friendship->id
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to send friend request',
                'errors' => $friendship->getErrors()
            ]));
        }
    }

    /**
     * API: Accept a friend request
     */
    public function acceptRequest()
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
        $userId = $this->_getUserId($identity);

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $friendshipId = $this->request->getData('friendship_id');

        if (empty($friendshipId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Friendship ID is required'
            ]));
        }

        $friendshipsTable = $this->getTableLocator()->get('Friendships');
        $friendship = $friendshipsTable->get($friendshipId);

        // Verify current user is the addressee
        if ($friendship->addressee_id != $userId) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'You are not authorized to accept this request'
            ]));
        }

        // Verify status is pending
        if ($friendship->status !== 'pending') {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'This request cannot be accepted',
                'current_status' => $friendship->status
            ]));
        }

        // Update status to accepted
        $friendship->status = 'accepted';

        if ($friendshipsTable->save($friendship)) {
            // Dispatch event for notification
            $event = new Event('Model.Friendship.accepted', $this, [
                'requester_id' => $friendship->requester_id,
                'addressee_id' => $userId,
                'friendship_id' => $friendship->id
            ]);
            $this->getEventManager()->dispatch($event);

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Friend request accepted'
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to accept friend request'
            ]));
        }
    }

    /**
     * API: Reject a friend request
     */
    public function rejectRequest()
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
        $userId = $this->_getUserId($identity);

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $friendshipId = $this->request->getData('friendship_id');

        if (empty($friendshipId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Friendship ID is required'
            ]));
        }

        $friendshipsTable = $this->getTableLocator()->get('Friendships');
        $friendship = $friendshipsTable->get($friendshipId);

        // Verify current user is the addressee
        if ($friendship->addressee_id != $userId) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'You are not authorized to reject this request'
            ]));
        }

        // Update status to rejected
        $friendship->status = 'rejected';

        if ($friendshipsTable->save($friendship)) {
            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Friend request rejected'
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to reject friend request'
            ]));
        }
    }

    /**
     * API: Cancel a sent friend request
     */
    public function cancelRequest()
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
        $userId = $this->_getUserId($identity);

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $friendshipId = $this->request->getData('friendship_id');

        if (empty($friendshipId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Friendship ID is required'
            ]));
        }

        $friendshipsTable = $this->getTableLocator()->get('Friendships');
        $friendship = $friendshipsTable->get($friendshipId);

        // Verify current user is the requester
        if ($friendship->requester_id != $userId) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'You are not authorized to cancel this request'
            ]));
        }

        // Delete the friendship request
        if ($friendshipsTable->delete($friendship)) {
            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Friend request cancelled'
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to cancel friend request'
            ]));
        }
    }

    /**
     * API: Unfriend a user
     */
    public function unfriend()
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
        $userId = $this->_getUserId($identity);

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $friendId = $this->request->getData('friend_id');

        if (empty($friendId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Friend ID is required'
            ]));
        }

        $friendshipsTable = $this->getTableLocator()->get('Friendships');
        $friendship = $friendshipsTable->getFriendship($userId, $friendId);

        if (!$friendship) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Friendship not found'
            ]));
        }

        // Verify friendship is accepted
        if ($friendship->status !== 'accepted') {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'You are not friends with this user'
            ]));
        }

        // Delete the friendship
        if ($friendshipsTable->delete($friendship)) {
            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Friend removed successfully'
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to remove friend'
            ]));
        }
    }

    /**
     * API: Block a user
     */
    public function blockUser()
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
        $userId = $this->_getUserId($identity);

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $blockUserId = $this->request->getData('block_user_id');

        if (empty($blockUserId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User ID to block is required'
            ]));
        }

        $friendshipsTable = $this->getTableLocator()->get('Friendships');
        $friendship = $friendshipsTable->getFriendship($userId, $blockUserId);

        if ($friendship) {
            // Update existing friendship to blocked
            $friendship->status = 'blocked';
            // Ensure current user is the requester for the block
            if ($friendship->requester_id != $userId) {
                // Swap if needed - the blocker should be the requester
                $temp = $friendship->requester_id;
                $friendship->requester_id = $friendship->addressee_id;
                $friendship->addressee_id = $temp;
            }
        } else {
            // Create new blocked relationship
            $friendship = $friendshipsTable->newEmptyEntity();
            $friendship->requester_id = $userId;
            $friendship->addressee_id = $blockUserId;
            $friendship->status = 'blocked';
        }

        if ($friendshipsTable->save($friendship)) {
            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'User blocked successfully'
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to block user'
            ]));
        }
    }

    /**
     * API: Unblock a user
     */
    public function unblockUser()
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
        $userId = $this->_getUserId($identity);

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $unblockUserId = $this->request->getData('unblock_user_id');

        if (empty($unblockUserId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User ID to unblock is required'
            ]));
        }

        $friendshipsTable = $this->getTableLocator()->get('Friendships');
        $friendship = $friendshipsTable->getFriendship($userId, $unblockUserId);

        if (!$friendship || $friendship->status !== 'blocked') {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User is not blocked'
            ]));
        }

        // Verify current user is the one who blocked
        if ($friendship->requester_id != $userId) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'You cannot unblock this user'
            ]));
        }

        // Delete the block
        if ($friendshipsTable->delete($friendship)) {
            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'User unblocked successfully'
            ]));
        } else {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Failed to unblock user'
            ]));
        }
    }

    /**
     * API: Get pending friend requests
     */
    public function getRequests()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is('get')) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

        $identity = $this->Authentication->getIdentity();
        $userId = $this->_getUserId($identity);

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $friendshipsTable = $this->getTableLocator()->get('Friendships');
        $requests = $friendshipsTable->getPendingRequests($userId)->toArray();

        $formattedRequests = [];
        foreach ($requests as $request) {
            $formattedRequests[] = [
                'id' => $request->id,
                'requester_id' => $request->requester_id,
                'requester_username' => $request->requester->username,
                'requester_full_name' => $request->requester->full_name,
                'requester_profile_photo' => $request->requester->profile_photo_path,
                'created_at' => $request->created_at,
            ];
        }

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'requests' => $formattedRequests
        ]));
    }

    /**
     * API: Get sent friend requests
     */
    public function getSentRequests()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is('get')) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

        $identity = $this->Authentication->getIdentity();
        $userId = $this->_getUserId($identity);

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $friendshipsTable = $this->getTableLocator()->get('Friendships');
        $requests = $friendshipsTable->getSentRequests($userId)->toArray();

        $formattedRequests = [];
        foreach ($requests as $request) {
            $formattedRequests[] = [
                'id' => $request->id,
                'addressee_id' => $request->addressee_id,
                'addressee_username' => $request->addressee->username,
                'addressee_full_name' => $request->addressee->full_name,
                'addressee_profile_photo' => $request->addressee->profile_photo_path,
                'created_at' => $request->created_at,
            ];
        }

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'requests' => $formattedRequests
        ]));
    }

    /**
     * API: Get friends list
     */
    public function getFriends()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is('get')) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

        $identity = $this->Authentication->getIdentity();
        $userId = $this->_getUserId($identity);

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $friendshipsTable = $this->getTableLocator()->get('Friendships');
        $friendships = $friendshipsTable->getFriends($userId)->toArray();

        $friends = [];
        foreach ($friendships as $friendship) {
            // Determine which user is the friend (not the current user)
            $friend = ($friendship->requester_id == $userId) 
                ? $friendship->addressee 
                : $friendship->requester;

            $friends[] = [
                'friendship_id' => $friendship->id,
                'friend_id' => $friend->id,
                'username' => $friend->username,
                'full_name' => $friend->full_name,
                'profile_photo' => $friend->profile_photo_path,
                'friends_since' => $friendship->updated_at ?? $friendship->created_at,
            ];
        }

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'friends' => $friends
        ]));
    }

    /**
     * API: Get friendship status with a specific user
     */
    public function getStatus($otherUserId = null)
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        try {
            if (!$this->request->is('get')) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Invalid request method'
                ]));
            }

            $identity = $this->Authentication->getIdentity();
            $userId = $this->_getUserId($identity);

            if (empty($userId)) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'User not authenticated'
                ]));
            }

            // Get otherUserId from passed parameters (URL path)
            if (empty($otherUserId)) {
                $passedArgs = $this->request->getParam('pass', []);
                $otherUserId = !empty($passedArgs) ? $passedArgs[0] : null;
            }

            // If still not in URL params, try query string
            if (empty($otherUserId)) {
                $otherUserId = $this->request->getQuery('user_id');
            }

            if (empty($otherUserId)) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Other user ID is required'
                ]));
            }

            // Cast to int for type safety
            $otherUserId = (int)$otherUserId;
            $userId = (int)$userId;

            $friendshipsTable = $this->getTableLocator()->get('Friendships');
            $friendship = $friendshipsTable->getFriendship($userId, $otherUserId);

            if (!$friendship) {
                return $this->response->withStringBody(json_encode([
                    'success' => true,
                    'status' => 'none',
                    'friendship_id' => null
                ]));
            }

            // Determine relationship direction
            $isRequester = ($friendship->requester_id == $userId);

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'status' => $friendship->status,
                'friendship_id' => $friendship->id,
                'is_requester' => $isRequester,
                'created_at' => $friendship->created_at,
                'updated_at' => $friendship->updated_at
            ]));
        } catch (\Exception $e) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'error_trace' => Configure::read('debug') ? $e->getTraceAsString() : null
            ]));
        }
    }

    /**
     * API: Get blocked users list
     */
    public function getBlockedUsers()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');

        if (!$this->request->is('get')) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]));
        }

        $identity = $this->Authentication->getIdentity();
        $userId = $this->_getUserId($identity);

        if (empty($userId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'User not authenticated'
            ]));
        }

        $friendshipsTable = $this->getTableLocator()->get('Friendships');
        $blocked = $friendshipsTable->find()
            ->where([
                'requester_id' => $userId,
                'status' => 'blocked'
            ])
            ->contain(['Addressee'])
            ->toArray();

        $blockedUsers = [];
        foreach ($blocked as $block) {
            $blockedUsers[] = [
                'friendship_id' => $block->id,
                'user_id' => $block->addressee->id,
                'username' => $block->addressee->username,
                'full_name' => $block->addressee->full_name,
                'profile_photo' => $block->addressee->profile_photo_path,
                'blocked_at' => $block->updated_at ?? $block->created_at,
            ];
        }

        return $this->response->withStringBody(json_encode([
            'success' => true,
            'blocked_users' => $blockedUsers
        ]));
    }

    /**
     * Helper method to extract user ID from identity
     */
    private function _getUserId($identity)
    {
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
}
