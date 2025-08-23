<?php
session_start();

// Database configuration
$db_file = 'private_social.db';

// Initialize SQLite database
function init_db() {
    global $db_file;
    $pdo = new PDO("sqlite:$db_file");
    
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY, 
        username TEXT UNIQUE, 
        email TEXT UNIQUE, 
        password_hash TEXT, 
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Posts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY, 
        user_id INTEGER, 
        content TEXT, 
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )");
    
    // Friends table
    $pdo->exec("CREATE TABLE IF NOT EXISTS friends (
        id INTEGER PRIMARY KEY, 
        user_id INTEGER, 
        friend_id INTEGER,
        status TEXT, 
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Messages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY, 
        sender_id INTEGER, 
        receiver_id INTEGER,
        content TEXT, 
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add file columns to posts table
    try {
        $pdo->exec("ALTER TABLE posts ADD COLUMN file_path TEXT");
        $pdo->exec("ALTER TABLE posts ADD COLUMN file_type TEXT");
    } catch (Exception $e) {
        // Columns already exist
    }
    
    return $pdo;
}

function get_db() {
    global $db_file;
    return new PDO("sqlite:$db_file");
}
?>