<?php
session_start();
if (!isset($_SESSION['hotgram_user'])) {
    header('Location: https://osrg.lol/osrg/webtest/signup.php');
    exit;
}

$usersFile = __DIR__ . '/users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

// Simulated user data (no SQL, so we use a session array)
if (!isset($_SESSION['posts'])) {
    $_SESSION['posts'] = [];
    $_SESSION['user'] = ['username' => 'hotuser', 'id' => 1]; // Simulated logged-in user
}

// --- Detect if we should show reels after posting ---
$showReelsAfterPost = false;

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = filter_input(INPUT_POST, 'caption', FILTER_SANITIZE_STRING);
    $image = isset($_FILES['image']) ? $_FILES['image'] : null;
    $video = isset($_FILES['video']) ? $_FILES['video'] : null;
    $image_path = '';
    $video_path = '';
    $uploads_dir = 'uploads/';
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);
    }
    if ($image && $image['tmp_name']) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($image['type'], $allowed_types) && $image['size'] < 5000000) {
            $image_name = uniqid() . '_' . $image['name'];
            $target_path = $uploads_dir . $image_name;
            if (move_uploaded_file($image['tmp_name'], $target_path)) {
                $image_path = $target_path;
            }
        }
    }
    if ($video && $video['tmp_name']) {
        $allowed_types = ['video/mp4', 'video/webm'];
        if (in_array($video['type'], $allowed_types) && $video['size'] < 20000000) {
            $video_name = uniqid() . '_' . $video['name'];
            $target_path = $uploads_dir . $video_name;
            if (move_uploaded_file($video['tmp_name'], $target_path)) {
                $video_path = $target_path;
            }
        }
    }
    $is_reel = (!empty($video_path) && empty($image_path)) ? true : false;
    $_SESSION['posts'][] = [
        'id' => uniqid(),
        'username' => $_SESSION['hotgram_user'],
        'caption' => $caption,
        'image' => $image_path,
        'video' => $video_path,
        'timestamp' => time(),
        'expire' => time() + 3600,
        'is_reel' => $is_reel
    ];
    // Set flag to show reels if this was a reel post
    if ($is_reel) {
        $showReelsAfterPost = true;
    }
    // --- Add a JS redirect to the correct section after post ---
    echo '<script>window.onload = function() {';
    echo $is_reel ? "document.getElementById('goToReelsBtn').click();" : "document.getElementById('goToPostBtn').click();";
    echo '};</script>';
}

// Delete expired posts
$_SESSION['posts'] = array_filter($_SESSION['posts'], function($post) {
    return $post['timestamp'] >= time() - 3600;
});

