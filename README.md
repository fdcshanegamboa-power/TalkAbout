# TalkAbout - Social Media Platform

A modern social media platform with CakePHP 5.0 RESTful API backend, Vue.js 3 frontend, and real-time WebSocket notifications.

## Tech Stack

**Backend:** CakePHP 5.0, PHP 8.2, MySQL 8.0, Session-based Authentication  
**Frontend:** Vue.js 3, Tailwind CSS 3, Cropper.js  
**Real-time:** Node.js 18, Socket.IO  
**Infrastructure:** Docker, Docker Compose, Nginx

RESTful API backend serving JSON responses consumed by Vue.js frontend components. Real-time WebSocket communication for live notifications and updates.

**Vue.js Components:**
- PostCard, PostComposer, Modal, RightSidebar mixins
- Reactive data binding, optimistic UI updates
- Client-side validation and image previews

## Key Features

**User Management:** Registration, login, profiles with photo upload/cropping, bio editing  
**Posts:** Text/image posts, edit/delete own posts, like/unlike, infinite scroll feed  
**Comments:** Add/delete comments with optional images, real-time updates  
**Friendships:** Send/accept/reject requests, unfriend, friend suggestions, blocking  
**Notifications:** Real-time WebSocket delivery for likes, comments, friend requests  
**Search:** User and post search functionality  
**Responsive:** Mobile-first design with pull-to-refresh, touch optimization

## Quick Start

### Prerequisites
- Docker Desktop
- Docker Compose

### Installation

```bash
# Navigate to project directory
cd /Users/shanegamboa-intern/Documents/TalkAbout

# Copy environment template
cp .env.example .env

# Start containers
docker-compose up -d

# Wait for services to initialize (~30 seconds)
```

Access the application at **http://localhost:8080**

Default credentials: `admin` / `admin123`

### Container Management

```bash
# View logs
docker logs talkabout-backend
docker logs talkabout-nginx
docker logs talkabout-websocket

# Access database
docker exec -it talkabout-db mysql -u talkabout_user -p

# Run CakePHP commands
docker exec -it talkabout-backend bin/cake [command]

# Stop containers
docker-compose down

# Restart services
docker-compose restart
```

## API Reference

### Authentication
- `POST /login` - User login
- `POST /register` - User registration
- `GET /logout` - User logout

### Posts
- `GET /api/posts/feed` - Paginated feed (offset/limit)
- `GET /api/posts/user/:username` - User's posts
- `GET /api/posts/view/:id` - Single post details
- `POST /api/posts/create` - Create post (multipart/form-data)
- `POST /api/posts/update` - Update post
- `POST /api/posts/delete` - Delete post
- `POST /api/posts/like` - Like post
- `POST /api/posts/unlike` - Unlike post

### Comments
- `GET /api/comments/list/:postId` - Get comments
- `POST /api/comments/add` - Add comment
- `POST /api/comments/delete/:id` - Delete comment

### Profile
- `GET /api/profile/user/:username` - Get profile
- `POST /api/profile/update` - Update profile
- `POST /api/profile/upload-photo` - Upload photo

### Friendships
- `GET /api/friendships/status/:userId` - Friendship status
- `GET /api/friendships/friends` - Friends list
- `GET /api/friendships/suggestions` - Friend suggestions
- `POST /api/friendships/send` - Send request
- `POST /api/friendships/accept` - Accept request
- `POST /api/friendships/reject` - Reject request
- `POST /api/friendships/cancel` - Cancel request
- `POST /api/friendships/unfriend` - Remove friendship

### Notifications
- `GET /api/notifications` - Get notifications
- `POST /api/notifications/mark-read` - Mark as read
- `POST /api/notifications/mark-all-read` - Mark all read

### Search
- `GET /api/search/users?q=query` - Search users
- `GET /api/search/posts?q=query` - Search posts

### WebSocket Events
- `authenticate` - Client sends `{ userId }`
- `notification` - Server sends notification object
- `new_post` - Server broadcasts `{ postId, authorName }`

## Database Schema

**Users:** Profile info, password hash (bcrypt), profile photo  
**Posts:** Content, visibility, counters, soft delete  
**PostImages:** Multiple images per post  
**Comments:** Content, optional image, soft delete  
**Friendships:** Status tracking (pending/accepted/rejected/blocked)  
**Notifications:** Type, read status, timestamps  
**Likes:** User-post associations

All tables include appropriate indexes on foreign keys and status columns.

## Development

### File Structure
```
backend/
  config/          # App configuration
  src/
    Controller/    # API controllers
    Model/         # Entities and Tables
    Utility/       # WebSocket client
  templates/       # PHP templates with Vue components
  webroot/
    js/            # Vue mixins and page scripts
    img/profiles/  # Uploaded images
websocket-server/  # Node.js Socket.IO server
db/                # Database initialization
nginx/             # Reverse proxy config
```

### Environment Variables (.env)
```
DB_HOST=db
DB_DATABASE=talkabout_db
DB_USERNAME=talkabout_user
DB_PASSWORD=talkabout@!password
SECURITY_SALT=your_random_salt_string
```

Never commit `.env` file to version control.

## Security & Performance

**Security Features:**
- Bcrypt password hashing
- Session-based authentication with CakePHP plugin
- CSRF protection
- SQL injection protection via ORM
- XSS protection through escaping
- File upload validation (size, type)
- Soft deletes for comment moderation

**Performance Optimizations:**
- Database indexing on foreign keys
- Pagination on all list endpoints
- Lazy loading with infinite scroll
- Client-side validation before upload
- ORM query caching
- WebSocket connection pooling

## Troubleshooting

**Database connection issues:**
```bash
docker ps                          # Check containers
docker logs talkabout-db           # Check MySQL logs
# Verify credentials in backend/config/app_local.php
```

**Permission errors:**
```bash
docker exec -it talkabout-backend chmod -R 777 tmp/ logs/
```

**WebSocket not connecting:**
```bash
docker ps | grep websocket
docker logs talkabout-websocket
curl http://localhost:3000/health
```

**Images not uploading:**
- Max file size: 5MB
- Allowed formats: JPEG, PNG, GIF, WebP
- Check permissions: `docker exec -it talkabout-backend ls -la webroot/img/profiles/`

## Browser Support

Chrome/Edge, Firefox, Safari (latest versions), iOS Safari, Chrome Mobile

Requires: ES6+, WebSocket, Fetch API, CSS Grid/Flexbox

## Known Limitations

- 5MB file upload limit
- WebSocket requires port 3000 accessible
- Session-based auth (not mobile-app friendly without modification)
- No email notifications
- Local storage for profile photos (no CDN)

## Future Enhancements

- Direct messaging
- Post sharing/reposting
- Hashtags and trending topics
- Email notifications
- Password reset
- Two-factor authentication
- Video uploads
- Redis session storage
- CDN integration
- API rate limiting

## License

Educational/development purposes.

## Resources

- [CakePHP 5 Documentation](https://book.cakephp.org/5/en/index.html)
- [Vue 3 Documentation](https://vuejs.org/)
- [Docker Documentation](https://docs.docker.com/)

