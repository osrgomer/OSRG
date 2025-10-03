<?php
require_once 'config.php';
init_db();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$pdo = get_db();

// Handle new reel creation
if (isset($_POST['content'])) {
    $file_path = null;
    $file_type = null;
    
    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $allowed = ['mp4', 'mov', 'avi'];
        $filename = $_FILES['file']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file_size = $_FILES['file']['size'];
        $max_size = 52 * 1024 * 1024; // 52MB limit
        
        if ($file_size <= $max_size && in_array($file_ext, $allowed)) {
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }
            
            $new_filename = uniqid() . '.' . $file_ext;
            $upload_path = 'uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                $file_path = $upload_path;
                $file_type = $file_ext;
            }
        }
    }
    
    // Require video file for reels
    if ($file_path && in_array($file_type, ['mp4', 'mov', 'avi'])) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, file_path, file_type, post_type) VALUES (?, ?, ?, ?, 'reel')");
        $stmt->execute([$_SESSION['user_id'], $_POST['content'], $file_path, $file_type]);
    }
    
    header('Location: /reels');
    exit;
}

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
    
    header('Location: /reels');
    exit;
}

// Handle comments
if ($_POST['comment'] ?? false) {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['post_id'], $_SESSION['user_id'], $_POST['comment']]);
    header('Location: /reels');
    exit;
}

// Get all reels
try {
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
        WHERE u.approved = 1 AND p.post_type = 'reel'
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $reels = $stmt->fetchAll();
} catch (Exception $e) {
    $reels = [];
}

