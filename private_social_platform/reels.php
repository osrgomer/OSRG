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
    $upload_error = null;
    
    // Handle file upload
    if (isset($_FILES['file'])) {
        if ($_FILES['file']['error'] !== 0) {
            $upload_error = 'Upload failed. Error code: ' . $_FILES['file']['error'];
        } else {
            $allowed = ['mp4', 'mov', 'avi'];
            $filename = $_FILES['file']['name'];
            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $file_size = $_FILES['file']['size'];
            $max_size = 10 * 1024 * 1024; // 10MB limit
            
            if ($file_size <= $max_size && in_array($file_ext, $allowed)) {
                // Create uploads directory with proper permissions
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                    chmod('uploads', 0777);
                }
                
                $new_filename = 'reel_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = 'uploads/' . $new_filename;
                
                // Set proper permissions for the uploads directory
                if (is_dir('uploads')) {
                    chmod('uploads', 0777);
                }
                
                if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                    $file_path = $upload_path;
                    $file_type = $file_ext;
                    // Set file permissions
                    chmod($upload_path, 0644);
                    $_SESSION['upload_debug'] = 'File saved: ' . $upload_path . ' | Type: ' . $file_type . ' | Size: ' . filesize($upload_path) . ' bytes';
                } else {
                    $upload_error = 'Failed to save uploaded file. Temp: ' . $_FILES['file']['tmp_name'] . ', Target: ' . $upload_path . ', Dir writable: ' . (is_writable('uploads') ? 'yes' : 'no');
                }
            } else {
                $upload_error = 'Invalid file type or size too large.';
            }
        }
    } else {
        $upload_error = 'No file uploaded.';
    }
    
    // Require video file for reels
    if ($file_path && in_array($file_type, ['mp4', 'mov', 'avi'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, file_path, file_type, post_type) VALUES (?, ?, ?, ?, 'reel')");
            $result = $stmt->execute([$_SESSION['user_id'], $_POST['content'], $file_path, $file_type]);
            $post_id = $pdo->lastInsertId();
            $_SESSION['reel_success'] = 'Reel created successfully!';
            $_SESSION['upload_debug'] .= ' | DB Insert: SUCCESS (Post ID: ' . $post_id . ')';
        } catch (Exception $e) {
            // Try without post_type if column doesn't exist
            try {
                $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, file_path, file_type) VALUES (?, ?, ?, ?)");
                $result = $stmt->execute([$_SESSION['user_id'], $_POST['content'], $file_path, $file_type]);
                $post_id = $pdo->lastInsertId();
                $_SESSION['reel_success'] = 'Reel created successfully!';
                $_SESSION['upload_debug'] .= ' | DB Insert: SUCCESS without post_type (Post ID: ' . $post_id . ')';
            } catch (Exception $e2) {
                $_SESSION['reel_error'] = 'Database error: ' . $e2->getMessage();
                $_SESSION['upload_debug'] .= ' | DB Insert: FAILED - ' . $e2->getMessage();
            }
        }
    } else {
        $_SESSION['reel_error'] = 'Upload failed - File: ' . ($file_path ?: 'none') . ', Type: ' . ($file_type ?: 'none') . ', Error: ' . ($upload_error ?: 'unknown');
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
    // Quick debug check
    $debug_count = $pdo->query("SELECT COUNT(*) FROM posts WHERE file_type IN ('mp4', 'mov', 'avi')")->fetchColumn();
    
    // Get all video posts for debugging
    $debug_posts = $pdo->query("SELECT id, user_id, file_path, created_at FROM posts WHERE file_type IN ('mp4', 'mov', 'avi') ORDER BY created_at DESC LIMIT 5")->fetchAll();
    
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
        WHERE u.approved = 1 AND p.file_type IN ('mp4', 'mov', 'avi')
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $reels = $stmt->fetchAll();
} catch (Exception $e) {
    $reels = [];
    $debug_count = 0;
}

