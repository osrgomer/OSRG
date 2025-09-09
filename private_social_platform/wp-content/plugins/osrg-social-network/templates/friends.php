<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is logged in
if (!osrg_social_is_logged_in()) {
    return '<p>Please log in to view friends.</p>';
}

$current_user = osrg_social_get_current_user();
global $wpdb;

// Get friends
$friends = $wpdb->get_results($wpdb->prepare("
    SELECT u.ID, u.user_login as username, u.social_avatar as avatar
    FROM {$wpdb->prefix}social_friends f
    JOIN {$wpdb->prefix}users u ON (f.friend_id = u.ID OR f.user_id = u.ID)
    WHERE (f.user_id = %d OR f.friend_id = %d) AND f.status = 'accepted' AND u.ID != %d
", $current_user->ID, $current_user->ID, $current_user->ID));
?>

<style>
.osrg-friends { max-width: 600px; margin: 0 auto; }
.osrg-friend { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 15px; }
</style>

<div class="osrg-friends">
    <h2>My Friends</h2>
    
    <?php if ($friends): ?>
        <?php foreach ($friends as $friend): ?>
        <div class="osrg-friend">
            <?= osrg_social_get_avatar($friend->ID, 50) ?>
            <div>
                <h3><?= esc_html($friend->username) ?></h3>
                <a href="<?= add_query_arg('user', $friend->ID, home_url('/messages/')) ?>">Send Message</a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="osrg-friend">
            <p>You haven't added any friends yet. Start connecting with other users!</p>
        </div>
    <?php endif; ?>
</div>