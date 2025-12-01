-- MySQL Database initialization for AISana
-- Run this in Adminer or MySQL client after creating the database

-- Create news table
CREATE TABLE IF NOT EXISTS tbl_news (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    image VARCHAR(500),
    slug VARCHAR(255) NOT NULL UNIQUE,
    published TINYINT(1) DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME,
    INDEX idx_published (published),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create courses table
CREATE TABLE IF NOT EXISTS tbl_courses (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    link VARCHAR(500) NOT NULL,
    published TINYINT(1) DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME,
    INDEX idx_published (published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



