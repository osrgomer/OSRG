<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Use WordPress database connection
function get_db() {
    global $wpdb;
    return $wpdb;
}

// WordPress password verification using built-in functions
function osrg_wp_check_password($password, $hash) {
    return wp_check_password($password, $hash);
}

// WordPress authentication function
function osrg_wp_social_authenticate($username, $password) {
    $user = wp_authenticate($username, $password);
    
    if (is_wp_error($user)) {
        return false;
    }
    
    return [
        'id' => $user->ID,
        'username' => $user->user_login,
        'email' => $user->user_email
    ];
}

// Initialize WordPress MySQL database with social network tables
function init_db() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Social network posts table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}social_posts (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        user_id BIGINT(20) UNSIGNED, 
        content LONGTEXT, 
        file_path VARCHAR(255),
        file_type VARCHAR(50),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES {$wpdb->prefix}users(ID)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Social network friends table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}social_friends (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        user_id BIGINT(20) UNSIGNED, 
        friend_id BIGINT(20) UNSIGNED,
        status VARCHAR(20), 
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES {$wpdb->prefix}users(ID),
        FOREIGN KEY(friend_id) REFERENCES {$wpdb->prefix}users(ID)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Social network messages table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}social_messages (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        sender_id BIGINT(20) UNSIGNED, 
        receiver_id BIGINT(20) UNSIGNED,
        content TEXT, 
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(sender_id) REFERENCES {$wpdb->prefix}users(ID),
        FOREIGN KEY(receiver_id) REFERENCES {$wpdb->prefix}users(ID)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Social network comments table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}social_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT,
        user_id BIGINT(20) UNSIGNED,
        content TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(post_id) REFERENCES {$wpdb->prefix}social_posts(id),
        FOREIGN KEY(user_id) REFERENCES {$wpdb->prefix}users(ID)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Social network reactions table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}social_reactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT,
        user_id BIGINT(20) UNSIGNED,
        reaction_type VARCHAR(20),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(post_id) REFERENCES {$wpdb->prefix}social_posts(id),
        FOREIGN KEY(user_id) REFERENCES {$wpdb->prefix}users(ID),
        UNIQUE(post_id, user_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Add social network fields to WordPress users table
    $wpdb->query("ALTER TABLE {$wpdb->prefix}users ADD COLUMN social_approved TINYINT(1) DEFAULT 1");
    $wpdb->query("ALTER TABLE {$wpdb->prefix}users ADD COLUMN social_timezone VARCHAR(50) DEFAULT 'Europe/London'");
    $wpdb->query("ALTER TABLE {$wpdb->prefix}users ADD COLUMN social_email_notifications TINYINT(1) DEFAULT 0");
    $wpdb->query("ALTER TABLE {$wpdb->prefix}users ADD COLUMN social_avatar VARCHAR(255) DEFAULT NULL");
}

function get_link_preview($url) {
    $response = wp_remote_get($url, [
        'timeout' => 10,
        'user-agent' => 'Mozilla/5.0 (compatible; OSRG-Bot)'
    ]);
    
    if (is_wp_error($response)) {
        return null;
    }
    
    $html = wp_remote_retrieve_body($response);
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
                $image_url = $base_url . $image_url;
            } else {
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
    $text_content = strip_tags($content);
    $url_pattern = '/https?:\/\/[^\s<>"]+/i';
    preg_match_all($url_pattern, $text_content, $matches);
    
    $processed_content = $content;
    $link_previews = [];
    
    foreach ($matches[0] as $url) {
        $clean_url = html_entity_decode($url);
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