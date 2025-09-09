<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is logged in
if (!is_user_logged_in()) {
    return '<p>Please log in to view the social feed. <a href="' . wp_login_url() . '">Login</a></p>';
}

$current_user = wp_get_current_user();
global $wpdb;

// Handle form submissions
if ($_POST['content'] ?? false) {
    $file_path = null;
    $file_type = null;
    
    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $upload = osrg_social_handle_upload($_FILES['file']);
        if ($upload) {
            $file_path = $upload['file'];
            $file_type = pathinfo($upload['file'], PATHINFO_EXTENSION);
        }
    }
    
    $wpdb->insert(
        $wpdb->prefix . 'social_posts',
        [
            'user_id' => $current_user->ID,
            'content' => $_POST['content'],
            'file_path' => $file_path,
            'file_type' => $file_type
        ]
    );
}

// Handle reactions
if ($_POST['reaction'] ?? false) {
    $post_id = $_POST['post_id'];
    $reaction = $_POST['reaction'];
    
    $wpdb->delete(
        $wpdb->prefix . 'social_reactions',
        ['post_id' => $post_id, 'user_id' => $current_user->ID]
    );
    
    if ($reaction !== 'remove') {
        $wpdb->insert(
            $wpdb->prefix . 'social_reactions',
            [
                'post_id' => $post_id,
                'user_id' => $current_user->ID,
                'reaction_type' => $reaction
            ]
        );
    }
}

// Handle comments
if ($_POST['comment'] ?? false) {
    $wpdb->insert(
        $wpdb->prefix . 'social_comments',
        [
            'post_id' => $_POST['post_id'],
            'user_id' => $current_user->ID,
            'content' => $_POST['comment']
        ]
    );
}

