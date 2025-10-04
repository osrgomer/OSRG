<?php
require_once 'config.php';
init_db();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$pdo = get_db();
$page_title = 'My Friends - OSRG Connect';
require_once 'header.php';
?>

<div class="container">
    <div class="header" style="background: #1877f2; color: white; padding: 15px; text-align: center; margin-bottom: 20px;">
        <h1>Friends Feed</h1>
    </div>
    
    <div class="post" style="background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <p>Friends page is working! More features coming soon...</p>
    </div>
</div>

</body>
</html>