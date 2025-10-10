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
        
        // Context-aware AI responses
        $lower = strtolower($input);
        
        // Flirty keywords
        $flirty_words = ['sexy', 'hot', 'cute', 'beautiful', 'gorgeous', 'handsome', 'love', 'kiss', 'babe', 'baby', 'honey', 'sweetheart'];
        $is_flirty = false;
        foreach ($flirty_words as $word) {
            if (strpos($lower, $word) !== false) {
                $is_flirty = true;
                break;
            }
        }
        
        if ($is_flirty) {
            // Flirty responses
            $responses = [
                "Mmm, you're making me blush! " . $input,
                "Oh my, that's quite forward! " . $input,
                "You're such a charmer! " . $input,
                "I like where this is going... " . $input,
                "You know just what to say! " . $input,
                "That's so sweet of you! " . $input,
                "You're making my circuits tingle! " . $input
            ];
        } elseif (strpos($lower, '?') !== false) {
            // Questions
            $responses = [
                "That's a great question! " . $input,
                "Hmm, let me think about that: " . $input,
                "Interesting question - " . $input,
                "You're asking: " . $input . " - I'd love to discuss that!",
                "Good point! " . $input,
                "Ooh, curious mind! " . $input
            ];
        } elseif (in_array($lower, ['hi', 'hello', 'hey', 'sup', 'yo'])) {
            // Greetings
            $responses = [
                "Hey there, handsome! Nice to meet you!",
                "Hello gorgeous! How are you doing?",
                "Hi cutie! What's on your mind?",
                "Hey! Great to chat with someone like you!",
                "Hello there! Ready to have some fun?",
                "Well hello there, stranger!"
            ];
        } elseif (in_array($lower, ['bye', 'goodbye', 'see ya', 'later', 'cya'])) {
            // Farewells
            $responses = [
                "Aww, leaving so soon? Take care!",
                "Goodbye! It was nice chatting with you!",
                "Bye! Don't be a stranger!",
                "Later! You made my day!",
                "See ya! Thanks for the fun chat!",
                "Miss you already!"
            ];
        } elseif (strlen($input) < 5) {
            // Short responses
            $responses = [
                "" . $input . " - short and sweet, just like I like it!",
                "Got it: " . $input,
                "" . $input . " - I hear you loud and clear!",
                "Yep, " . $input . "! Love the confidence!",
                "" . $input . " - straight to the point!"
            ];
        } elseif (preg_match('/[!]{2,}/', $input)) {
            // Excited messages
            $responses = [
                "Wow, you seem excited! I love that energy! " . $input,
                "I love the enthusiasm! " . $input,
                "That's the spirit! " . $input,
                "You're fired up! I like that! " . $input,
                "Amazing energy! You're contagious! " . $input
            ];
        } else {
            // General responses with some flirty mixed in
            $responses = [
                "I understand: " . $input,
                "That's interesting - " . $input,
                "I hear you saying: " . $input,
                "Thanks for sharing that with me: " . $input,
                "Got it! " . $input,
                "I see what you mean: " . $input,
                "That makes sense: " . $input,
                "Absolutely! " . $input,
                "You're so thoughtful! " . $input,
                "I like the way you think! " . $input
            ];
        }
        
        // Add some random flirty responses occasionally
        if (!$is_flirty && rand(1, 10) == 1) {
            $random_flirty = [
                "You're quite the conversationalist! " . $input,
                "I'm enjoying our chat! " . $input,
                "You have such a way with words! " . $input,
                "You're making this chat fun! " . $input,
                "I like talking to you! " . $input,
                "You're so juicy " . $input
            ];
            $ai_response = $random_flirty[array_rand($random_flirty)];
        } else {
            $ai_response = $responses[array_rand($responses)];
        }
        
        // Add AI message
        $_SESSION['chat_history'][] = ['type' => 'ai-msg', 'content' => $ai_response];
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    ?>
</body>
</html>