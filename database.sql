-- ================================================================
-- Travel Guide - Database Schema
-- Import this file into phpMyAdmin once before using the app.
-- ================================================================

CREATE DATABASE IF NOT EXISTS travel_guide_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE travel_guide_db;

-- Users table (all roles)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','scout','user') NOT NULL DEFAULT 'user',
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    profile_picture VARCHAR(255) NULL DEFAULT NULL,
    remember_token VARCHAR(255) NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Posts table (approved/published travel posts)
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scout_id INT NULL,
    title VARCHAR(200) NOT NULL,
    short_history TEXT NOT NULL,
    country VARCHAR(100) NOT NULL,
    genre ENUM('beach','mountain','city','historical','wildlife','cultural','adventure','other') NOT NULL DEFAULT 'other',
    cost_level ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    travel_medium_info VARCHAR(255) NOT NULL,
    image VARCHAR(255) NULL DEFAULT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (scout_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Post requests (scout submissions awaiting admin review)
CREATE TABLE IF NOT EXISTS post_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scout_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    short_history TEXT NOT NULL,
    country VARCHAR(100) NOT NULL,
    genre ENUM('beach','mountain','city','historical','wildlife','cultural','adventure','other') NOT NULL DEFAULT 'other',
    cost_level ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    travel_medium_info VARCHAR(255) NOT NULL,
    image VARCHAR(255) NULL DEFAULT NULL,
    original_post_id INT NULL DEFAULT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    rejection_reason VARCHAR(500) NULL DEFAULT NULL,
    requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (scout_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (original_post_id) REFERENCES posts(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Wishlist table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Comments table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Cost estimates table
CREATE TABLE IF NOT EXISTS cost_estimates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL UNIQUE,
    base_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    currency VARCHAR(10) NOT NULL DEFAULT 'USD',
    last_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed default admin
INSERT INTO users (name, email, password_hash, role, is_verified)
SELECT 'Administrator', 'admin@travelguide.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE role = 'admin' LIMIT 1);
-- Default admin password: password
