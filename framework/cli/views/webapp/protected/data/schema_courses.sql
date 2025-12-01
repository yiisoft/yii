-- SQLite
CREATE TABLE IF NOT EXISTS tbl_courses (
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    link VARCHAR(500) NOT NULL,
    published INTEGER DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME
);

-- MySQL
CREATE TABLE IF NOT EXISTS tbl_courses (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    link VARCHAR(500) NOT NULL,
    published TINYINT(1) DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



