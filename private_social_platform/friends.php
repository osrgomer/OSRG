<?php
require_once 'config.php';
init_db();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$pdo = get_db();

// Handle reactions
if ($_POST['reaction'] ?? false) {
    $post_id = $_POST['post_id'];
    $reaction = $_POST['reaction'];
    
    $stmt = $pdo->prepare("DELETE FROM reactions WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
    
    if ($reaction !== 'remove') {
        $stmt = $pdo->prepare("INSERT INTO reactions (post_id, user_id, reaction_type) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $_SESSION['user_id'], $reaction]);
    }
    
    header('Location: /friends');
    exit;
}

// Handle comments
if ($_POST['comment'] ?? false) {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['post_id'], $_SESSION['user_id'], $_POST['comment']]);
    header('Location: /friends');
    exit;
}

// Get friends
try {
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
} catch (Exception $e) {
    $friends = [];
}

// Get friend IDs
$friend_ids = array_column($friends, 'id');
$friend_ids[] = $_SESSION['user_id']; // Include your own posts

// Get posts from friends and yourself
try {
    if (!empty($friend_ids)) {
        $placeholders = str_repeat('?,', count($friend_ids) - 1) . '?';
        $stmt = $pdo->prepare("
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
            WHERE u.approved = 1 AND p.user_id IN ($placeholders) AND (p.post_type IS NULL OR p.post_type = 'post')
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ");
        $params = array_merge([$_SESSION['user_id']], $friend_ids);
        $stmt->execute($params);
        $friend_posts = $stmt->fetchAll();
    } else {
        $friend_posts = [];
    }
} catch (Exception $e) {
    $friend_posts = [];
}

$page_title = 'My Friends - OSRG Connect';
require_once 'header.php';
?>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <div style="display: flex; gap: 20px;">
        <!-- Friends List -->
        <div style="width: 300px;">
            <div style="background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3>Friends List</h3>
                <?php if ($friends): ?>
                    <?php foreach ($friends as $friend): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #eee;">
                        <div>
                            <div style="font-weight: bold;"><?= htmlspecialchars($friend['username']) ?></div>
                            <div style="color: #666; font-size: 12px;">Friends since <?= date('M j, Y', strtotime($friend['created_at'])) ?></div>
                        </div>
                        <span style="color: #4caf50; font-size: 18px;">✓</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 20px;">
                        No friends yet.<br>
                        <a href="/find-friends" style="color: #1877f2;">Find some friends!</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Friends Feed -->
        <div style="flex: 1;">
            <div style="background: #1877f2; color: white; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 8px;">
                <h1>Friends Feed</h1>
            </div>

            <?php if ($friend_posts): ?>
                <?php foreach ($friend_posts as $post): ?>
                <div style="background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <?php if ($post['avatar'] && strpos($post['avatar'], 'avatars/') === 0): ?>
                            <img src="/<?= htmlspecialchars($post['avatar']) ?>" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <?php elseif ($post['avatar']): ?>
                            <span style="font-size: 30px;"><?= htmlspecialchars($post['avatar']) ?></span>
                        <?php else: ?>
                            <span style="font-size: 30px;">👤</span>
                        <?php endif; ?>
                        <strong><?= htmlspecialchars($post['username']) ?></strong>
                    </div>
                    
                    <div><?= nl2br(htmlspecialchars($post['content'])) ?></div>
                    
                    <?php if ($post['file_path']): ?>
                    <div style="margin: 10px 0;">
                        <?php if ($post['file_type'] == 'mp4'): ?>
                            <video controls style="width: 100%; max-width: 100%;">
                                <source src="/<?= htmlspecialchars($post['file_path']) ?>" type="video/mp4">
                            </video>
                        <?php elseif (in_array($post['file_type'], ['png', 'jpg', 'jpeg'])): ?>
                            <img src="/<?= htmlspecialchars($post['file_path']) ?>" alt="Image" style="max-width: 100%; border-radius: 8px;">
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Reactions -->
                    <div style="margin: 15px 0; padding: 10px 0; border-top: 1px solid #eee;">
                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit" name="reaction" value="<?= $post['user_reaction'] === 'like' ? 'remove' : 'like' ?>" 
                                        style="background: none; border: none; font-size: 20px; cursor: pointer; <?= $post['user_reaction'] === 'like' ? 'color: #1877f2;' : '' ?>">
                                    👍 <?= $post['like_count'] > 0 ? $post['like_count'] : '' ?>
                                </button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit" name="reaction" value="<?= $post['user_reaction'] === 'love' ? 'remove' : 'love' ?>" 
                                        style="background: none; border: none; font-size: 20px; cursor: pointer; <?= $post['user_reaction'] === 'love' ? 'color: #e91e63;' : '' ?>">
                                    ❤️ <?= $post['love_count'] > 0 ? $post['love_count'] : '' ?>
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
                                <input type="text" name="comment" placeholder="Write a comment..." style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 20px;" required>
                                <button type="submit" style="background: #1877f2; color: white; padding: 8px 15px; border: none; border-radius: 20px;">Post</button>
                            </div>
                        </form>
                    </div>
                    
                    <small style="color: #666;"><?= date('M j, H:i', strtotime($post['created_at'])) ?></small>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <p style="text-align: center; color: #666; padding: 20px;">
                        No posts from friends yet.<br>
                        <?php if (!$friends): ?>
                            <a href="/find-friends" style="color: #1877f2;">Add some friends to see their posts!</a><br><br>
                            <small>Debug: Friends count: <?= count($friends) ?>, Posts count: <?= count($friend_posts) ?></small>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>