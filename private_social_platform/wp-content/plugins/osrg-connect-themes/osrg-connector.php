<?php
/**
 * Plugin Name:       OSRG Connect Connector
 * Description:       Connects the OSRG Social Platform to WordPress.
 * Version:           1.0
 * Author:            OSRG
 */

function osrg_connect_display_app() {
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        // Redirect to the WordPress login page
        wp_redirect(wp_login_url());
        exit;
    }
    
    // Check if the user is a registered OSRG user
    // Your existing user management is separate, so you'll need a way to link them
    // For now, let's assume if they are logged into WordPress, they can access the app.
    
    // Start output buffering to capture the content of your app
    ob_start();
    
    // Include the main file of your app
    // This assumes your file structure is correct
    require_once plugin_dir_path( __FILE__ ) . 'index.php';
    
    return ob_get_clean();
}
add_shortcode('osrg_connect', 'osrg_connect_display_app');
?>