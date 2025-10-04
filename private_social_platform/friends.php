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

// Handle delete post
if ($_GET['delete_post'] ?? false) {
    $post_id = $_GET['delete_post'];
    // Verify user owns the post
    $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post_owner = $stmt->fetch();
    
    if ($post_owner && $post_owner['user_id'] == $_SESSION['user_id']) {
        // Delete related data first
        $stmt = $pdo->prepare("DELETE FROM reactions WHERE post_id = ?");
        $stmt->execute([$post_id]);
        $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
        $stmt->execute([$post_id]);
        // Delete the post
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
    }
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
        SELECT p.id, p.content, p.created_at, u.username, u.avatar, p.file_path, p.file_type, p.post_type,
               COUNT(DISTINCT CASE WHEN r.reaction_type = 'like' THEN r.id END) as like_count,
               COUNT(DISTINCT CASE WHEN r.reaction_type = 'love' THEN r.id END) as love_count,
               COUNT(DISTINCT CASE WHEN r.reaction_type = 'laugh' THEN r.id END) as laugh_count,
               COUNT(DISTINCT c.id) as comment_count,
               ur.reaction_type as user_reaction
        FROM posts p 
        JOIN users u ON p.user_id = u.id
        LEFT JOIN friends f ON (
            (f.user_id = ? AND f.friend_id = p.user_id AND f.status = 'accepted') OR
            (f.friend_id = ? AND f.user_id = p.user_id AND f.status = 'accepted')
        )
        LEFT JOIN reactions r ON p.id = r.post_id
        LEFT JOIN comments c ON p.id = c.post_id
        LEFT JOIN reactions ur ON p.id = ur.post_id AND ur.user_id = ?
        WHERE u.approved = 1 AND (f.id IS NOT NULL OR p.user_id = ?) AND (p.post_type IS NULL OR p.post_type = 'post')
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $friend_posts = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback for old database
    $stmt = $pdo->prepare("
        SELECT p.id, p.content, p.created_at, u.username, u.avatar, NULL as file_path, NULL as file_type, 'post' as post_type,
               0 as like_count, 0 as love_count, 0 as laugh_count, 0 as comment_count, NULL as user_reaction
        FROM posts p 
        JOIN users u ON p.user_id = u.id
        LEFT JOIN friends f ON (
            (f.user_id = ? AND f.friend_id = p.user_id AND f.status = 'accepted') OR
            (f.friend_id = ? AND f.user_id = p.user_id AND f.status = 'accepted')
        )
        WHERE u.approved = 1 AND (f.id IS NOT NULL OR p.user_id = ?) AND (p.post_type IS NULL OR p.post_type = 'post')
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
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
$mobile_viewport = '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';

// Add Open Graph meta tags
$og_tags = '
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="OSRG Connect - Friends Feed">
    <meta property="og:description" content="Stay connected with your friends on OSRG Connect - View posts from your friend network.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://osrg.lol/osrg/private_social_platform/friends.php">
    <meta property="og:site_name" content="OSRG Connect">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="OSRG Connect - Friends Feed">
    <meta name="twitter:description" content="Stay connected with your friends on OSRG Connect - View posts from your friend network.">
    
    <!-- Additional Meta Tags -->
    <meta name="description" content="OSRG Connect Friends Feed - See what your friends are sharing and stay connected with your network.">
    <meta name="keywords" content="friends feed, social media, private platform, friend posts, OSRG">
    <meta name="author" content="OSRG">
';

require_once 'header.php';
?>
<?= $og_tags ?>
<script>
    function editPost(postId) {
        const contentDiv = document.getElementById('content-' + postId);
        const currentContent = contentDiv.textContent.trim();
        
        const textarea = document.createElement('textarea');
        textarea.value = currentContent;
        textarea.style.width = '100%';
        textarea.style.minHeight = '100px';
        textarea.style.padding = '10px';
        textarea.style.border = '2px solid #1877f2';
        textarea.style.borderRadius = '8px';
        textarea.style.fontSize = '16px';
        
        const saveBtn = document.createElement('button');
        saveBtn.textContent = 'Save';
        saveBtn.style.background = '#4caf50';
        saveBtn.style.color = 'white';
        saveBtn.style.padding = '8px 15px';
        saveBtn.style.border = 'none';
        saveBtn.style.borderRadius = '5px';
        saveBtn.style.marginRight = '10px';
        saveBtn.style.cursor = 'pointer';
        
        const cancelBtn = document.createElement('button');
        cancelBtn.textContent = 'Cancel';
        cancelBtn.style.background = '#f44336';
        cancelBtn.style.color = 'white';
        cancelBtn.style.padding = '8px 15px';
        cancelBtn.style.border = 'none';
        cancelBtn.style.borderRadius = '5px';
        cancelBtn.style.cursor = 'pointer';
        
        const buttonDiv = document.createElement('div');
        buttonDiv.style.marginTop = '10px';
        buttonDiv.appendChild(saveBtn);
        buttonDiv.appendChild(cancelBtn);
        
        contentDiv.innerHTML = '';
        contentDiv.appendChild(textarea);
        contentDiv.appendChild(buttonDiv);
        
        saveBtn.onclick = function() {
            const newContent = textarea.value.trim();
            if (newContent) {
                fetch('edit_post.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'post_id=' + postId + '&content=' + encodeURIComponent(newContent)
                })
                .then(response => response.text())
                .then(result => {
                    if (result === 'success') {
                        location.reload();
                    } else {
                        alert('Error updating post');
                    }
                });
            }
        };
        
        cancelBtn.onclick = function() {
            contentDiv.innerHTML = currentContent;
        };
    }
    
    window.onload = function() {
        // Restore scroll position after comment submission
        const scrollPos = sessionStorage.getItem('scrollPos');
        if (scrollPos) {
            // Mobile detection
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth <= 768;
            
            if (isMobile) {
                // Use setTimeout for mobile compatibility and prevent iOS scroll issues
                setTimeout(() => {
                    window.scrollTo(0, parseInt(scrollPos));
                    sessionStorage.removeItem('scrollPos');
                }, 100);
            } else {
                // Immediate scroll for desktop
                window.scrollTo(0, parseInt(scrollPos));
                sessionStorage.removeItem('scrollPos');
            }
        }
    }
</script>
    
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
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <?php if ($post['avatar']): ?>
                                    <?php if (strpos($post['avatar'], 'avatars/') === 0): ?>
                                        <img src="/<?= htmlspecialchars($post['avatar']) ?>" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <span style="font-size: 30px;"><?= htmlspecialchars($post['avatar']) ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="font-size: 30px;">👤</span>
                                <?php endif; ?>
                                <div>
                                    <strong><?= htmlspecialchars($post['username']) ?></strong>
                                    <?php if (($post['post_type'] ?? 'post') === 'reel'): ?>
                                        <span style="background: linear-gradient(45deg, #ff6b6b, #4ecdc4); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; margin-left: 8px;">🎬 REEL</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                            // Check if current user owns this post
                            $stmt_owner = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
                            $stmt_owner->execute([$post['id']]);
                            $post_owner = $stmt_owner->fetch();
                            if ($post_owner && $post_owner['user_id'] == $_SESSION['user_id']):
                            ?>
                            <div style="display: flex; gap: 5px;">
                                <button onclick="editPost(<?= $post['id'] ?>)" style="background: #1877f2; color: white; padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 12px;">Edit</button>
                                <a href="?delete_post=<?= $post['id'] ?>" onclick="return confirm('Are you sure you want to delete this post?')" style="background: #f44336; color: white; padding: 6px 12px; text-decoration: none; border-radius: 5px; font-size: 12px;">Delete</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php
                        $processed = process_content_with_links($post['content']);
                        ?>
                        <div id="content-<?= $post['id'] ?>"><?= $processed['content'] ?></div>
                        
                        <?php if (!empty($processed['previews'])): ?>
                            <?php foreach ($processed['previews'] as $preview): ?>
                            <div style="border: 1px solid #e1e5e9; border-radius: 8px; margin: 10px 0; overflow: hidden; max-width: 400px;">
                                <?php if ($preview['image']): ?>
                                <img src="<?= htmlspecialchars($preview['image']) ?>" alt="Preview" style="width: 100%; height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                <div style="padding: 12px;">
                                    <div style="font-weight: bold; margin-bottom: 5px; color: #1d2129;"><?= htmlspecialchars($preview['title']) ?></div>
                                    <?php if ($preview['description']): ?>
                                    <div style="color: #606770; font-size: 14px; margin-bottom: 8px;"><?= htmlspecialchars(substr($preview['description'], 0, 100)) ?>...</div>
                                    <?php endif; ?>
                                    <div style="color: #8a8d91; font-size: 12px; text-transform: uppercase;"><?= parse_url($preview['url'], PHP_URL_HOST) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
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
                                            style="background: none; border: none; font-size: 20px; cursor: pointer; padding: 10px; touch-action: manipulation; <?= $post['user_reaction'] === 'like' ? 'color: #1877f2;' : '' ?>">
                                        👍 <?= $post['like_count'] > 0 ? $post['like_count'] : '' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" name="reaction" value="<?= $post['user_reaction'] === 'love' ? 'remove' : 'love' ?>" 
                                            style="background: none; border: none; font-size: 20px; cursor: pointer; padding: 10px; touch-action: manipulation; <?= $post['user_reaction'] === 'love' ? 'color: #e91e63;' : '' ?>">
                                        ❤️ <?= $post['love_count'] > 0 ? $post['love_count'] : '' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" name="reaction" value="<?= $post['user_reaction'] === 'laugh' ? 'remove' : 'laugh' ?>" 
                                            style="background: none; border: none; font-size: 20px; cursor: pointer; padding: 10px; touch-action: manipulation; <?= $post['user_reaction'] === 'laugh' ? 'color: #ff9800;' : '' ?>">
                                        😂 <?= $post['laugh_count'] > 0 ? $post['laugh_count'] : '' ?>
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
                            <form method="POST" style="margin-top: 10px;" onsubmit="sessionStorage.setItem('scrollPos', window.pageYOffset);">
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