// Handle post deletion
if (isset($_GET['delete'])) {
    $post_id = filter_input(INPUT_GET, 'delete', FILTER_SANITIZE_STRING);
    foreach ($_SESSION['posts'] as $key => $post) {
        if ($post['id'] === $post_id) {
            unlink($post['image']); // Delete image file
            unlink($post['video']); // Delete video file if exists
            unset($_SESSION['posts'][$key]);
            break;
        }
    }
    header('Location: hotgram.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Sexy Social App 😘</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #000;
        }
        body { background: linear-gradient(to bottom, #ff9a9e, #fad0c4); font-family: 'Arial', sans-serif; }
        .post { transition: all 0.3s ease; }
        .post:hover { transform: scale(1.02); }
        #postForm { display: none; }
        .post img { max-height: 300px; object-fit: cover; }
        .post video { max-height: 300px; object-fit: cover; }
        .reels-container {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: #000;
            z-index: 1;
        }
        .reel {
            position: absolute;
            width: 100vw;
            height: 100vh;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            transition: transform 0.5s;
            background: #000;
        }
        .reel video {
            position: absolute;
            top: 0; left: 0;
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            z-index: 1;
        }
        .reel .overlay {
            position: relative;
            z-index: 2;
            width: 100vw;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 0 32px 32px 32px;
        }
        .reel .left-info {
            color: #fff;
            max-width: 60vw;
        }
        .reel .username {
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 0.2em;
        }
        .reel .caption {
            font-size: 1em;
            margin-bottom: 0.2em;
            white-space: pre-line;
        }
        .reel .music {
            display: flex;
            align-items: center;
            font-size: 0.95em;
            opacity: 0.8;
        }
        .reel .music i {
            margin-right: 0.4em;
        }
        .reel .actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
        }
        .reel .action-btn {
            color: #fff;
            background: rgba(0,0,0,0.3);
            border: none;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6em;
            cursor: pointer;
            margin-bottom: 2px;
        }
        .reel .action-label {
            color: #fff;
            font-size: 0.9em;
            text-align: center;
        }
        .reel .heart-anim {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            font-size: 5em;
            color: #fff;
            opacity: 0.8;
            pointer-events: none;
            z-index: 10;
            animation: pop 0.5s;
        }
        @keyframes pop {
            0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0.2; }
            60% { transform: translate(-50%, -50%) scale(1.2); opacity: 1; }
            100% { transform: translate(-50%, -50%) scale(1); opacity: 0.8; }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4">
    <h1 class="text-4xl font-bold text-white mb-6">Our Hot Social App 🔥</h1>
    <p class="text-lg text-white mb-4">Hey cutie, @<?php echo $_SESSION['user']['username']; ?>! Let's share some spicy moments! 😏</p>

    <div class="absolute top-4 left-4 z-20">
        <a href="#" id="goToReelsBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Go to Reels</a>
        <a href="#" id="goToPostBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 ml-2">Add Post</a>
    </div>

    <div class="reels-container" id="reelsContainer" style="display:none;"></div>
    <div id="postFormContainer" style="display:block;">
        <!-- Post Form -->
        <button id="toggleForm" class="bg-pink-500 text-white px-6 py-2 rounded-full mb-4 hover:bg-pink-600 transition">Make a Post, Babe! 💋</button>
        <form id="postForm" action="" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="caption">What's on your mind, sexy? 😘</label>
                <textarea name="caption" id="caption" class="w-full p-2 border rounded" placeholder="Tell me something naughty..." required></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="image">Upload a hot pic 📸 (Optional)</label>
                <input type="file" name="image" id="image" accept="image/*" class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="video">Upload a reel (video, optional) 🎬</label>
                <input type="file" name="video" id="video" accept="video/mp4,video/webm" class="w-full p-2 border rounded">
            </div>
            <button type="submit" class="bg-pink-500 text-white px-4 py-2 rounded hover:bg-pink-600 transition">Post It, Baby! 💦</button>
        </form>
    </div>

    <!-- Feed -->
    <div id="feed" class="w-full max-w-2xl space-y-4">
        <?php foreach (array_reverse($_SESSION['posts']) as $post): ?>
            <div class="post bg-white p-4 rounded-lg shadow-lg relative">
                <p class="text-gray-700 font-bold">@<?php echo htmlspecialchars($post['username']); ?></p>
                <p class="text-gray-600"><?php echo htmlspecialchars($post['caption']); ?></p>
                <?php if (!empty($post['image'])): ?>
                    <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="w-full rounded mt-2">
                <?php endif; ?>
                <?php if (!empty($post['video'])): ?>
                    <video controls class="w-full rounded mt-2" style="max-height:300px;">
                        <source src="<?php echo htmlspecialchars($post['video']); ?>" type="video/mp4">
                        <source src="<?php echo htmlspecialchars($post['video']); ?>" type="video/webm">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
                <p class="text-sm text-gray-500 mt-2">Posted <?php echo date('h:i A', $post['timestamp']); ?> (Expires in <?php echo ($post['expire'] - time()) / 60; ?> mins)</p>
                <a href="?delete=<?php echo $post['id']; ?>" class="text-red-500 text-sm hover:underline">Delete</a>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="logout.php" class="absolute top-4 right-4 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Exit</a>

    <script>
        // Toggle post form
        document.getElementById('toggleForm').addEventListener('click', () => {
            const form = document.getElementById('postForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });

        // Auto-refresh feed to simulate expiring posts
        setInterval(() => {
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    document.getElementById('feed').innerHTML = doc.getElementById('feed').innerHTML;
                });
        }, 60000); // Refresh every minute

        // Cute alert when posting
        document.getElementById('postForm').addEventListener('submit', () => {
            alert('Mmm, that’s a hot post, baby! Can’t wait to see it in our feed! 😘');
        });

        // --- Reels Data ---
        const reels = <?php echo json_encode(array_values(array_filter($_SESSION['posts'], function($post){ return !empty($post['video']) && empty($post['image']); }))); ?>;
        let current = 0;
        let heartTimeout;

        function renderReel(idx) {
            const reel = reels[idx];
            if (!reel) return '';
            return `
            <div class="reel" data-idx="${idx}">
                <video src="${reel.video}" autoplay loop playsinline muted id="reelVideo" style="background:#000;"></video>
                <div class="overlay">
                    <div class="left-info">
                        <div class="username">@${reel.username}</div>
                        <div class="caption">${reel.caption || ''}</div>
                        <div class="music"><i class="fa fa-music"></i> <span>HotGram Audio</span></div>
                    </div>
                    <div class="actions">
                        <button class="action-btn" id="likeBtn"><i class="fa fa-heart"></i></button>
                        <div class="action-label">Like</div>
                        <button class="action-btn"><i class="fa fa-comment"></i></button>
                        <div class="action-label">Comment</div>
                        <button class="action-btn"><i class="fa fa-share"></i></button>
                        <div class="action-label">Share</div>
                        <button class="action-btn"><i class="fa fa-bookmark"></i></button>
                        <div class="action-label">Save</div>
                        <button class="action-btn"><i class="fa fa-ellipsis-v"></i></button>
                        <div class="action-label">More</div>
                    </div>
                </div>
                <div id="heartAnim" style="display:none;"><i class="fa fa-heart heart-anim"></i></div>
            </div>`;
        }

        function showReel(idx) {
            const container = document.getElementById('reelsContainer');
            container.innerHTML = renderReel(idx);
            const video = document.getElementById('reelVideo');
            video.muted = false;
            video.play();
            // Play/pause on tap
            video.onclick = () => video.paused ? video.play() : video.pause();
            // Double tap to like
            let lastTap = 0;
            video.addEventListener('click', function(e) {
                const now = Date.now();
                if (now - lastTap < 300) {
                    document.getElementById('heartAnim').style.display = 'block';
                    clearTimeout(heartTimeout);
                    heartTimeout = setTimeout(()=>{
                        document.getElementById('heartAnim').style.display = 'none';
                    }, 600);
                }
                lastTap = now;
            });
            // Like button
            document.getElementById('likeBtn').onclick = () => {
                document.getElementById('heartAnim').style.display = 'block';
                clearTimeout(heartTimeout);
                heartTimeout = setTimeout(()=>{
                    document.getElementById('heartAnim').style.display = 'none';
                }, 600);
            };
        }

        // Vertical swipe navigation
        let startY = null;
        document.addEventListener('touchstart', e => {
            startY = e.touches[0].clientY;
        });
        document.addEventListener('touchend', e => {
            if (startY === null) return;
            const endY = e.changedTouches[0].clientY;
            if (endY - startY > 50) { // swipe down
                current = (current - 1 + reels.length) % reels.length;
                showReel(current);
            } else if (startY - endY > 50) { // swipe up
                current = (current + 1) % reels.length;
                showReel(current);
            }
            startY = null;
        });
        // Mouse wheel for desktop
        document.addEventListener('wheel', e => {
            if (e.deltaY > 0) {
                current = (current + 1) % reels.length;
                showReel(current);
            } else if (e.deltaY < 0) {
                current = (current - 1 + reels.length) % reels.length;
                showReel(current);
            }
        });
        // Initial render
        if (reels.length) showReel(current);

        // Add toggle logic for buttons
        document.getElementById('goToReelsBtn').onclick = function() {
            document.getElementById('reelsContainer').style.display = 'block';
            document.getElementById('postFormContainer').style.display = 'none';
        };
        document.getElementById('goToPostBtn').onclick = function() {
            document.getElementById('reelsContainer').style.display = 'none';
            document.getElementById('postFormContainer').style.display = 'block';
            document.getElementById('postForm').style.display = 'block'; // Always show the form
        };
        // Also, always show the form on page load if postFormContainer is visible
        if (document.getElementById('postFormContainer').style.display !== 'none') {
            document.getElementById('postForm').style.display = 'block';
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</body>
</html>