<?php
require_once 'config.php';

$pdo = get_db();

// Handle reactions
if ($_POST['reaction'] ?? false) {
    $post_id = $_POST['post_id'];
    $reaction = $_POST['reaction'];
    
    // Remove existing reaction or add new one
    $stmt = $pdo->prepare("DELETE FROM reactions WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
    
    if ($reaction !== 'remove') {
        $stmt = $pdo->prepare("INSERT INTO reactions (post_id, user_id, reaction_type) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $_SESSION['user_id'], $reaction]);
    }
    
    header('Location: ' . OSRG_CONNECT_BASE_URL . '?page=friends');
    exit;
}

// Handle comments
if ($_POST['comment'] ?? false) {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['post_id'], $_SESSION['user_id'], $_POST['comment']]);
    header('Location: ' . OSRG_CONNECT_BASE_URL . '?page=friends');
    exit;
}

// Get all friends (accepted friendships)
$stmt = $pdo->prepare("
    SELECT u.id, u.username, MIN(f.created_at) as created_at
    FROM users u
    JOIN friends f ON (
        (f.user_id = ? AND f.friend_id = u.id AND f.status = 'accepted') OR
        (f.friend_id = ? AND u.id = f.user_id AND f.status = 'accepted')
    )
    GROUP BY u.id
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$friends = $stmt->fetchAll();

// Get posts from friends
if ($friends) {
    $friend_ids = array_column($friends, 'id');
    $placeholders = implode(',', array_fill(0, count($friend_ids), '?'));

    $query = "
        SELECT p.id, p.content, p.created_at, u.username, u.avatar, p.file_path, p.file_type,
               COUNT(DISTINCT CASE WHEN r.reaction_type = 'like' THEN r.id END) as like_count,
               COUNT(DISTINCT CASE WHEN r.reaction_type = 'love' THEN r.id END) as love_count,
               COUNT(DISTINCT CASE WHEN r.reaction_type = 'laugh' THEN r.id END) as laugh_count,
               COUNT(DISTINCT c.id) as comment_count,
               ur.reaction_type as user_reaction
        FROM posts p 
        JOIN users u ON p.user_id = u.id
        LEFT JOIN reactions r ON p.id = r.post_id
        LEFT JOIN comments c ON p.id = c.post_id
        LEFT JOIN reactions ur ON p.id = ur.post_id AND ur.user_id = ?
        WHERE p.user_id IN ($placeholders)
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ";
    
    $params = array_merge([$_SESSION['user_id']], $friend_ids);
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
} else {
    $posts = [];
}

$page_title = 'My Friends';

// Include the header
include 'header.php';
?>

<div class="content">
    <div class="main-content">
        <div class="posts-container">
            <?php if ($posts): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <div class="post-header">
                            <img src="<?= $post['avatar'] ?: 'https://t3.gstatic.com/licensed-image?q=tbn:ANd9GcR_xNqW4qR3z8C4lP_jG6yv6Wn2wS7f7C6F9Q_a_n7b1F2M-N-o' ?>" class="post-avatar" alt="User Avatar">
                            <strong><a href="profile.php?id=<?= $post['user_id'] ?>" style="color: black; text-decoration: none;"><?= htmlspecialchars($post['username']) ?></a></strong>
                        </div>
                        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                        <?php if ($post['file_path']): ?>
                            <a href="<?= htmlspecialchars($post['file_path']) ?>" target="_blank">
                                <img src="<?= htmlspecialchars($post['file_path']) ?>" style="max-width: 100%; height: auto; display: block; margin-top: 10px; border-radius: 8px;">
                            </a>
                        <?php endif; ?>
                        
                        <div class="post-meta">
                            <span class="reaction-counts">
                                <span class="reaction-count" data-reaction-type="like" data-post-id="<?= $post['id'] ?>">👍 <?= $post['like_count'] ?></span>
                                <span class="reaction-count" data-reaction-type="love" data-post-id="<?= $post['id'] ?>">❤️ <?= $post['love_count'] ?></span>
                                <span class="reaction-count" data-reaction-type="laugh" data-post-id="<?= $post['id'] ?>">😂 <?= $post['laugh_count'] ?></span>
                            </span>
                            <span class="comment-count">
                                <a href="comments.php?post_id=<?= $post['id'] ?>" style="color: #666; text-decoration: none;"><?= $post['comment_count'] ?> comments</a>
                            </span>
                        </div>
                        
                        <div class="reaction-bar">
                            <form action="<?= OSRG_CONNECT_BASE_URL ?>?page=friends" method="POST">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <input type="hidden" name="reaction" value="like">
                                <button type="submit" class="<?= $post['user_reaction'] === 'like' ? 'active' : '' ?>">
                                    👍 Like
                                </button>
                            </form>
                            <form action="<?= OSRG_CONNECT_BASE_URL ?>?page=friends" method="POST">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <input type="hidden" name="reaction" value="love">
                                <button type="submit" class="<?= $post['user_reaction'] === 'love' ? 'active' : '' ?>">
                                    ❤️ Love
                                </button>
                            </form>
                            <form action="<?= OSRG_CONNECT_BASE_URL ?>?page=friends" method="POST">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <input type="hidden" name="reaction" value="laugh">
                                <button type="submit" class="<?= $post['user_reaction'] === 'laugh' ? 'active' : '' ?>">
                                    😂 Laugh
                                </button>
                            </form>
                            <?php if ($post['user_reaction']): ?>
                                <form action="<?= OSRG_CONNECT_BASE_URL ?>?page=friends" method="POST">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <input type="hidden" name="reaction" value="remove">
                                    <button type="submit">
                                        Remove Reaction
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        
                        <div class="comment-form-container">
                            <form action="<?= OSRG_CONNECT_BASE_URL ?>?page=friends" method="POST">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <div style="display: flex; gap: 10px;">
                                    <input type="text" name="comment" placeholder="Write a comment..." style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 20px; font-size: 14px;" required>
                                    <button type="submit" style="padding: 8px 15px; font-size: 12px;">Post</button>
                                </div>
                            </form>
                        </div>
                        
                        <div style="clear: both; margin-top: 10px;">
                            <small><?php
                            // Get user's timezone
                            $stmt_tz = $pdo->prepare("SELECT timezone FROM users WHERE id = ?");
                            $stmt_tz->execute([$_SESSION['user_id']]);
                            $user_tz = $stmt_tz->fetch();
                            $timezone = $user_tz['timezone'] ?? 'Europe/London';
                            
                            // Convert to user's timezone
                            $date = new DateTime($post['created_at'], new DateTimeZone('UTC'));
                            $date->setTimezone(new DateTimeZone($timezone));
                            echo $date->format('M j, H:i');
                            ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="post">
                    <p style="text-align: center; color: #666; padding: 20px;">
                        No posts from friends yet.<br>
                        <?php if (!$friends): ?>
                            <a href="<?= OSRG_CONNECT_BASE_URL ?>?page=users" style="color: #1877f2;">Add some friends to see their posts!</a>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>