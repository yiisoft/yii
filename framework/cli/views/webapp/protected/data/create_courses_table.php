<?php
/**
 * Script to create courses table
 * Run this from command line: php create_courses_table.php
 */

$dbPath = dirname(__FILE__) . '/aisana.db';

if (!file_exists($dbPath)) {
    die("Database file not found: $dbPath\n");
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "CREATE TABLE IF NOT EXISTS tbl_courses (
        id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        link VARCHAR(500) NOT NULL,
        published INTEGER DEFAULT 0,
        created_at DATETIME,
        updated_at DATETIME
    );";
    
    $db->exec($sql);
    echo "Table tbl_courses created successfully!\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}



