<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Migration functions for moving from standalone to plugin
function osrg_social_migrate_uploads() {
    $old_uploads = ABSPATH . 'uploads/';
    $old_avatars = ABSPATH . 'avatars/';
    
    $upload_dir = wp_upload_dir();
    $new_uploads = $upload_dir['basedir'] . '/osrg-social/uploads/';
    $new_avatars = $upload_dir['basedir'] . '/osrg-social/avatars/';
    
    // Create new directories
    if (!file_exists($new_uploads)) {
        wp_mkdir_p($new_uploads);
    }
    if (!file_exists($new_avatars)) {
        wp_mkdir_p($new_avatars);
    }
    
    // Move uploads if they exist
    if (is_dir($old_uploads)) {
        $files = glob($old_uploads . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                copy($file, $new_uploads . $filename);
            }
        }
    }
    
    // Move avatars if they exist
    if (is_dir($old_avatars)) {
        $files = glob($old_avatars . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                copy($file, $new_avatars . $filename);
            }
        }
    }
}

// Update file paths in database
function osrg_social_update_file_paths() {
    global $wpdb;
    
    $upload_dir = wp_upload_dir();
    $new_base_url = $upload_dir['baseurl'] . '/osrg-social/uploads/';
    
    // Update post file paths
    $wpdb->query($wpdb->prepare("
        UPDATE {$wpdb->prefix}social_posts 
        SET file_path = REPLACE(file_path, 'uploads/', %s)
        WHERE file_path LIKE 'uploads/%'
    ", $new_base_url));
    
    // Update avatar paths
    $avatar_base_url = $upload_dir['baseurl'] . '/osrg-social/avatars/';
    $wpdb->query($wpdb->prepare("
        UPDATE {$wpdb->prefix}users 
        SET social_avatar = REPLACE(social_avatar, 'avatars/', %s)
        WHERE social_avatar LIKE 'avatars/%'
    ", $avatar_base_url));
}
?>