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
    
    return $pdo;
}

function get_db() {
    global $db_file;
    return new PDO("sqlite:$db_file");
}
?>