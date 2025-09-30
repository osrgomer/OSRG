<?php
require_once 'config.php';
init_db();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$pdo = get_db();
$user_id = $_GET['id'] ?? $_SESSION['user_id'];

// Get basic user data with bio
try {
    $stmt = $pdo->prepare("SELECT username, email, created_at, avatar, bio FROM users WHERE id = ? AND approved = 1");
    $stmt->execute([$user_id]);
    $profile_user = $stmt->fetch();
} catch (Exception $e) {
    // Fallback without bio column
    $stmt = $pdo->prepare("SELECT username, email, created_at, avatar FROM users WHERE id = ? AND approved = 1");
    $stmt->execute([$user_id]);
    $profile_user = $stmt->fetch();
    if ($profile_user) $profile_user['bio'] = null;
}

if (!$profile_user) {
    header('Location: /home');
    exit;
}

// Get user's posts
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$user_posts = $stmt->fetchAll();

// Get post count
$post_count = count($user_posts);

$is_own_profile = ($user_id == $_SESSION['user_id']);

// Set variables for header.php
$page_title = htmlspecialchars($profile_user['username']) . ' - Profile';
$additional_css = '
        .profile-header { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); padding: 30px; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); text-align: center; }
        .avatar-large { width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 20px; object-fit: cover; border: 4px solid #1877f2; }
        .username { font-size: 2em; color: #1877f2; margin-bottom: 10px; }
        .stats { display: flex; justify-content: center; gap: 30px; margin-top: 20px; }
        .stat { text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #1877f2; }
        .stat-label { color: #666; font-size: 14px; }
        .post { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); padding: 20px; margin: 15px 0; border-radius: 15px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); }
        .post-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .post-content { margin: 15px 0; line-height: 1.6; }
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
        
        <?php if (!empty($profile_user['bio'])): ?>
            <div style="color: #666; font-size: 16px; margin: 15px 0; max-width: 400px; margin-left: auto; margin-right: auto;">
                <?= nl2br(htmlspecialchars($profile_user['bio'])) ?>
            </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat">
                <div class="stat-number"><?= $post_count ?></div>
                <div class="stat-label">Posts</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?= date('M Y', strtotime($profile_user['created_at'])) ?></div>
                <div class="stat-label">Joined</div>
            </div>
        </div>
        
        <?php if ($is_own_profile): ?>
            <div style="margin-top: 20px;">
                <a href="/settings" style="background: #1877f2; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: 600;">Edit Profile</a>
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
                            </small>
                        </div>
                    </div>
                    <div class="post-content">
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #666; padding: 40px;">No posts yet</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>