# Friendship Functionality - Backend Implementation

## Summary

I've successfully implemented a complete friendship system for your TalkAbout social media application. Here's what has been added:

## 1. Database Changes

### Added to `db/init-db.sql`:
- **friendships** table with the following structure:
  - `id`: Primary key
  - `requester_id`: User who sent the friend request
  - `addressee_id`: User who received the friend request
  - `status`: ENUM('pending', 'accepted', 'rejected', 'blocked')
  - `created_at`, `updated_at`: Timestamps
  - Foreign keys to users table
  - Unique constraint on (requester_id, addressee_id)
  - Indexes for performance optimization

## 2. Model Layer

### Entity: `backend/src/Model/Entity/Friendship.php`
- Defines accessible fields for mass assignment
- Associates with requester and addressee user records

### Table: `backend/src/Model/Table/FriendshipsTable.php`
- Validation rules for friendship data
- Business rules (prevents self-friending)
- Associations with Users table (Requester and Addressee)
- Helper methods:
  - `getFriendship($userId1, $userId2)` - Check if friendship exists
  - `getFriends($userId)` - Get all accepted friendships
  - `getPendingRequests($userId)` - Get incoming pending requests
  - `getSentRequests($userId)` - Get outgoing pending requests

## 3. Controller: `backend/src/Controller/FriendshipsController.php`

### API Endpoints:

#### POST Endpoints (Mutations):
1. **`/friendships/send-request`** - Send a friend request
   - Body: `{addressee_id: int}`
   - Returns: friendship_id on success
   - Events: Dispatches 'Model.Friendship.requested' for notifications

2. **`/friendships/accept-request`** - Accept a pending request
   - Body: `{friendship_id: int}`
   - Only addressee can accept
   - Events: Dispatches 'Model.Friendship.accepted' for notifications

3. **`/friendships/reject-request`** - Reject a pending request
   - Body: `{friendship_id: int}`
   - Only addressee can reject

4. **`/friendships/cancel-request`** - Cancel a sent request
   - Body: `{friendship_id: int}`
   - Only requester can cancel

5. **`/friendships/unfriend`** - Remove a friend
   - Body: `{friend_id: int}`
   - Either friend can unfriend

6. **`/friendships/block-user`** - Block a user
   - Body: `{block_user_id: int}`
   - Prevents future interactions

7. **`/friendships/unblock-user`** - Unblock a user
   - Body: `{unblock_user_id: int}`
   - Only blocker can unblock

#### GET Endpoints (Queries):
1. **`/friendships/get-requests`** - Get pending friend requests (incoming)
   - Returns: Array of requests with requester info

2. **`/friendships/get-sent-requests`** - Get sent pending requests
   - Returns: Array of requests with addressee info

3. **`/friendships/get-friends`** - Get friends list
   - Returns: Array of friends with user details

4. **`/friendships/get-status?user_id={id}`** or **`/friendships/get-status/{id}`**
   - Check friendship status with a specific user
   - Returns: status, friendship_id, is_requester

5. **`/friendships/get-blocked-users`** - Get list of blocked users
   - Returns: Array of blocked users

## Features Implemented:

✅ Send/accept/reject friend requests
✅ Cancel sent requests
✅ Unfriend functionality
✅ Block/unblock users
✅ Bidirectional friendship detection
✅ Prevent self-friending
✅ Prevent duplicate requests
✅ Event system integration for notifications
✅ Proper authentication checks
✅ Comprehensive error handling
✅ JSON API responses

## Next Steps (Optional):

1. **Update NotificationListener**: Handle friendship-related events
2. **Update Routes**: Ensure routes are configured for the new endpoints
3. **Frontend Integration**: Create UI components for friendship features
4. **Testing**: Test all endpoints with various scenarios
5. **Privacy Controls**: Optionally restrict posts/comments to friends only

## Database Migration:

To apply the changes, you'll need to:
```bash
# If using Docker:
docker-compose down
docker-compose up -d

# Or manually run the SQL:
mysql -u talkabout_user -p talkabout_db < db/init-db.sql
```

## Questions to Consider:

1. **Should friend requests expire** after a certain time period?
2. **Should there be a limit** on the number of pending requests a user can send?
3. **Should posts be visible only to friends**, or remain public?
4. **Should blocked users be invisible** to each other in search/profile views?
5. **Should there be mutual friend indicators** when viewing profiles?

All backend code is ready and follows your existing CakePHP patterns!
