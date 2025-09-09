<?php
session_start();
date_default_timezone_set('Europe/London');

// Database configuration
$db_file = 'private_social.db';

// WordPress database configuration
$wp_db_host = 'localhost';
$wp_db_name = 'u542077544_OSRGConnect';
$wp_db_user = 'u542077544_Omer';
$wp_db_pass = 'V0Zw7celP]AO9';
$wp_table_prefix = 'wp_';

// Get WordPress database connection
function get_wp_db() {
    global $wp_db_host, $wp_db_name, $wp_db_user, $wp_db_pass;
    try {
        return new PDO("mysql:host=$wp_db_host;dbname=$wp_db_name", $wp_db_user, $wp_db_pass);
    } catch (PDOException $e) {
        return null;
    }
}

// WordPress password verification
function wp_check_password($password, $hash) {
    // WordPress uses MD5 for older passwords or phpass for newer ones
    if (strlen($hash) <= 32) {
        return hash_equals($hash, md5($password));
    }
    
    // Check if it's a phpass hash
    if (substr($hash, 0, 3) == '$P$' || substr($hash, 0, 3) == '$H$') {
        $wp_hasher = new PasswordHash(8, true);
        return $wp_hasher->CheckPassword($password, $hash);
    }
    
    return false;
}

// WordPress authentication function
function wp_social_authenticate($username, $password) {
    global $wp_table_prefix;
    $wp_pdo = get_wp_db();
    if (!$wp_pdo) return false;
    
    try {
        $stmt = $wp_pdo->prepare("SELECT ID, user_login, user_email, user_pass FROM {$wp_table_prefix}users WHERE user_login = ? OR user_email = ?");
        $stmt->execute([$username, $username]);
        $wp_user = $stmt->fetch();
        
        if ($wp_user && wp_check_password($password, $wp_user['user_pass'])) {
            return [
                'id' => $wp_user['ID'],
                'username' => $wp_user['user_login'],
                'email' => $wp_user['user_email']
            ];
        }
    } catch (Exception $e) {
        // WordPress DB connection failed, return false
    }
    
    return false;
}

// Simple WordPress-compatible password hasher
class PasswordHash {
    var $itoa64;
    var $iteration_count_log2;
    var $portable_hashes;
    var $random_state;

    function __construct($iteration_count_log2, $portable_hashes) {
        $this->itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
            $iteration_count_log2 = 8;
        $this->iteration_count_log2 = $iteration_count_log2;
        $this->portable_hashes = $portable_hashes;
        $this->random_state = microtime() . uniqid(rand(), TRUE);
    }

    function CheckPassword($password, $stored_hash) {
        $hash = $this->crypt_private($password, $stored_hash);
        if ($hash[0] == '*')
            $hash = crypt($password, $stored_hash);
        return hash_equals($stored_hash, $hash);
    }

    function crypt_private($password, $setting) {
        $output = '*0';
        if (substr($setting, 0, 2) == $output)
            $output = '*1';
        $id = substr($setting, 0, 3);
        if ($id != '$P$' && $id != '$H$')
            return $output;
        $count_log2 = strpos($this->itoa64, $setting[3]);
        if ($count_log2 < 7 || $count_log2 > 30)
            return $output;
        $count = 1 << $count_log2;
        $salt = substr($setting, 4, 8);
        if (strlen($salt) != 8)
            return $output;
        $hash = md5($salt . $password, TRUE);
        do {
            $hash = md5($hash . $password, TRUE);
        } while (--$count);
        $output = substr($setting, 0, 12);
        $output .= $this->encode64($hash, 16);
        return $output;
    }

    function encode64($input, $count) {
        $output = '';
        $i = 0;
        do {
            $value = ord($input[$i++]);
            $output .= $this->itoa64[$value & 0x3f];
            if ($i < $count)
                $value |= ord($input[$i]) << 8;
            $output .= $this->itoa64[($value >> 6) & 0x3f];
            if ($i++ >= $count)
                break;
            if ($i < $count)
                $value |= ord($input[$i]) << 16;
            $output .= $this->itoa64[($value >> 12) & 0x3f];
            if ($i++ >= $count)
                break;
            $output .= $this->itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);
        return $output;
    }
}

// Sync WordPress user to social network
function sync_wp_user($wp_user) {
    $pdo = get_db();
    
    // Check if user exists in social network
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$wp_user['username'], $wp_user['email']]);
    $existing = $stmt->fetch();
    
    if (!$existing) {
        // Create new user in social network
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, approved) VALUES (?, ?, ?, 1)");
        $stmt->execute([$wp_user['username'], $wp_user['email'], 'wp_synced']);
        return $pdo->lastInsertId();
    }
    
    return $existing['id'];
}

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
    
    $image_url = '';
    if ($image->length > 0) {
        $image_url = $image->item(0)->value;
        // Convert relative URLs to absolute
        if ($image_url && !filter_var($image_url, FILTER_VALIDATE_URL)) {
            $parsed_url = parse_url($url);
            $base_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
            if (isset($parsed_url['port'])) {
                $base_url .= ':' . $parsed_url['port'];
            }
            
            if (strpos($image_url, '/') === 0) {
                // Absolute path
                $image_url = $base_url . $image_url;
            } else {
                // Relative path
                $path = dirname($parsed_url['path'] ?? '/');
                $image_url = $base_url . rtrim($path, '/') . '/' . $image_url;
            }
        }
    }
    
    return [
        'title' => $title,
        'description' => $description->length > 0 ? $description->item(0)->value : '',
        'image' => $image_url,
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