<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check admin permissions
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

$stats = osrg_social_get_stats();
global $wpdb;

// Handle user approval
if ($_POST['approve_user'] ?? false) {
    $wpdb->update(
        $wpdb->prefix . 'users',
        ['social_approved' => 1],
        ['ID' => $_POST['user_id']]
    );
    echo '<div class="notice notice-success"><p>User approved successfully!</p></div>';
}

// Get pending users
$pending_users = $wpdb->get_results(
    "SELECT ID, user_login, user_email FROM {$wpdb->prefix}users WHERE social_approved = 0"
);

// Get recent posts
$recent_posts = $wpdb->get_results(
    "SELECT p.id, p.content, p.created_at, u.user_login as username 
     FROM {$wpdb->prefix}social_posts p 
     JOIN {$wpdb->prefix}users u ON p.user_id = u.ID 
     ORDER BY p.created_at DESC LIMIT 10"
);
?>

<div class="wrap">
    <h1>OSRG Social Network Admin</h1>
    
    <div class="card" style="margin: 20px 0;">
        <h2>Statistics</h2>
        <table class="wp-list-table widefat fixed striped">
            <tr><td><strong>Total Users:</strong></td><td><?= $stats['total_users'] ?></td></tr>
            <tr><td><strong>Total Posts:</strong></td><td><?= $stats['total_posts'] ?></td></tr>
            <tr><td><strong>Total Comments:</strong></td><td><?= $stats['total_comments'] ?></td></tr>
            <tr><td><strong>Total Reactions:</strong></td><td><?= $stats['total_reactions'] ?></td></tr>
            <tr><td><strong>Total Messages:</strong></td><td><?= $stats['total_messages'] ?></td></tr>
        </table>
    </div>
    
    <?php if ($pending_users): ?>
    <div class="card" style="margin: 20px 0;">
        <h2>Pending User Approvals</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_users as $user): ?>
                <tr>
                    <td><?= esc_html($user->user_login) ?></td>
                    <td><?= esc_html($user->user_email) ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="user_id" value="<?= $user->ID ?>">
                            <button type="submit" name="approve_user" value="1" class="button button-primary">Approve</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <div class="card" style="margin: 20px 0;">
        <h2>Recent Posts</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Content</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_posts as $post): ?>
                <tr>
                    <td><?= esc_html($post->username) ?></td>
                    <td><?= esc_html(wp_trim_words($post->content, 10)) ?></td>
                    <td><?= esc_html($post->created_at) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>