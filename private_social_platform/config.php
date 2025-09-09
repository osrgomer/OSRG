<?php
session_start();
date_default_timezone_set('Europe/London');

// WordPress database configuration - now the ONLY database
$wp_db_host = 'localhost';
$wp_db_name = 'u542077544_OSRGConnect';
$wp_db_user = 'u542077544_Omer';
$wp_db_pass = 'V0Zw7celP]AO9';
$wp_table_prefix = 'wp_';

// Get database connection (WordPress MySQL)
function get_db() {
    global $wp_db_host, $wp_db_name, $wp_db_user, $wp_db_pass;
    try {
        return new PDO("mysql:host=$wp_db_host;dbname=$wp_db_name", $wp_db_user, $wp_db_pass);
    } catch (PDOException $e) {
        return null;
    }
}

// Alias for compatibility
function get_wp_db() {
    return get_db();
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
    $pdo = get_db();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("SELECT ID, user_login, user_email, user_pass FROM {$wp_table_prefix}users WHERE user_login = ? OR user_email = ?");
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

// No sync needed - using WordPress users directly
function sync_wp_user($wp_user) {
    return $wp_user['id'];
}

// Initialize WordPress MySQL database with social network tables
function init_db() {
    global $wp_table_prefix;
    $pdo = get_db();
    if (!$pdo) return null;
    
    // Social network posts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS {$wp_table_prefix}social_posts (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        user_id BIGINT(20) UNSIGNED, 
        content LONGTEXT, 
        file_path VARCHAR(255),
        file_type VARCHAR(50),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES {$wp_table_prefix}users(ID)
    )");
    
    // Social network friends table
    $pdo->exec("CREATE TABLE IF NOT EXISTS {$wp_table_prefix}social_friends (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        user_id BIGINT(20) UNSIGNED, 
        friend_id BIGINT(20) UNSIGNED,
        status VARCHAR(20), 
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES {$wp_table_prefix}users(ID),
        FOREIGN KEY(friend_id) REFERENCES {$wp_table_prefix}users(ID)
    )");
    
    // Social network messages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS {$wp_table_prefix}social_messages (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        sender_id BIGINT(20) UNSIGNED, 
        receiver_id BIGINT(20) UNSIGNED,
        content TEXT, 
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(sender_id) REFERENCES {$wp_table_prefix}users(ID),
        FOREIGN KEY(receiver_id) REFERENCES {$wp_table_prefix}users(ID)
    )");
    
    // Social network comments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS {$wp_table_prefix}social_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT,
        user_id BIGINT(20) UNSIGNED,
        content TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(post_id) REFERENCES {$wp_table_prefix}social_posts(id),
        FOREIGN KEY(user_id) REFERENCES {$wp_table_prefix}users(ID)
    )");
    
    // Social network reactions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS {$wp_table_prefix}social_reactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT,
        user_id BIGINT(20) UNSIGNED,
        reaction_type VARCHAR(20),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(post_id) REFERENCES {$wp_table_prefix}social_posts(id),
        FOREIGN KEY(user_id) REFERENCES {$wp_table_prefix}users(ID),
        UNIQUE(post_id, user_id)
    )");
    
    // Add social network fields to WordPress users table
    try {
        $pdo->exec("ALTER TABLE {$wp_table_prefix}users ADD COLUMN social_approved TINYINT(1) DEFAULT 1");
        $pdo->exec("ALTER TABLE {$wp_table_prefix}users ADD COLUMN social_timezone VARCHAR(50) DEFAULT 'Europe/London'");
        $pdo->exec("ALTER TABLE {$wp_table_prefix}users ADD COLUMN social_email_notifications TINYINT(1) DEFAULT 0");
        $pdo->exec("ALTER TABLE {$wp_table_prefix}users ADD COLUMN social_avatar VARCHAR(255) DEFAULT NULL");
    } catch (Exception $e) {
        // Columns already exist
    }
    
    return $pdo;
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