// Get posts with reactions and comments
$posts = $wpdb->get_results($wpdb->prepare("
    SELECT p.id, p.content, p.created_at, u.user_login as username, u.social_avatar as avatar, p.file_path, p.file_type,
           COUNT(DISTINCT CASE WHEN r.reaction_type = 'like' THEN r.id END) as like_count,
           COUNT(DISTINCT CASE WHEN r.reaction_type = 'love' THEN r.id END) as love_count,
           COUNT(DISTINCT CASE WHEN r.reaction_type = 'laugh' THEN r.id END) as laugh_count,
           COUNT(DISTINCT c.id) as comment_count,
           ur.reaction_type as user_reaction
    FROM {$wpdb->prefix}social_posts p 
    JOIN {$wpdb->prefix}users u ON p.user_id = u.ID
    LEFT JOIN {$wpdb->prefix}social_reactions r ON p.id = r.post_id
    LEFT JOIN {$wpdb->prefix}social_comments c ON p.id = c.post_id
    LEFT JOIN {$wpdb->prefix}social_reactions ur ON p.id = ur.post_id AND ur.user_id = %d
    WHERE u.social_approved = 1
    GROUP BY p.id
    ORDER BY p.created_at DESC
", $current_user->ID));
?>

<style>
.osrg-social-feed { max-width: 600px; margin: 0 auto; }
.osrg-post { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.osrg-form-group { margin: 15px 0; }
.osrg-textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
.osrg-button { background: #1877f2; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
.osrg-reactions { display: flex; gap: 10px; margin: 10px 0; }
.osrg-reaction-btn { background: none; border: none; font-size: 18px; cursor: pointer; padding: 8px; }
</style>

<div class="osrg-social-feed">
    <div class="osrg-post">
        <h3>Share something...</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="osrg-form-group">
                <textarea name="content" class="osrg-textarea" placeholder="What's on your mind?" required></textarea>
            </div>
            <div class="osrg-form-group">
                <input type="file" name="file" accept=".mp4,.mp3,.png,.jpg,.jpeg">
                <small>Upload: MP4, MP3, PNG, JPG (max 10MB)</small>
            </div>
            <button type="submit" class="osrg-button">Post</button>
        </form>
    </div>

    <?php if ($posts): ?>
        <?php foreach ($posts as $post): ?>
        <div class="osrg-post">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <?= osrg_social_get_avatar($post->user_id ?? 0, 40) ?>
                <strong><?= esc_html($post->username) ?></strong>
            </div>
            
            <div><?= wp_kses_post($post->content) ?></div>
            
            <?php if ($post->file_path): ?>
            <div style="margin: 10px 0;">
                <?php if ($post->file_type == 'mp4'): ?>
                    <video controls style="width: 100%; max-width: 100%;">
                        <source src="<?= esc_url($post->file_path) ?>" type="video/mp4">
                    </video>
                <?php elseif ($post->file_type == 'mp3'): ?>
                    <audio controls style="width: 100%;">
                        <source src="<?= esc_url($post->file_path) ?>" type="audio/mpeg">
                    </audio>
                <?php elseif (in_array($post->file_type, ['png', 'jpg', 'jpeg'])): ?>
                    <img src="<?= esc_url($post->file_path) ?>" alt="Uploaded image" style="width: 100%; border-radius: 8px;">
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="osrg-reactions">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="post_id" value="<?= $post->id ?>">
                    <button type="submit" name="reaction" value="<?= $post->user_reaction === 'like' ? 'remove' : 'like' ?>" 
                            class="osrg-reaction-btn <?= $post->user_reaction === 'like' ? 'active' : '' ?>">
                        👍 <?= $post->like_count > 0 ? $post->like_count : '' ?>
                    </button>
                </form>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="post_id" value="<?= $post->id ?>">
                    <button type="submit" name="reaction" value="<?= $post->user_reaction === 'love' ? 'remove' : 'love' ?>" 
                            class="osrg-reaction-btn <?= $post->user_reaction === 'love' ? 'active' : '' ?>">
                        ❤️ <?= $post->love_count > 0 ? $post->love_count : '' ?>
                    </button>
                </form>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="post_id" value="<?= $post->id ?>">
                    <button type="submit" name="reaction" value="<?= $post->user_reaction === 'laugh' ? 'remove' : 'laugh' ?>" 
                            class="osrg-reaction-btn <?= $post->user_reaction === 'laugh' ? 'active' : '' ?>">
                        😂 <?= $post->laugh_count > 0 ? $post->laugh_count : '' ?>
                    </button>
                </form>
                <span>💬 <?= $post->comment_count ?></span>
            </div>
            
            <?php
            $comments = $wpdb->get_results($wpdb->prepare(
                "SELECT c.content, c.created_at, u.user_login as username 
                 FROM {$wpdb->prefix}social_comments c 
                 JOIN {$wpdb->prefix}users u ON c.user_id = u.ID 
                 WHERE c.post_id = %d ORDER BY c.created_at ASC",
                $post->id
            ));
            ?>
            
            <?php if ($comments): ?>
                <?php foreach ($comments as $comment): ?>
                <div style="background: #f8f9fa; padding: 8px; margin: 5px 0; border-radius: 5px; font-size: 14px;">
                    <strong><?= esc_html($comment->username) ?>:</strong> 
                    <?= esc_html($comment->content) ?>
                    <small style="color: #666; margin-left: 10px;"><?= osrg_social_time_ago($comment->created_at) ?></small>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <form method="POST" style="margin-top: 10px;">
                <input type="hidden" name="post_id" value="<?= $post->id ?>">
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="comment" placeholder="Write a comment..." style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 20px;" required>
                    <button type="submit" class="osrg-button" style="padding: 8px 15px; font-size: 12px;">Post</button>
                </div>
            </form>
            
            <small style="color: #666; margin-top: 10px; display: block;">
                <?= osrg_social_time_ago($post->created_at) ?>
            </small>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="osrg-post">
            <p><strong>Welcome!</strong></p>
            <p>Start by creating your first post above!</p>
        </div>
    <?php endif; ?>
</div>