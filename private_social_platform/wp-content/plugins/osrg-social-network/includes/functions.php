<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Session management for social network
function osrg_social_start_session() {
    if (!session_id()) {
        session_start();
    }
}

// Check if user is logged into social network
function osrg_social_is_logged_in() {
    osrg_social_start_session();
    return isset($_SESSION['user_id']);
}

// Get current social network user
function osrg_social_get_current_user() {
    if (!osrg_social_is_logged_in()) {
        return null;
    }
    
    global $wpdb;
    $user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}users WHERE ID = %d",
        $_SESSION['user_id']
    ));
    
    return $user;
}

// Login user to social network
function osrg_social_login_user($user_id) {
    osrg_social_start_session();
    $_SESSION['user_id'] = $user_id;
}

// Logout user from social network
function osrg_social_logout_user() {
    osrg_social_start_session();
    session_destroy();
}

// Get user avatar
function osrg_social_get_avatar($user_id, $size = 40) {
    global $wpdb;
    
    $avatar = $wpdb->get_var($wpdb->prepare(
        "SELECT social_avatar FROM {$wpdb->prefix}users WHERE ID = %d",
        $user_id
    ));
    
    if ($avatar) {
        if (strpos($avatar, 'emoji:') === 0) {
            $emoji = substr($avatar, 6);
            return '<div style="width: ' . $size . 'px; height: ' . $size . 'px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: ' . ($size * 0.6) . 'px;">' . $emoji . '</div>';
        } else {
            $upload_dir = wp_upload_dir();
            $avatar_url = $upload_dir['baseurl'] . '/osrg-social/avatars/' . $avatar;
            return '<img src="' . $avatar_url . '" style="width: ' . $size . 'px; height: ' . $size . 'px; border-radius: 50%; object-fit: cover;">';
        }
    }
    
    return get_avatar($user_id, $size);
}

// Format time ago
function osrg_social_time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm';
    if ($time < 86400) return floor($time/3600) . 'h';
    if ($time < 2592000) return floor($time/86400) . 'd';
    if ($time < 31536000) return floor($time/2592000) . 'mo';
    return floor($time/31536000) . 'y';
}

// Handle file uploads
function osrg_social_handle_upload($file) {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($file, $upload_overrides);
    
    if ($movefile && !isset($movefile['error'])) {
        return $movefile;
    }
    
    return false;
}

// Send email notification
function osrg_social_send_email($to, $subject, $message) {
    return wp_mail($to, $subject, $message);
}

// Check if user is admin
function osrg_social_is_admin($user_id = null) {
    if (!$user_id) {
        $current_user = osrg_social_get_current_user();
        $user_id = $current_user ? $current_user->ID : 0;
    }
    
    return user_can($user_id, 'manage_options');
}

// Get social network statistics
function osrg_social_get_stats() {
    global $wpdb;
    
    $stats = [];
    $stats['total_users'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}users");
    $stats['total_posts'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}social_posts");
    $stats['total_comments'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}social_comments");
    $stats['total_reactions'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}social_reactions");
    $stats['total_messages'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}social_messages");
    
    return $stats;
}
?>