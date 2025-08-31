<?php
session_start();
date_default_timezone_set('Europe/London');

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
    
    // Add approval status to users table
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN approved INTEGER DEFAULT 0");
        // Make OSRG auto-approved
        $pdo->exec("UPDATE users SET approved = 1 WHERE username = 'OSRG'");
    } catch (Exception $e) {
        // Column already exists
    }
    
    // Add timezone to users table
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN timezone TEXT DEFAULT 'Europe/London'");
    } catch (Exception $e) {
        // Column already exists
    }
    
    // Add email notifications preference
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN email_notifications INTEGER DEFAULT 0");
    } catch (Exception $e) {
        // Column already exists
    }
    
    // Add avatar column
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar TEXT DEFAULT NULL");
    } catch (Exception $e) {
        // Column already exists
    }
    
    // Comments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id INTEGER PRIMARY KEY,
        post_id INTEGER,
        user_id INTEGER,
        content TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(post_id) REFERENCES posts(id),
        FOREIGN KEY(user_id) REFERENCES users(id)
    )");
    
    // Reactions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS reactions (
        id INTEGER PRIMARY KEY,
        post_id INTEGER,
        user_id INTEGER,
        reaction_type TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(post_id) REFERENCES posts(id),
        FOREIGN KEY(user_id) REFERENCES users(id),
        UNIQUE(post_id, user_id)
    )");
    
    return $pdo;
}

function get_db() {
    global $db_file;
    return new PDO("sqlite:$db_file");
}

function get_link_preview($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; OSRG-Bot)');
    $html = curl_exec($ch);
    curl_close($ch);
    
    if (!$html) return null;
    
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $xpath = new DOMXPath($doc);
    
    $title = $xpath->query('//meta[@property="og:title"]/@content');
    $description = $xpath->query('//meta[@property="og:description"]/@content');
    $image = $xpath->query('//meta[@property="og:image"]/@content');
    
    if ($title->length == 0) {
        $title = $xpath->query('//title');
        $title = $title->length > 0 ? $title->item(0)->textContent : parse_url($url, PHP_URL_HOST);
    } else {
        $title = $title->item(0)->value;
    }
    
    return [
        'title' => $title,
        'description' => $description->length > 0 ? $description->item(0)->value : '',
        'image' => $image->length > 0 ? $image->item(0)->value : '',
        'url' => $url
    ];
}

function process_content_with_links($content) {
    // Extract text content from HTML to find URLs
    $text_content = strip_tags($content);
    $url_pattern = '/https?:\/\/[^\s<>"]+/i';
    preg_match_all($url_pattern, $text_content, $matches);
    
    $processed_content = $content;
    $link_previews = [];
    
    foreach ($matches[0] as $url) {
        // Clean URL from any HTML entities
        $clean_url = html_entity_decode($url);
        // Only process if URL is not already a link
        if (strpos($processed_content, 'href="' . $clean_url . '"') === false) {
            $processed_content = str_replace($clean_url, '<a href="' . $clean_url . '" target="_blank" style="color: #1877f2; text-decoration: none;">' . $clean_url . '</a>', $processed_content);
            $preview = get_link_preview($clean_url);
            if ($preview) {
                $link_previews[] = $preview;
            }
        }
    }
    
    return [
        'content' => $processed_content,
        'previews' => $link_previews
    ];
}
?>