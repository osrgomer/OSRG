<?php
require_once 'config.php';
init_db();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get posts
$pdo = get_db();
$stmt = $pdo->query("SELECT p.content, p.created_at, u.username 
                     FROM posts p JOIN users u ON p.user_id = u.id 
                     ORDER BY p.created_at DESC");
$posts = $stmt->fetchAll();

// Handle new post
if ($_POST['content'] ?? false) {
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['content']]);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Feed - Private Social</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1877f2; color: white; padding: 15px; text-align: center; }
        .nav { background: white; padding: 10px; margin-bottom: 20px; border-radius: 8px; }
        .nav a { color: #1877f2; text-decoration: none; margin-right: 15px; }
        .post { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin: 15px 0; }
        textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #1877f2; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="nav">
        <a href="index.php">Home</a>
        <a href="users.php">Find Friends</a>
        <a href="logout.php">Logout</a>
    </div>
    
    <div class="container">
        <div class="header">
            <h1>Your Private Feed</h1>
        </div>

        <div class="post">
            <h3>Share something...</h3>
            <form method="POST">
                <div class="form-group">
                    <textarea name="content" placeholder="What's on your mind?" rows="3" required></textarea>
                </div>
                <button type="submit">Post</button>
            </form>
        </div>

        <?php if ($posts): ?>
            <?php foreach ($posts as $post): ?>
            <div class="post">
                <p><strong><?= htmlspecialchars($post['username']) ?></strong></p>
                <p><?= htmlspecialchars($post['content']) ?></p>
                <small><?= $post['created_at'] ?></small>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="post">
                <p><strong>Welcome!</strong></p>
                <p>Start by creating your first post above!</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>