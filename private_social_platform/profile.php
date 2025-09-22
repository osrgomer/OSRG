<?php
require_once 'config.php';
init_db();

if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}

$pdo = get_db();
$user_id = $_GET['id'] ?? $_SESSION['user_id'];

// Get user data with fallback for missing columns
try {
    $stmt = $pdo->prepare("SELECT username, email, bio, avatar, created_at, last_seen FROM users WHERE id = ? AND approved = 1");
    $stmt->execute([$user_id]);
    $profile_user = $stmt->fetch();
} catch (Exception $e) {
    // Fallback for missing columns
    try {
        $stmt = $pdo->prepare("SELECT username, email, created_at, avatar FROM users WHERE id = ? AND approved = 1");
        $stmt->execute([$user_id]);
        $profile_user = $stmt->fetch();
        if ($profile_user) {
            $profile_user['bio'] = '';
            $profile_user['last_seen'] = date('Y-m-d H:i:s');
        }
    } catch (Exception $e2) {
        // Ultimate fallback
        $stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ? AND approved = 1");
        $stmt->execute([$user_id]);
        $profile_user = $stmt->fetch();
        if ($profile_user) {
            $profile_user['bio'] = '';
            $profile_user['last_seen'] = date('Y-m-d H:i:s');
            $profile_user['avatar'] = null;
        }
    }
}

if (!$profile_user) {
    header('Location: home');
    exit;
}

// Check if user is online (active within last 5 minutes)
$is_online = isset($profile_user['last_seen']) ? (strtotime($profile_user['last_seen']) > (time() - 300)) : false;

// Get user's posts
try {
    $stmt = $pdo->prepare("SELECT p.*, COUNT(r.id) as reaction_count FROM posts p LEFT JOIN reactions r ON p.id = r.post_id WHERE p.user_id = ? GROUP BY p.id ORDER BY p.created_at DESC");
    $stmt->execute([$user_id]);
    $user_posts = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback for database issues
    try {
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $user_posts = $stmt->fetchAll();
        // Add reaction_count and edited_at as defaults for each post
        foreach ($user_posts as &$post) {
            $post['reaction_count'] = 0;
            if (!isset($post['edited_at'])) {
                $post['edited_at'] = null;
            }
        }
    } catch (Exception $e2) {
        $user_posts = [];
    }
}

// Get user stats
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as post_count FROM posts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $post_count = $stmt->fetch()['post_count'] ?? 0;

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT CASE WHEN user_id = ? THEN friend_id WHEN friend_id = ? THEN user_id END) as friend_count FROM friends WHERE (user_id = ? OR friend_id = ?) AND status = 'accepted'");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
    $friend_count = $stmt->fetch()['friend_count'] ?? 0;
} catch (Exception $e) {
    // Fallback values
    $post_count = 0;
    $friend_count = 0;
}

$is_own_profile = ($user_id == $_SESSION['user_id']);
?>
<?php
$page_title = htmlspecialchars($profile_user['username']) . ' - Profile';
$additional_css = '
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .profile-header { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); padding: 30px; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); text-align: center; }
        .avatar-large { width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 20px; object-fit: cover; border: 4px solid #1877f2; }
        .username { font-size: 2em; color: #1877f2; margin-bottom: 10px; }
        .online-status { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 20px; font-size: 14px; font-weight: 600; }
        .online { background: #e8f5e8; color: #2e7d32; }
        .offline { background: #f5f5f5; color: #666; }
        .bio { margin: 15px 0; color: #666; font-style: italic; }
        .stats { display: flex; justify-content: center; gap: 30px; margin-top: 20px; }
        .stat { text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #1877f2; }
        .stat-label { color: #666; font-size: 14px; }
        .post { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); padding: 20px; margin: 15px 0; border-radius: 15px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); }
        .post-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .post-content { margin: 15px 0; line-height: 1.6; }
        .post-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(0,0,0,0.1); }
        .edit-btn { background: #1877f2; color: white; padding: 8px 15px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; }
        .edit-btn:hover { background: #0d47a1; }
';
require_once 'header.php';
?>

<div class="container">
    <div class="profile-header">
        <?php 
        $user_avatar = $profile_user['avatar'] ?? null;
        $random_avatars = ['👤', '👨', '👩', '🧑', '👶', '🐱', '🐶', '🦊'];
        $default_avatar = $random_avatars[($user_id ?? 0) % count($random_avatars)];
        ?>
        <?php if ($user_avatar && strpos($user_avatar, 'avatars/') === 0): ?>
            <img src="<?= htmlspecialchars($user_avatar) ?>" alt="Avatar" class="avatar-large">
        <?php elseif ($user_avatar): ?>
            <div style="font-size: 120px; margin-bottom: 20px;"><?= htmlspecialchars($user_avatar) ?></div>
        <?php else: ?>
            <div style="font-size: 120px; margin-bottom: 20px;"><?= $default_avatar ?></div>
        <?php endif; ?>
        
        <h1 class="username"><?= htmlspecialchars($profile_user['username']) ?></h1>
        
        <div class="online-status <?= $is_online ? 'online' : 'offline' ?>">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: <?= $is_online ? '#4caf50' : '#999' ?>;"></span>
            <?= $is_online ? 'Online' : 'Last seen ' . date('M j, H:i', strtotime($profile_user['last_seen'])) ?>
        </div>
        
        <?php if ($profile_user['bio']): ?>
            <div class="bio">"<?= htmlspecialchars($profile_user['bio']) ?>"</div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat">
                <div class="stat-number"><?= $post_count ?></div>
                <div class="stat-label">Posts</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?= $friend_count ?></div>
                <div class="stat-label">Friends</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?= date('M Y', strtotime($profile_user['created_at'])) ?></div>
                <div class="stat-label">Joined</div>
            </div>
        </div>
        
        <?php if ($is_own_profile): ?>
            <div style="margin-top: 20px;">
                <a href="settings#profile" style="background: #1877f2; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: 600;">Edit Profile</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="post">
        <h3 style="color: #1877f2; margin-bottom: 20px;">📝 Posts by <?= htmlspecialchars($profile_user['username']) ?></h3>
        
        <?php if ($user_posts): ?>
            <?php foreach ($user_posts as $post): ?>
                <div style="border-bottom: 1px solid rgba(0,0,0,0.1); padding: 20px 0;">
                    <div class="post-header">
                        <div>
                            <strong><?= htmlspecialchars($profile_user['username']) ?></strong>
                            <small style="color: #666; margin-left: 10px;">
                                <?= date('M j, Y H:i', strtotime($post['created_at'])) ?>
                                <?php if ($post['edited_at']): ?>
                                    <span style="color: #999;"> • edited</span>
                                <?php endif; ?>
                            </small>
                        </div>
                        <?php if ($is_own_profile): ?>
                            <button class="edit-btn" onclick="editPost(<?= $post['id'] ?>)">Edit</button>
                        <?php endif; ?>
                    </div>
                    <div class="post-content" id="content-<?= $post['id'] ?>">
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                    </div>
                    <div class="post-footer">
                        <span style="color: #666;">❤️ <?= $post['reaction_count'] ?> reactions</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #666; padding: 40px;">No posts yet</p>
        <?php endif; ?>
    </div>
</div>

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
        contentDiv.innerHTML = currentContent.replace(/\n/g, '<br>');
    };
}
</script>
</body>
</html>