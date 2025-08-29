<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = get_db();

// Get friend to message
$friend_id = $_GET['friend'] ?? null;
$friend_name = '';

if ($friend_id) {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$friend_id]);
    $friend = $stmt->fetch();
    $friend_name = $friend ? $friend['username'] : '';
}

// Send message
if ($_POST['content'] ?? false && $friend_id) {
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $friend_id, $_POST['content']]);
    
    // Check if receiver wants email notifications
    $stmt = $pdo->prepare("SELECT u.email, u.username, u.email_notifications, s.username as sender_name FROM users u, users s WHERE u.id = ? AND s.id = ?");
    $stmt->execute([$friend_id, $_SESSION['user_id']]);
    $notification_data = $stmt->fetch();
    
    if ($notification_data && $notification_data['email_notifications']) {
        $subject = "New Message - OSRG Connect";
        $body = "Hi " . $notification_data['username'] . ",\n\n";
        $body .= "You have received a new message from " . $notification_data['sender_name'] . " on OSRG Connect.\n\n";
        $body .= "Message: " . $_POST['content'] . "\n\n";
        $body .= "Login to reply: https://osrg.lol/osrg/private_social_platform/messages.php\n\n";
        $body .= "Best regards,\nOSRG Connect Team";
        
        $headers = "From: OSRG Connect <omer@osrg.lol>\r\n";
        $headers .= "Reply-To: omer@osrg.lol\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        ini_set('SMTP', 'smtp.hostinger.com');
        ini_set('smtp_port', '465');
        ini_set('sendmail_from', 'omer@osrg.lol');
        
        mail($notification_data['email'], $subject, $body, $headers);
    }
    
    header("Location: messages.php?friend=$friend_id");
    exit;
}

// Get messages between users
$messages = [];
if ($friend_id) {
    $stmt = $pdo->prepare("
        SELECT m.content, m.created_at, u.username, m.sender_id
        FROM messages m 
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$_SESSION['user_id'], $friend_id, $friend_id, $_SESSION['user_id']]);
    $messages = $stmt->fetchAll();
}

// Get friends list
$stmt = $pdo->prepare("
    SELECT u.id, u.username
    FROM users u
    JOIN friends f ON (
        (f.user_id = ? AND f.friend_id = u.id AND f.status = 'accepted') OR
        (f.friend_id = ? AND f.user_id = u.id AND f.status = 'accepted')
    )
    GROUP BY u.id, u.username
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$friends = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <title>Messages - OSRG Connect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; display: flex; gap: 20px; }
        .header { background: #1877f2; color: white; padding: 15px; text-align: center; margin-bottom: 20px; }
        .nav { background: white; padding: 10px; margin-bottom: 20px; border-radius: 8px; }
        .nav a { color: #1877f2; text-decoration: none; margin-right: 15px; }
        .friends-list { width: 250px; background: white; padding: 15px; border-radius: 8px; height: fit-content; }
        .chat-area { flex: 1; background: white; border-radius: 8px; display: flex; flex-direction: column; }
        .messages { flex: 1; padding: 15px; max-height: 400px; overflow-y: auto; }
        .message { margin: 10px 0; padding: 10px; border-radius: 8px; }
        .message.sent { background: #1877f2; color: white; margin-left: 50px; }
        .message.received { background: #e3f2fd; margin-right: 50px; }
        .message-form { padding: 15px; border-top: 1px solid #ddd; }
        .friend-item { padding: 10px; border-bottom: 1px solid #eee; cursor: pointer; }
        .friend-item:hover { background: #f5f5f5; }
        .friend-item.active { background: #e3f2fd; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #1877f2; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
    <script>
        let lastMessageCount = 0;
        let currentFriendId = <?= $friend_id ? $friend_id : 'null' ?>;
        
        function checkNewMessages() {
            if (currentFriendId) {
                fetch('check_messages.php?friend=' + currentFriendId)
                    .then(response => response.json())
                    .then(data => {
                        if (lastMessageCount > 0 && data.count > lastMessageCount) {
                            if ('Notification' in window && Notification.permission === 'granted') {
                                new Notification('New Message!', {
                                    body: data.latest_message,
                                    icon: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%231877f2"><path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4l4 4 4-4h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>'
                                });
                            }
                            // Reload page to show new messages
                            location.reload();
                        }
                        lastMessageCount = data.count;
                    });
            }
        }
        
        // Auto-refresh every 3 seconds
        setInterval(function() {
            if (currentFriendId) {
                checkNewMessages();
            }
        }, 3000);
        
        // Request notification permission on page load
        function handleEnter(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                event.target.form.submit();
            }
        }
        
        function sendMessage(event) {
            return true; // Allow form submission
        }
        
        window.onload = function() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
            if (currentFriendId) {
                // Get initial message count
                fetch('check_messages.php?friend=' + currentFriendId)
                    .then(response => response.json())
                    .then(data => {
                        lastMessageCount = data.count;
                    });
            }
        }
    </script>
</head>
<body>
    <div class="nav">
        <a href="index.php">Home</a>
        <a href="users.php">Find Friends</a>
        <a href="friends.php">My Friends</a>
        <a href="messages.php">Messages</a>
        <a href="settings.php">Settings</a>
        <?php
        $pdo_nav = get_db();
        $stmt_nav = $pdo_nav->prepare("SELECT username FROM users WHERE id = ?");
        $stmt_nav->execute([$_SESSION['user_id']]);
        $user_nav = $stmt_nav->fetch();
        if ($user_nav && $user_nav['username'] === 'OSRG'):
        ?>
        <a href="admin.php" style="color: #d32f2f; font-weight: bold;">Admin Panel</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    </div>
    
    <div class="header">
        <h1>Private Messages</h1>
    </div>

    <div class="container">
        <div class="friends-list">
            <h3>Friends</h3>
            <?php foreach ($friends as $friend): ?>
            <div class="friend-item <?= $friend['id'] == $friend_id ? 'active' : '' ?>" 
                 onclick="location.href='messages.php?friend=<?= $friend['id'] ?>'">
                <?= htmlspecialchars($friend['username']) ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="chat-area">
            <?php if ($friend_id): ?>
                <div style="padding: 15px; border-bottom: 1px solid #ddd; background: #f8f9fa;">
                    <h3>Chat with <?= htmlspecialchars($friend_name) ?></h3>
                </div>
                
                <div class="messages">
                    <?php foreach ($messages as $message): ?>
                    <div class="message <?= $message['sender_id'] == $_SESSION['user_id'] ? 'sent' : 'received' ?>">
                        <div><?= htmlspecialchars($message['content']) ?></div>
                        <small><?= date('M j, H:i', strtotime($message['created_at'])) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>

                <form method="POST" class="message-form" onsubmit="return sendMessage(event)">
                    <div style="display: flex; gap: 10px;">
                        <textarea name="content" placeholder="Type your message..." rows="2" required onkeydown="handleEnter(event)"></textarea>
                        <button type="submit">Send</button>
                    </div>
                </form>
            <?php else: ?>
                <div style="padding: 50px; text-align: center; color: #666;">
                    Select a friend to start messaging
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>