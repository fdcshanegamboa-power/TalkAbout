# TalkAbout - CakePHP Backend with Vue Frontend

A modern web application built with CakePHP 5.0 backend and Vue 3 (CDN) frontend, featuring user authentication (login and registration).

## Tech Stack

- **Backend**: CakePHP 5.0 (MVC Architecture)
- **Frontend**: Vue 3 (CDN) with Tailwind CSS
- **Database**: MySQL 8.0
- **Web Server**: Nginx
- **PHP**: 8.2 (PHP-FPM)
- **Containerization**: Docker & Docker Compose

## Features

- ✅ User Registration
- ✅ User Login & Logout
- ✅ Session-based Authentication
- ✅ Password Hashing (bcrypt)
- ✅ Form Validation
- ✅ Responsive Design with Tailwind CSS
- ✅ Vue 3 Interactive Components
- ✅ Dashboard

## Project Structure

```
TalkAbout/
├── backend/                    # CakePHP application
│   ├── config/                 # Configuration files
│   │   ├── app.php            # Main application config
│   │   ├── app_local.php      # Local environment config
│   │   ├── bootstrap.php      # Bootstrap file
│   │   ├── routes.php         # Route definitions
│   ├── src/                   # Application source code
│   │   ├── Application.php    # Application class
│   │   ├── Controller/        # Controllers
│   │   ├── Model/             # Models (Entity & Table)
│   │   └── View/              # View helpers
│   ├── templates/             # View templates
│   │   ├── layout/            # Layout templates
│   │   └── Users/             # User views (login, register, dashboard)
│   ├── webroot/               # Public web root
│   └── composer.json          # PHP dependencies
├── nginx/                     # Nginx configuration
├── php/                       # PHP configuration & Dockerfile
├── db/                        # Database initialization scripts
└── Docker-compose.yaml        # Docker orchestration
```

## Prerequisites

- Docker Desktop
- Docker Compose

## Installation & Setup

### 1. Clone or Navigate to the Project

```bash
cd /Users/shanegamboa-intern/Documents/TalkAbout
```

### 2. Start Docker Containers

```bash
docker-compose up -d
```

This will start:
- Nginx web server (port 80)
- CakePHP backend (PHP-FPM)
- MySQL database (port 3306)

### 3. Install Dependencies

```bash
docker exec -it talkabout-backend composer install
```

### 4. Initialize the Database Schema & Seed Data

The MySQL container automatically executes `db/init-db.sql` the first time it starts, creating the `users` table and seeding default accounts. If you need to re-run the script manually (for example, after wiping the database volume), run:

```bash
docker exec -i talkabout-db mysql -uroot -ptalkabout@!password talkabout_db < /docker-entrypoint-initdb.d/init.sql
```

This ensures the schema is provisioned and the default admin/test credentials are always available.

### 5. Set Permissions (if needed)

```bash
docker exec -it talkabout-backend chmod -R 777 tmp/ logs/
```

### 6. Access the Application

Open your browser and navigate to:
```
http://localhost
```

You'll be redirected to the login page.

### Default Login Credentials

The init script seeds two accounts (use email + password to sign in):
- `admin@talkabout.local` / `iZE^Sb7?GszRGA`
- `test@talkabout.local` / `!mylo803OeCilR`

## Usage

### Register a New Account

1. Navigate to `http://localhost/register`
2. Fill in the registration form:
   - Username (unique)
   - Email (unique, valid email format)
   - Password (minimum 8 characters)
   - Confirm Password
3. The form includes real-time validation:
   - Password strength meter
   - Password match confirmation
   - Form validity checking
4. Click "Create Account"
5. Upon success, you'll be redirected to the login page

### Login

1. Navigate to `http://localhost/login`
2. Enter your email and password
3. Click "Sign In"
4. Upon success, you'll be redirected to the dashboard

### Dashboard

After logging in, you'll see:
- Welcome message with your username
- User statistics cards (Messages, Conversations, Notifications)
- Recent activity feed (with sample data option)
- Logout button

## API Endpoints

| Method | Endpoint | Description | Authentication |
|--------|----------|-------------|----------------|
| GET/POST | `/login` | User login | Public |
| GET/POST | `/register` | User registration | Public |
| GET | `/logout` | User logout | Required |
| GET | `/dashboard` | User dashboard | Required |

## Database Configuration

The database connection is configured in `backend/config/app_local.php`:

```php
'Datasources' => [
    'default' => [
        'host' => 'db',
        'port' => '3306',
        'username' => 'talkabout_user',
        'password' => 'talkabout@!password',
        'database' => 'talkabout_db',
    ],
],
```

## Vue 3 Features

The frontend uses Vue 3 via CDN with the following features:

### Login Page
- Reactive form data binding
- Loading states
- Form submission handling

### Register Page
- Real-time password strength validation
- Password match confirmation
- Form validation
- Dynamic error messages
- Visual feedback (colors, progress bars)

### Dashboard
- User information display
- Interactive statistics cards
- Dynamic activity feed
- Sample data loading

## Development

### View Logs

**Backend logs:**
```bash
docker logs talkabout-backend
```

**Nginx logs:**
```bash
docker logs talkabout-nginx
```

**Database logs:**
```bash
docker logs talkabout-db
```

### Access MySQL Database

```bash
docker exec -it talkabout-db mysql -u talkabout_user -p
# Password: talkabout@!password
```

### Run CakePHP Console Commands

```bash
docker exec -it talkabout-backend bin/cake [command]
```

### Stop Containers

```bash
docker-compose down
```

### Restart Containers

```bash
docker-compose restart
```

## Troubleshooting

### Issue: Cannot connect to database

**Solution:**
1. Ensure MySQL container is running: `docker ps`
2. Check database logs: `docker logs talkabout-db`
3. Verify credentials in `backend/config/app_local.php`

### Issue: CakePHP errors not showing

**Solution:**
Set `'debug' => true` in `backend/config/app_local.php`

### Issue: Permission denied errors

**Solution:**
```bash
docker exec -it talkabout-backend chmod -R 777 tmp/ logs/
```

### Issue: Composer dependencies not installing

**Solution:**
```bash
docker exec -it talkabout-backend composer clear-cache
docker exec -it talkabout-backend composer install
```

## Security Notes

- Passwords are hashed using bcrypt via CakePHP's `DefaultPasswordHasher`
- CSRF protection is enabled (can be configured in middleware)
- Input validation is performed on both frontend and backend
- Session-based authentication using CakePHP Authentication plugin
- SQL injection protection via CakePHP's ORM

## Next Steps / Future Enhancements

- [ ] Add password reset functionality
- [ ] Implement email verification
- [ ] Add user profile management
- [ ] Create messaging functionality
- [ ] Add real-time notifications
- [ ] Implement API for mobile app integration
- [ ] Add social authentication (Google, Facebook)
- [ ] Create admin panel

## License

This project is for educational/development purposes.

## Support

For issues or questions, please refer to:
- [CakePHP Documentation](https://book.cakephp.org/5/en/index.html)
- [Vue 3 Documentation](https://vuejs.org/)
- [Docker Documentation](https://docs.docker.com/)
