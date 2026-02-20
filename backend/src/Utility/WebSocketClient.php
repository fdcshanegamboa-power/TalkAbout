<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * WebSocket Client
 * 
 * Sends real-time events to the WebSocket server
 */
class WebSocketClient
{
    private string $websocketUrl;

    public function __construct()
    {
        // Default to docker service name
        $this->websocketUrl = \Cake\Core\env('WEBSOCKET_URL', 'http://websocket:3000');
    }

    /**
     * Emit a notification to a specific user
     *
     * @param int $userId The user ID to send the notification to
     * @param array $notification The notification data
     * @return bool Success status
     */
    public function emitNotification(int $userId, array $notification): bool
    {
        return $this->post('/notify', [
            'userId' => $userId,
            'notification' => $notification
        ]);
    }

    /**
     * Emit notification count update to a specific user
     *
     * @param int $userId The user ID
     * @param int $count The unread notification count
     * @return bool Success status
     */
    public function emitNotificationCount(int $userId, int $count): bool
    {
        return $this->post('/notify-count', [
            'userId' => $userId,
            'count' => $count
        ]);
    }

    /**
     * Broadcast new post to all connected users
     *
     * @param int $postId The post ID
     * @param int $authorId The author's user ID
     * @param string $authorName The author's name
     * @return bool Success status
     */
    public function broadcastNewPost(int $postId, int $authorId, string $authorName): bool
    {
        return $this->post('/broadcast-new-post', [
            'postId' => $postId,
            'authorId' => $authorId,
            'authorName' => $authorName
        ]);
    }

    /**
     * Send POST request to WebSocket server
     *
     * @param string $endpoint The endpoint path
     * @param array $data The data to send
     * @return bool Success status
     */
    private function post(string $endpoint, array $data): bool
    {
        try {
            $url = $this->websocketUrl . $endpoint;
            $jsonData = json_encode($data);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Short timeout
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $httpCode >= 200 && $httpCode < 300;
        } catch (\Exception $e) {
            // Fail silently - WebSocket is not critical
            error_log("WebSocket emit failed: " . $e->getMessage());
            return false;
        }
    }
}
