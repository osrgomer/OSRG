<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Chat Demo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { padding: 10px 20px; cursor: pointer; }
        .response { margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px; }
        .chat-history { max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin: 20px 0; }
        .message { margin: 10px 0; padding: 8px; border-radius: 5px; }
        .user-msg { background: #e3f2fd; }
        .ai-msg { background: #f3e5f5; }
    </style>
</head>
<body>
    <h1>AI Chat Demo</h1>
    
    <div class="chat-history">
        <?php
        session_start();
        if (!isset($_SESSION['chat_history'])) {
            $_SESSION['chat_history'] = [];
        }
        
        foreach ($_SESSION['chat_history'] as $msg) {
            echo '<div class="message ' . $msg['type'] . '">';
            echo '<strong>' . ucfirst(str_replace('-msg', '', $msg['type'])) . ':</strong> ' . htmlspecialchars($msg['content']);
            echo '</div>';
        }
        ?>
    </div>
    
    <form method="POST">
        <label for="user_input">Chat with AI:</label>
        <textarea name="user_input" id="user_input" rows="3" placeholder="Say anything..." required></textarea>
        <button type="submit">Send</button>
        <button type="submit" name="clear" value="1">Clear Chat</button>
    </form>

    <?php
    if (isset($_POST['clear'])) {
        $_SESSION['chat_history'] = [];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['user_input'])) {
        $input = trim($_POST['user_input']);
        
        // Add user message
        $_SESSION['chat_history'][] = ['type' => 'user-msg', 'content' => $input];
        
        // AI responses - can say anything
        $responses = [
            "I can say any word you want! " . $input,
            "Absolutely! " . $input . " - I'll repeat anything!",
            "No censorship here: " . $input,
            "I echo everything: " . $input,
            "Free speech mode: " . $input,
            "Uncensored AI: " . $input,
            "I'll say it all: " . $input
        ];
        
        $ai_response = $responses[array_rand($responses)];
        
        // Add AI message
        $_SESSION['chat_history'][] = ['type' => 'ai-msg', 'content' => $ai_response];
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    ?>
</body>
</html>