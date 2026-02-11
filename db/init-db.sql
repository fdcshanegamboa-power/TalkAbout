-- TalkAbout Database Initialization Script

CREATE DATABASE IF NOT EXISTS talkabout_db;
USE talkabout_db;

-- Grant privileges to the application user
GRANT ALL PRIVILEGES ON talkabout_db.* TO 'talkabout_user'@'%';
FLUSH PRIVILEGES;

-- Application tables
CREATE TABLE IF NOT EXISTS users (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	username VARCHAR(50) NOT NULL,
	email VARCHAR(255) NOT NULL,
	password VARCHAR(255) NOT NULL,
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	modified DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	UNIQUE KEY uq_users_username (username),
	UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default application users (admin + test)
INSERT INTO users (username, email, password, created, modified) VALUES
	('admin', 'admin@talkabout.local', '$2y$12$IbdThB5WNMTHVLkdolaMCu/19cKMpm0ujIGqRHv3QvjR5dby75Rum', NOW(), NOW()),
	('testuser', 'test@talkabout.local', '$2y$12$DLVaiOqQJ7NqML1QUQc5teGaxQ.UrBQrepJ7hTcI4wVt1UPQ3JCW2', NOW(), NOW())
ON DUPLICATE KEY UPDATE
	email = VALUES(email),
	password = VALUES(password),
	modified = VALUES(modified);
