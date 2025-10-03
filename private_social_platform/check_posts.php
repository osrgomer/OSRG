<?php
try {
    require_once 'config.php';
    init_db();
    
    $pdo = get_db();
    
    echo "<h2>Recent Posts Check</h2>";
    
    // Check all posts
    try {
        $stmt = $pdo->query("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 10");
        $posts = $stmt->fetchAll();
        
        echo "<h3>Last 10 Posts:</h3>";
        if ($posts) {
            foreach ($posts as $post) {
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
                echo "<strong>ID:</strong> " . $post['id'] . "<br>";
                echo "<strong>User:</strong> " . $post['username'] . "<br>";
                echo "<strong>Type:</strong> " . ($post['post_type'] ?? 'post') . "<br>";
                echo "<strong>Content:</strong> " . htmlspecialchars($post['content']) . "<br>";
                echo "<strong>File:</strong> " . ($post['file_path'] ?? 'none') . "<br>";
                echo "<strong>Created:</strong> " . $post['created_at'] . "<br>";
                echo "</div>";
            }
        } else {
            echo "<p>No posts found.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error fetching posts: " . $e->getMessage() . "</p>";
    }

    
    // Check for any reels specifically
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM posts WHERE post_type = 'reel'");
        $reel_count = $stmt->fetch()['count'];
        echo "<h3>Total Reels: " . $reel_count . "</h3>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error counting reels: " . $e->getMessage() . "</p>";
    }
    
    // Check if post_type column exists
    try {
        $stmt = $pdo->query("PRAGMA table_info(posts)");
        $columns = $stmt->fetchAll();
        echo "<h3>Posts Table Structure:</h3>";
        foreach ($columns as $col) {
            echo $col['name'] . " (" . $col['type'] . ")<br>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error checking table structure: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Fatal error: " . $e->getMessage() . "</p>";
}
?>