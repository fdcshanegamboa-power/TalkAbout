# TalkAbout WebSocket Server

Real-time notification server for TalkAbout using Socket.io.

## Features

- Real-time notification delivery via WebSocket
- Automatic reconnection handling
- Support for multiple concurrent connections per user
- Efficient user-to-socket mapping

## Architecture

The WebSocket server runs as a separate Node.js service and receives notification events from the PHP backend. When a notification is created (e.g., someone likes a post), the backend calls the WebSocket server's HTTP API to emit the notification to the connected user in real-time.

## API Endpoints

### POST `/notify`
Emit a notification to a specific user.

**Request Body:**
```json
{
  "userId": 123,
  "notification": {
    "id": 456,
    "type": "post_liked",
    "actor_id": 789,
    "target_type": "post",
    "target_id": 111,
    "message": "...",
    "is_read": false,
    "created_at": "2026-02-19T...",
    "actor": {
      "id": 789,
      "username": "john",
      "full_name": "John Doe",
      "profile_photo": "..."
    }
  }
}
```

**Response:**
```json
{
  "success": true,
  "delivered": true,
  "socketCount": 2
}
```

### POST `/notify-count`
Emit a notification count update to a specific user.

**Request Body:**
```json
{
  "userId": 123,
  "count": 5
}
```

**Response:**
```json
{
  "success": true,
  "delivered": true
}
```

### GET `/health`
Health check endpoint.

**Response:**
```json
{
  "status": "ok",
  "connections": 42,
  "users": 38
}
```

## Socket.io Events

### Client → Server

#### `authenticate`
Authenticate the WebSocket connection with a user ID.

```javascript
socket.emit('authenticate', { userId: 123 });
```

#### `ping`
Keep-alive ping.

```javascript
socket.emit('ping');
```

### Server → Client

#### `authenticated`
Confirmation of successful authentication.

```javascript
socket.on('authenticated', (data) => {
  console.log('Authenticated:', data.userId, data.socketId);
});
```

#### `authError`
Authentication error.

```javascript
socket.on('authError', (data) => {
  console.error('Auth error:', data.message);
});
```

#### `notification`
New notification received.

```javascript
socket.on('notification', (notification) => {
  console.log('New notification:', notification);
});
```

#### `notificationCount`
Notification count update.

```javascript
socket.on('notificationCount', (data) => {
  console.log('Unread count:', data.count);
});
```

#### `pong`
Keep-alive pong response.

```javascript
socket.on('pong', () => {
  console.log('Pong received');
});
```

## Environment Variables

- `PORT` - Server port (default: 3000)
- `CORS_ORIGIN` - CORS origin for client connections (default: http://localhost)

## Docker Setup

The WebSocket server is automatically started via docker-compose:

```bash
docker compose up -d
```

The server will be available at:
- Internal: `http://websocket:3000`
- External (via nginx proxy): `http://localhost/socket.io/`

## Development

Install dependencies:
```bash
cd websocket-server
npm install
```

Run in development mode:
```bash
npm run dev
```

Run in production mode:
```bash
npm start
```

## Integration with PHP Backend

The PHP backend uses `App\Utility\WebSocketClient` to emit events to the WebSocket server:

```php
use App\Utility\WebSocketClient;

$wsClient = new WebSocketClient();

// Emit a notification
$wsClient->emitNotification($userId, $notificationData);

// Emit a count update
$wsClient->emitNotificationCount($userId, $count);
```

Events are automatically emitted by the `NotificationListener` when:
- A post is liked/unliked
- A comment is created/deleted
- A comment is liked/unliked
- A friend request is sent

## Client Integration

The frontend connects to the WebSocket server automatically when the navbar loads:

```javascript
// Connection is handled in navbar.js
const socket = io(window.location.origin, {
  path: '/socket.io',
  transports: ['websocket', 'polling']
});

// Authenticate with user ID
socket.emit('authenticate', { userId: currentUserId });

// Listen for notifications
socket.on('notification', (notification) => {
  // Handle new notification
});

socket.on('notificationCount', (data) => {
  // Update notification count badge
});
```

## Architecture Benefits

1. **Real-time**: Notifications appear instantly without polling
2. **Efficient**: Eliminates constant polling requests (was 1 request/second)
3. **Scalable**: WebSocket connections are lightweight and efficient
4. **Reliable**: Automatic reconnection handling
5. **Multi-device**: Users can be connected on multiple devices simultaneously

## Fallback Support

The implementation gracefully falls back to:
1. **Initial Load**: REST API is still used to load existing notifications on page load
2. **Polling Fallback**: Socket.io automatically falls back to long-polling if WebSocket is unavailable

## Monitoring

Check WebSocket server health:
```bash
curl http://localhost:3000/health
```

View logs:
```bash
docker compose logs -f websocket
```