$page_title = 'Reels - OSRG Connect';
$additional_css = '
    .reel-container { max-width: 400px; margin: 0 auto; }
    .reel-item { background: white; margin: 20px 0; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .reel-video { width: 100%; height: 600px; object-fit: cover; }
    .reel-content { padding: 15px; }
    .create-reel { background: linear-gradient(135deg, #ff6b6b, #4ecdc4); color: white; padding: 20px; border-radius: 15px; margin-bottom: 20px; }
    @media (max-width: 768px) {
        .reel-container { max-width: 100%; }
        .reel-video { height: 500px; }
    }
';

require_once 'header.php';
?>

<div class="reel-container">
    <div class="create-reel">
        <h2>🎬 Create a Reel</h2>
        <form method="POST" enctype="multipart/form-data" id="reelForm">
            <div style="margin: 15px 0;">
                <textarea name="content" placeholder="Add a caption to your reel..." style="width: 100%; padding: 10px; border: none; border-radius: 8px; min-height: 80px; resize: vertical;"></textarea>
            </div>
            <div style="margin: 15px 0;">
                <input type="file" name="file" accept=".mp4,.mov,.avi" required style="width: 100%; padding: 10px; background: white; border-radius: 8px;" id="videoFile">
                <small style="color: rgba(255,255,255,0.8); display: block; margin-top: 5px;">Upload Video: MP4, MOV, AVI (max 52MB)</small>
            </div>
            <button type="submit" id="submitBtn" style="background: rgba(255,255,255,0.2); border: 2px solid white; color: white; padding: 12px 25px; border-radius: 25px; font-weight: bold;">Create Reel</button>
            <div id="uploadStatus" style="margin-top: 15px; padding: 10px; border-radius: 8px; display: none;"></div>
        </form>
    </div>

    <?php if ($reels): ?>
        <?php foreach ($reels as $reel): ?>
        <div class="reel-item">
            <video class="reel-video" controls>
                <source src="/<?= htmlspecialchars($reel['file_path']) ?>" type="video/<?= $reel['file_type'] ?>">
            </video>
            
            <div class="reel-content">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <?php if ($reel['avatar'] && strpos($reel['avatar'], 'avatars/') === 0): ?>
                        <img src="/<?= htmlspecialchars($reel['avatar']) ?>" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    <?php elseif ($reel['avatar']): ?>
                        <span style="font-size: 30px;"><?= htmlspecialchars($reel['avatar']) ?></span>
                    <?php else: ?>
                        <span style="font-size: 30px;">👤</span>
                    <?php endif; ?>
                    <div>
                        <strong><?= htmlspecialchars($reel['username']) ?></strong>
                        <span style="background: linear-gradient(45deg, #ff6b6b, #4ecdc4); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; margin-left: 8px;">🎬 REEL</span>
                    </div>
                </div>
                
                <?php if ($reel['content']): ?>
                    <div style="margin: 10px 0;"><?= nl2br(htmlspecialchars($reel['content'])) ?></div>
                <?php endif; ?>
                
                <!-- Reactions -->
                <div style="display: flex; gap: 15px; margin: 15px 0; padding: 10px 0; border-top: 1px solid #eee;">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="post_id" value="<?= $reel['id'] ?>">
                        <button type="submit" name="reaction" value="<?= $reel['user_reaction'] === 'like' ? 'remove' : 'like' ?>" 
                                style="background: none; border: none; font-size: 18px; cursor: pointer; <?= $reel['user_reaction'] === 'like' ? 'color: #1877f2;' : '' ?>">
                            👍 <?= $reel['like_count'] > 0 ? $reel['like_count'] : '' ?>
                        </button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="post_id" value="<?= $reel['id'] ?>">
                        <button type="submit" name="reaction" value="<?= $reel['user_reaction'] === 'love' ? 'remove' : 'love' ?>" 
                                style="background: none; border: none; font-size: 18px; cursor: pointer; <?= $reel['user_reaction'] === 'love' ? 'color: #e91e63;' : '' ?>">
                            ❤️ <?= $reel['love_count'] > 0 ? $reel['love_count'] : '' ?>
                        </button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="post_id" value="<?= $reel['id'] ?>">
                        <button type="submit" name="reaction" value="<?= $reel['user_reaction'] === 'laugh' ? 'remove' : 'laugh' ?>" 
                                style="background: none; border: none; font-size: 18px; cursor: pointer; <?= $reel['user_reaction'] === 'laugh' ? 'color: #ff9800;' : '' ?>">
                            😂 <?= $reel['laugh_count'] > 0 ? $reel['laugh_count'] : '' ?>
                        </button>
                    </form>
                    <span style="color: #666; font-size: 14px;">💬 <?= $reel['comment_count'] ?></span>
                </div>
                
                <!-- Comments -->
                <?php
                $stmt_comments = $pdo->prepare("SELECT c.content, c.created_at, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
                $stmt_comments->execute([$reel['id']]);
                $comments = $stmt_comments->fetchAll();
                ?>
                
                <?php if ($comments): ?>
                    <?php foreach ($comments as $comment): ?>
                    <div style="background: #f8f9fa; padding: 8px; margin: 5px 0; border-radius: 8px; font-size: 14px;">
                        <strong><?= htmlspecialchars($comment['username']) ?>:</strong> 
                        <?= htmlspecialchars($comment['content']) ?>
                        <small style="color: #666; margin-left: 10px;"><?= date('M j, H:i', strtotime($comment['created_at'])) ?></small>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <!-- Add Comment -->
                <form method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="post_id" value="<?= $reel['id'] ?>">
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="comment" placeholder="Write a comment..." style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 20px; font-size: 14px;" required>
                        <button type="submit" style="padding: 8px 15px; font-size: 12px; background: #1877f2; color: white; border: none; border-radius: 20px;">Post</button>
                    </div>
                </form>
                
                <div style="margin-top: 10px;">
                    <small style="color: #666;"><?= date('M j, H:i', strtotime($reel['created_at'])) ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <h3>No reels yet! 🎬</h3>
            <p>Be the first to create a reel and share it with everyone!</p>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('reelForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('videoFile');
    const submitBtn = document.getElementById('submitBtn');
    const status = document.getElementById('uploadStatus');
    
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const maxSize = 52 * 1024 * 1024; // 52MB
        
        if (file.size > maxSize) {
            e.preventDefault();
            status.style.display = 'block';
            status.style.background = 'rgba(255,0,0,0.2)';
            status.style.color = 'white';
            status.innerHTML = '❌ File too large! Maximum size is 52MB.';
            return;
        }
        
        // Show uploading status
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Creating Reel...';
        status.style.display = 'block';
        status.style.background = 'rgba(255,255,255,0.2)';
        status.style.color = 'white';
        status.innerHTML = '📤 Uploading your reel... Please wait!';
    }
});

// File selection feedback
document.getElementById('videoFile').addEventListener('change', function(e) {
    const status = document.getElementById('uploadStatus');
    if (e.target.files.length > 0) {
        const file = e.target.files[0];
        const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
        status.style.display = 'block';
        status.style.background = 'rgba(255,255,255,0.2)';
        status.style.color = 'white';
        status.innerHTML = `✅ Video selected: ${file.name} (${sizeMB}MB)`;
    }
});
</script>

</body>
</html>