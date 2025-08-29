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
    
    header('Location: friends.php');
    exit;
}

// Handle comments
if ($_POST['comment'] ?? false) {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['post_id'], $_SESSION['user_id'], $_POST['comment']]);
    header('Location: friends.php');
    exit;
}

// Get all friends (accepted friendships)
$stmt = $pdo->prepare("
    SELECT u.id, u.username, MIN(f.created_at) as created_at
    FROM users u
    JOIN friends f ON (
        (f.user_id = ? AND f.friend_id = u.id AND f.status = 'accepted') OR
        (f.friend_id = ? AND f.user_id = u.id AND f.status = 'accepted')
    )
    GROUP BY u.id, u.username
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$friends = $stmt->fetchAll();

// Get posts from friends only with reactions and comments
try {
    $stmt = $pdo->prepare("
        SELECT p.id, p.content, p.created_at, u.username, p.file_path, p.file_type,
               COUNT(DISTINCT r.id) as reaction_count,
               COUNT(DISTINCT c.id) as comment_count,
               ur.reaction_type as user_reaction
        FROM posts p 
        JOIN users u ON p.user_id = u.id
        JOIN friends f ON (
            (f.user_id = ? AND f.friend_id = p.user_id AND f.status = 'accepted') OR
            (f.friend_id = ? AND f.user_id = p.user_id AND f.status = 'accepted')
        )
        LEFT JOIN reactions r ON p.id = r.post_id
        LEFT JOIN comments c ON p.id = c.post_id
        LEFT JOIN reactions ur ON p.id = ur.post_id AND ur.user_id = ?
        WHERE u.approved = 1
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $friend_posts = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback for old database
    $stmt = $pdo->prepare("
        SELECT p.id, p.content, p.created_at, u.username, NULL as file_path, NULL as file_type,
               0 as reaction_count, 0 as comment_count, NULL as user_reaction
        FROM posts p 
        JOIN users u ON p.user_id = u.id
        JOIN friends f ON (
            (f.user_id = ? AND f.friend_id = p.user_id AND f.status = 'accepted') OR
            (f.friend_id = ? AND f.user_id = p.user_id AND f.status = 'accepted')
        )
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $friend_posts = $stmt->fetchAll();
}
// Set page variables for header
$page_title = 'My Friends - OSRG Connect';
$additional_css = '
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .content-wrapper { display: flex; gap: 20px; }
        .friends-sidebar { width: 300px; }
        .feed-main { flex: 1; }
        
        @media (max-width: 768px) {
            .content-wrapper { flex-direction: column; }
            .friends-sidebar { width: 100%; }
        }
        .header { background: #1877f2; color: white; padding: 15px; text-align: center; }
        .post { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .friend-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #eee; }
        .friend-info { display: flex; flex-direction: column; }
        .friend-name { font-weight: bold; font-size: 16px; }
        .friend-since { color: #666; font-size: 12px; }
';

require_once 'header.php';
?>
    
    <div class="container">
        <div class="content-wrapper">
            <!-- Left Sidebar: Friends List -->
            <div class="friends-sidebar">
                <div class="post">
                    <h3>Friends List</h3>
                    <?php if ($friends): ?>
                        <?php foreach ($friends as $friend): ?>
                        <div class="friend-item">
                            <div class="friend-info">
                                <span class="friend-name"><?= htmlspecialchars($friend['username']) ?></span>
                                <span class="friend-since">Friends since <?= date('M j, Y', strtotime($friend['created_at'])) ?></span>
                            </div>
                            <span style="color: #4caf50; font-size: 18px;">✓</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #666; padding: 20px;">
                            You don't have any friends yet.<br>
                            <a href="users.php" style="color: #1877f2;">Find some friends!</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Main: Friends Feed -->
            <div class="feed-main">
                <div class="header" style="margin-top: 0; margin-bottom: 20px;">
                    <h1>Friends Feed</h1>
                </div>

                <?php if ($friend_posts): ?>
                    <?php foreach ($friend_posts as $post): ?>
                    <div class="post">
                        <p><strong><?= htmlspecialchars($post['username']) ?></strong></p>
                        <p><?= htmlspecialchars($post['content']) ?></p>
                        
                        <?php if ($post['file_path']): ?>
                        <div style="margin: 10px 0;">
                            <?php if ($post['file_type'] == 'mp4'): ?>
                                <video controls style="width: 100%; max-width: 100%; display: block;">
                                    <source src="<?= $post['file_path'] ?>" type="video/mp4">
                                </video>
                            <?php elseif ($post['file_type'] == 'mp3'): ?>
                                <audio controls preload="metadata" style="width: 100%; display: block;">
                                    <source src="<?= $post['file_path'] ?>" type="audio/mpeg">
                                    <source src="<?= $post['file_path'] ?>" type="audio/mp3">
                                    Your browser does not support the audio element.
                                </audio>
                            <?php elseif (in_array($post['file_type'], ['png', 'jpg', 'jpeg'])): ?>
                                <img src="<?= htmlspecialchars($post['file_path']) ?>" alt="Uploaded image" style="max-width: 300px; width: auto; display: block; border-radius: 8px;">
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Reactions -->
                        <div style="margin: 15px 0; padding: 10px 0; border-top: 1px solid #eee;">
                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" name="reaction" value="<?= $post['user_reaction'] === 'like' ? 'remove' : 'like' ?>" 
                                            style="background: none; border: none; font-size: 16px; cursor: pointer; <?= $post['user_reaction'] === 'like' ? 'color: #1877f2;' : '' ?>">
                                        👍 <?= $post['reaction_count'] > 0 ? $post['reaction_count'] : '' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" name="reaction" value="<?= $post['user_reaction'] === 'love' ? 'remove' : 'love' ?>" 
                                            style="background: none; border: none; font-size: 16px; cursor: pointer; <?= $post['user_reaction'] === 'love' ? 'color: #e91e63;' : '' ?>">
                                        ❤️
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" name="reaction" value="<?= $post['user_reaction'] === 'laugh' ? 'remove' : 'laugh' ?>" 
                                            style="background: none; border: none; font-size: 16px; cursor: pointer; <?= $post['user_reaction'] === 'laugh' ? 'color: #ff9800;' : '' ?>">
                                        😂
                                    </button>
                                </form>
                                <span style="color: #666; font-size: 14px;">💬 <?= $post['comment_count'] ?></span>
                            </div>
                            
                            <!-- Comments -->
                            <?php
                            $stmt_comments = $pdo->prepare("SELECT c.content, c.created_at, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
                            $stmt_comments->execute([$post['id']]);
                            $comments = $stmt_comments->fetchAll();
                            ?>
                            
                            <?php if ($comments): ?>
                                <?php foreach ($comments as $comment): ?>
                                <div style="background: #f8f9fa; padding: 8px; margin: 5px 0; border-radius: 5px; font-size: 14px;">
                                    <strong><?= htmlspecialchars($comment['username']) ?>:</strong> 
                                    <?= htmlspecialchars($comment['content']) ?>
                                    <small style="color: #666; margin-left: 10px;"><?= date('M j, H:i', strtotime($comment['created_at'])) ?></small>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <!-- Add Comment -->
                            <form method="POST" style="margin-top: 10px;">
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
                                <a href="users.php" style="color: #1877f2;">Add some friends to see their posts!</a>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>