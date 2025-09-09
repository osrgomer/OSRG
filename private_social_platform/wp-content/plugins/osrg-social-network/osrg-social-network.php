<?php
/**
 * Plugin Name: OSRG Social Network
 * Plugin URI: https://osrg.lol
 * Description: Complete social media platform integrated with WordPress
 * Version: 1.0.0
 * Author: OSRG.lol
 * Author URI: https://osrg.lol
 * Text Domain: osrg-social
 * Network: false
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('OSRG_SOCIAL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OSRG_SOCIAL_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include core files
require_once OSRG_SOCIAL_PLUGIN_PATH . 'includes/config.php';
require_once OSRG_SOCIAL_PLUGIN_PATH . 'includes/functions.php';
require_once OSRG_SOCIAL_PLUGIN_PATH . 'includes/migration.php';

// Activation hook
register_activation_hook(__FILE__, 'osrg_social_activate');

function osrg_social_activate() {
    init_db();
    
    // Create upload directories
    $upload_dir = wp_upload_dir();
    $social_uploads = $upload_dir['basedir'] . '/osrg-social';
    $uploads_dir = $social_uploads . '/uploads';
    $avatars_dir = $social_uploads . '/avatars';
    
    if (!file_exists($social_uploads)) {
        wp_mkdir_p($social_uploads);
    }
    if (!file_exists($uploads_dir)) {
        wp_mkdir_p($uploads_dir);
    }
    if (!file_exists($avatars_dir)) {
        wp_mkdir_p($avatars_dir);
    }
    
    // Migrate existing files
    osrg_social_migrate_uploads();
    osrg_social_update_file_paths();
}

// Add menu items
add_action('admin_menu', 'osrg_social_admin_menu');

function osrg_social_admin_menu() {
    add_menu_page(
        'OSRG Social Network',
        'Social Network',
        'manage_options',
        'osrg-social',
        'osrg_social_admin_page',
        'dashicons-groups',
        30
    );
}

function osrg_social_admin_page() {
    include OSRG_SOCIAL_PLUGIN_PATH . 'templates/admin.php';
}

// Add shortcodes
add_shortcode('osrg_social_feed', 'osrg_social_feed_shortcode');
add_shortcode('osrg_social_friends', 'osrg_social_friends_shortcode');
add_shortcode('osrg_social_messages', 'osrg_social_messages_shortcode');
add_shortcode('osrg_social_login', 'osrg_social_login_shortcode');

function osrg_social_feed_shortcode() {
    ob_start();
    include OSRG_SOCIAL_PLUGIN_PATH . 'templates/feed.php';
    return ob_get_clean();
}

function osrg_social_friends_shortcode() {
    ob_start();
    include OSRG_SOCIAL_PLUGIN_PATH . 'templates/friends.php';
    return ob_get_clean();
}

function osrg_social_messages_shortcode() {
    ob_start();
    include OSRG_SOCIAL_PLUGIN_PATH . 'templates/messages.php';
    return ob_get_clean();
}

function osrg_social_login_shortcode() {
    ob_start();
    include OSRG_SOCIAL_PLUGIN_PATH . 'templates/login.php';
    return ob_get_clean();
}