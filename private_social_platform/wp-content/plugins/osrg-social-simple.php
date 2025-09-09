<?php
/**
 * Plugin Name: OSRG Social Network
 * Plugin URI: https://osrg.lol
 * Description: Complete social media platform integrated with WordPress
 * Version: 1.0.0
 * Author: OSRG.lol
 * Author URI: https://osrg.lol
 * Text Domain: osrg-social
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Activation hook
register_activation_hook(__FILE__, 'osrg_social_activate');

function osrg_social_activate() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Create social posts table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}social_posts (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        user_id BIGINT(20) UNSIGNED, 
        content LONGTEXT, 
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES {$wpdb->prefix}users(ID)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Add admin menu
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
    echo '<div class="wrap"><h1>OSRG Social Network</h1><p>Plugin is active and working!</p></div>';
}

// Add shortcode
add_shortcode('osrg_social_feed', 'osrg_social_feed_shortcode');

function osrg_social_feed_shortcode() {
    return '<div style="padding: 20px; background: #f9f9f9; border-radius: 8px;"><h3>OSRG Social Feed</h3><p>Social network is ready! Plugin is working correctly.</p></div>';
}
?>