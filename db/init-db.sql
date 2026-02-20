-- =====================================================
-- Database Initialization Script
-- Simple Social Media Web Application (OJT Mini Project)
-- =====================================================

-- Optional: create and use database
CREATE DATABASE IF NOT EXISTS talkabout_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE talkabout_db;

-- Grant privileges to the application user
GRANT ALL PRIVILEGES ON talkabout_db.* TO 'talkabout_user'@'%';
FLUSH PRIVILEGES;

-- =====================================================
-- Table: users
-- =====================================================
CREATE TABLE users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    profile_photo_path VARCHAR(255) NULL,
    about TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_users_username UNIQUE (username)
) ENGINE=InnoDB;

-- =====================================================
-- Table: posts
-- =====================================================
CREATE TABLE posts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    content_text TEXT NULL,
    visibility ENUM('public', 'friends') NOT NULL DEFAULT 'public',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,

    CONSTRAINT fk_posts_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_posts_user_id ON posts(user_id);
CREATE INDEX idx_posts_created_at ON posts(created_at);
CREATE INDEX idx_posts_visibility ON posts(visibility);

-- =====================================================
-- Table: post_images
-- =====================================================
CREATE TABLE post_images (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_post_images_post
        FOREIGN KEY (post_id)
        REFERENCES posts(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_post_images_post_id ON post_images(post_id);
CREATE INDEX idx_post_images_order ON post_images(post_id, display_order);

-- =====================================================
-- Table: comments
-- =====================================================
CREATE TABLE comments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    content_text TEXT NULL,
    content_image_path VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,

    CONSTRAINT fk_comments_post
        FOREIGN KEY (post_id)
        REFERENCES posts(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_comments_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    -- Validation rule: at least one content field must exist
    CONSTRAINT chk_comments_content
        CHECK (
            content_text IS NOT NULL
            OR content_image_path IS NOT NULL
        )
) ENGINE=InnoDB;

CREATE INDEX idx_comments_post_id ON comments(post_id);
CREATE INDEX idx_comments_created_at ON comments(created_at);

-- =====================================================
-- Table: likes
-- =====================================================
CREATE TABLE likes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    target_type ENUM('post', 'comment') NOT NULL,
    target_id BIGINT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_likes_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    -- Prevent duplicate likes
    CONSTRAINT uq_likes_unique
        UNIQUE (user_id, target_type, target_id)

    -- NOTE:
    -- target_id cannot have a foreign key because it is polymorphic
    -- (references posts.id OR comments.id depending on target_type)
) ENGINE=InnoDB;

CREATE INDEX idx_likes_user_id ON likes(user_id);
CREATE INDEX idx_likes_target ON likes(target_type, target_id);

-- =====================================================
-- Table: notifications
-- =====================================================
CREATE TABLE notifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    type ENUM('friend_request', 'post_liked', 'post_commented', 'comment_liked', 'mention') NOT NULL,
    actor_id BIGINT NULL,
    target_type ENUM('post', 'comment', 'user', 'friendship') NULL,
    target_id BIGINT NULL,
    message TEXT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notifications_actor
        FOREIGN KEY (actor_id)
        REFERENCES users(id)
        ON DELETE CASCADE

    -- NOTE:
    -- target_id cannot have a foreign key because it is polymorphic
    -- (references posts.id OR comments.id OR users.id depending on target_type)
) ENGINE=InnoDB;

CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_created_at ON notifications(created_at);
CREATE INDEX idx_notifications_is_read ON notifications(user_id, is_read);

-- =====================================================
-- Table: friendships
-- =====================================================
CREATE TABLE friendships (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,

    requester_id BIGINT NOT NULL,
    addressee_id BIGINT NOT NULL,

    status ENUM('pending', 'accepted', 'rejected', 'blocked')
           NOT NULL DEFAULT 'pending',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_friendships_requester
        FOREIGN KEY (requester_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_friendships_addressee
        FOREIGN KEY (addressee_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT uq_friendship_pair UNIQUE (requester_id, addressee_id)
) ENGINE=InnoDB;

CREATE INDEX idx_friendships_requester_id ON friendships(requester_id);
CREATE INDEX idx_friendships_addressee_id ON friendships(addressee_id);
CREATE INDEX idx_friendships_status ON friendships(status);

-- =====================================================
-- Seed default admin user
-- Username: admin
-- Password: admin123
-- =====================================================
INSERT INTO users (full_name, username, password_hash, about, created_at, updated_at) VALUES
    ('John Wick', 'admin', '$2y$10$vxMJJlmSEg6BItmenrzt7OVLD6wEhRzlK7i1P4qJMQn/T2IhbqJ4u', 'First account for TalkAbout', NOW(), NOW())

ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    password_hash = VALUES(password_hash),
    about = VALUES(about),
    updated_at = VALUES(updated_at);

-- =====================================================
-- End of init-db.sql
-- =====================================================