$page_title = 'Reels - OSRG Connect';
$additional_css = '
    body { overflow: hidden; }
    .reel-container { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: black; overflow-y: scroll; scroll-snap-type: y mandatory; }
    .reel-item { position: relative; width: 100%; height: 100vh; display: flex; align-items: center; justify-content: center; scroll-snap-align: start; }
    .reel-video { max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; }
    .reel-overlay { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); padding: 20px; color: white; pointer-events: none; z-index: 10; }
    .reel-overlay * { pointer-events: auto; }
    .reel-info { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .reel-avatar { width: 32px; height: 32px; border-radius: 50%; border: 2px solid white; }
    .reel-avatar-emoji { font-size: 24px; }
    .reel-username { font-size: 14px; font-weight: bold; }
    .reel-caption { font-size: 14px; margin-bottom: 15px; line-height: 1.3; }
    .reel-actions { position: absolute; right: 15px; bottom: 100px; display: flex; flex-direction: column; gap: 20px; align-items: center; z-index: 10; }
    .reel-action { display: inline; }
    .reel-btn { background: none; border: none; color: white; font-size: 28px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 5px; transition: transform 0.2s; }
    .reel-btn:hover { transform: scale(1.1); }
    .reel-btn span { font-size: 12px; font-weight: bold; }
    .reel-btn.active { color: #ff3040; }
    .comment-modal { position: fixed; bottom: 0; left: 0; right: 0; background: white; border-radius: 20px 20px 0 0; max-height: 70vh; transform: translateY(100%); transition: transform 0.3s; z-index: 1000; }
    .comment-modal.active { transform: translateY(0); }
    .comment-header { padding: 15px; border-bottom: 1px solid #eee; text-align: center; font-weight: bold; }
    .comment-list { max-height: 50vh; overflow-y: auto; padding: 10px; }
    .comment-item { padding: 10px; border-bottom: 1px solid #f0f0f0; }
    .comment-form { padding: 15px; border-top: 1px solid #eee; display: flex; gap: 10px; }
    .comment-input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 20px; }
    .comment-submit { padding: 10px 20px; background: #1877f2; color: white; border: none; border-radius: 20px; }
    .create-reel { position: fixed; top: 80px; left: 20px; right: 20px; background: linear-gradient(135deg, #ff6b6b, #4ecdc4); color: white; padding: 20px; border-radius: 15px; z-index: 100; }
    .nav { position: fixed; top: 0; left: 0; right: 0; z-index: 200; }
';

require_once 'header.php';
?>

<div class="reel-container">
    <div class="create-reel">
        <h2>🎬 Create a Reel</h2>
        <?php if (isset($_SESSION['reel_success'])): ?>
            <div style="background: rgba(0,255,0,0.2); color: white; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                ✅ <?= htmlspecialchars($_SESSION['reel_success']) ?>
            </div>
            <?php unset($_SESSION['reel_success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['reel_error'])): ?>
            <div style="background: rgba(255,0,0,0.2); color: white; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                ❌ <?= htmlspecialchars($_SESSION['reel_error']) ?>
            </div>
            <?php unset($_SESSION['reel_error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['upload_debug'])) unset($_SESSION['upload_debug']); ?>
        <form method="POST" enctype="multipart/form-data" id="reelForm">
            <div style="margin: 15px 0;">
                <textarea name="content" placeholder="Add a caption to your reel..." style="width: 100%; padding: 10px; border: none; border-radius: 8px; min-height: 80px; resize: vertical;"></textarea>
            </div>
            <div style="margin: 15px 0;">
                <input type="file" name="file" accept=".mp4,.mov,.avi" required style="width: 100%; padding: 10px; background: white; border-radius: 8px; color: black;" id="videoFile">
                <small style="color: rgba(255,255,255,0.8); display: block; margin-top: 5px;">Upload Video: MP4, MOV, AVI (max 10MB)</small>
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
            
            <div class="reel-overlay">
                <div class="reel-info">
                    <?php if ($reel['avatar'] && strpos($reel['avatar'], 'avatars/') === 0): ?>
                        <img src="/<?= htmlspecialchars($reel['avatar']) ?>" alt="Avatar" class="reel-avatar">
                    <?php elseif ($reel['avatar']): ?>
                        <span class="reel-avatar-emoji"><?= htmlspecialchars($reel['avatar']) ?></span>
                    <?php else: ?>
                        <span class="reel-avatar-emoji">👤</span>
                    <?php endif; ?>
                    <strong class="reel-username"><?= htmlspecialchars($reel['username']) ?></strong>
                </div>
                
                <?php if ($reel['content']): ?>
                    <div class="reel-caption"><?= nl2br(htmlspecialchars($reel['content'])) ?></div>
                <?php endif; ?>
                
                <div class="reel-actions">
                    <form method="POST" class="reel-action">
                        <input type="hidden" name="post_id" value="<?= $reel['id'] ?>">
                        <button type="submit" name="reaction" value="<?= $reel['user_reaction'] === 'like' ? 'remove' : 'like' ?>" class="reel-btn <?= $reel['user_reaction'] === 'like' ? 'active' : '' ?>">
                            👍<span><?= $reel['like_count'] ?: '' ?></span>
                        </button>
                    </form>
                    <form method="POST" class="reel-action">
                        <input type="hidden" name="post_id" value="<?= $reel['id'] ?>">
                        <button type="submit" name="reaction" value="<?= $reel['user_reaction'] === 'love' ? 'remove' : 'love' ?>" class="reel-btn <?= $reel['user_reaction'] === 'love' ? 'active' : '' ?>">
                            ❤️<span><?= $reel['love_count'] ?: '' ?></span>
                        </button>
                    </form>
                    <form method="POST" class="reel-action">
                        <input type="hidden" name="post_id" value="<?= $reel['id'] ?>">
                        <button type="submit" name="reaction" value="<?= $reel['user_reaction'] === 'laugh' ? 'remove' : 'laugh' ?>" class="reel-btn <?= $reel['user_reaction'] === 'laugh' ? 'active' : '' ?>">
                            😂<span><?= $reel['laugh_count'] ?: '' ?></span>
                        </button>
                    </form>
                    <button class="reel-btn" onclick="openComments(<?= $reel['id'] ?>)">💬<span><?= $reel['comment_count'] ?: '' ?></span></button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="reel-item" style="display: flex; align-items: center; justify-content: center; color: white; text-align: center;">
            <div>
                <h3>No reels yet! 🎬</h3>
                <p>Be the first to create a reel and share it with everyone!</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Comment Modal -->
<div id="commentModal" class="comment-modal">
    <div class="comment-header">
        Comments
        <button onclick="closeComments()" style="position: absolute; right: 15px; top: 15px; background: none; border: none; font-size: 20px;">×</button>
    </div>
    <div id="commentList" class="comment-list"></div>
    <form class="comment-form" onsubmit="submitComment(event)">
        <input type="hidden" id="commentPostId" value="">
        <input type="text" id="commentInput" class="comment-input" placeholder="Write a comment..." required>
        <button type="submit" class="comment-submit">Post</button>
    </form>
</div>

<script>
document.getElementById('reelForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('videoFile');
    const submitBtn = document.getElementById('submitBtn');
    const status = document.getElementById('uploadStatus');
    
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        if (file.size > maxSize) {
            e.preventDefault();
            status.style.display = 'block';
            status.style.background = 'rgba(255,0,0,0.2)';
            status.style.color = 'white';
            status.innerHTML = '❌ File too large! Maximum size is 10MB.';
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

// Comment functions
function openComments(postId) {
    document.getElementById('commentPostId').value = postId;
    document.getElementById('commentModal').classList.add('active');
    loadComments(postId);
}

function closeComments() {
    document.getElementById('commentModal').classList.remove('active');
}

function loadComments(postId) {
    fetch('get_comments.php?post_id=' + postId)
        .then(response => response.json())
        .then(comments => {
            const list = document.getElementById('commentList');
            list.innerHTML = comments.map(c => 
                `<div class="comment-item"><strong>${c.username}:</strong> ${c.content}<br><small>${c.created_at}</small></div>`
            ).join('');
        });
}

function submitComment(event) {
    event.preventDefault();
    const postId = document.getElementById('commentPostId').value;
    const content = document.getElementById('commentInput').value;
    
    fetch('add_comment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `post_id=${postId}&comment=${encodeURIComponent(content)}`
    }).then(() => {
        document.getElementById('commentInput').value = '';
        loadComments(postId);
        location.reload(); // Refresh to update comment count
    });
}

// Auto-play videos when in view (with user interaction handling)
const videos = document.querySelectorAll('.reel-video');
let userInteracted = false;

// Track user interaction
document.addEventListener('click', () => { userInteracted = true; }, { once: true });
document.addEventListener('touchstart', () => { userInteracted = true; }, { once: true });

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const video = entry.target;
            if (userInteracted) {
                video.play().catch(() => {});
            } else {
                // Show play button overlay if autoplay fails
                video.muted = true;
                video.play().catch(() => {});
            }
        } else {
            entry.target.pause();
        }
    });
}, { threshold: 0.5 });

videos.forEach(video => {
    video.muted = true; // Start muted for autoplay
    observer.observe(video);
});
</script>

</body>
</html>