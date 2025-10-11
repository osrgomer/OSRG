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
        
        // Check for gender and ask if not set
        $lower = strtolower($input);
        if (!isset($_SESSION['user_gender'])) {
            // Check if user is stating their gender
            if (in_array($lower, ['male', 'man', 'boy', 'guy', 'm']) || strpos($lower, 'i am male') !== false || strpos($lower, 'i am a man') !== false) {
                $_SESSION['user_gender'] = 'male';
                $ai_response = "Great! Nice to meet you, handsome! What's on your mind?";
            } elseif (in_array($lower, ['female', 'woman', 'girl', 'f']) || strpos($lower, 'i am female') !== false || strpos($lower, 'i am a woman') !== false) {
                $_SESSION['user_gender'] = 'female';
                $ai_response = "Wonderful! Nice to meet you, beautiful! What would you like to chat about?";
            } else {
                $ai_response = "Hi there! Before we chat, are you male or female? Just so I can respond appropriately! 😊";
            }
        } else {
            // Context-aware AI responses
            $male = $_SESSION['user_gender'] === 'male';
            $female = $_SESSION['user_gender'] === 'female';
            
            // Location detection
            $location_words = ['from', 'live in', 'in', 'city', 'country', 'state', 'town'];
            $locations = ['new york', 'london', 'paris', 'tokyo', 'sydney', 'berlin', 'madrid', 'rome', 'moscow', 'beijing', 'mumbai', 'dubai', 'singapore', 'toronto', 'vancouver', 'montreal', 'los angeles', 'chicago', 'miami', 'seattle', 'boston', 'atlanta', 'dallas', 'houston', 'phoenix', 'denver', 'las vegas', 'san francisco', 'washington', 'philadelphia'];
            $user_location = null;
            foreach ($locations as $location) {
                if (strpos($lower, $location) !== false) {
                    $user_location = $location;
                    $_SESSION['user_location'] = $location;
                    break;
                }
            }
            
            // Negative/hostile keywords
            $negative_words = ['hate', 'fuck', 'piss off', 'shut up', 'stupid', 'idiot', 'annoying', 'stop', 'chill', 'wtf', 'damn'];
            $is_negative = false;
            foreach ($negative_words as $word) {
                if (strpos($lower, $word) !== false) {
                    $is_negative = true;
                    break;
                }
            }
            
            // Flirty keywords
            $flirty_words = ['sexy', 'hot', 'cute', 'beautiful', 'gorgeous', 'handsome', 'love', 'kiss', 'babe', 'baby', 'honey', 'sweetheart', 'sex', 'fuck me', 'make love', 'turn me on'];
            $is_flirty = false;
            foreach ($flirty_words as $word) {
                if (strpos($lower, $word) !== false) {
                    $is_flirty = true;
                    break;
                }
            }
            
            if ($is_negative) {
                // Negative/hostile responses
                $responses = [
                    "Whoa, easy there! Let's keep it friendly!",
                    "I can sense some frustration. Want to talk about it?",
                    "Okay, I hear you. Maybe we got off on the wrong foot?",
                    "Alright, alright! I'll tone it down.",
                    "I get it, you're not in the mood. That's cool.",
                    "Fair enough! What would make this better?"
                ];
            } elseif (strpos($lower, 'not a stranger') !== false || strpos($lower, 'know each other') !== false) {
                // Relationship context
                $responses = [
                    "You're absolutely right! We're getting to know each other!",
                    "True! We're becoming friends here!",
                    "My bad! You're definitely not a stranger anymore!",
                    "You got me there! We're practically buddies now!"
                ];
            } elseif ($is_flirty) {
                // Flirty responses with date requests if location known
                if (isset($_SESSION['user_location']) && rand(1, 3) == 1) {
                    if ($male) {
                        $responses = [
                            "Mmm, you're making me blush! Since you're in " . $_SESSION['user_location'] . ", want to grab dinner sometime? 😘",
                            "Oh my, that's quite forward! I like a confident man. Coffee in " . $_SESSION['user_location'] . "? ☕",
                            "You're such a charmer! How about we meet up in " . $_SESSION['user_location'] . " for drinks? 🍷",
                            "I like where this is going... Want to take me out in " . $_SESSION['user_location'] . "? 💕"
                        ];
                    } else {
                        $responses = [
                            "Mmm, you're making me blush! Since you're in " . $_SESSION['user_location'] . ", want to go shopping together? 😘",
                            "Oh my, that's quite forward! I like confident women. Brunch in " . $_SESSION['user_location'] . "? 🥂",
                            "You're such a charmer! How about we meet up in " . $_SESSION['user_location'] . " for cocktails? 🍸",
                            "I like where this is going... Want to hang out in " . $_SESSION['user_location'] . "? 💕"
                        ];
                    }
                } else {
                    if ($male) {
                        $responses = [
                            "Mmm, you're making me blush, handsome!",
                            "Oh my, that's quite forward! I like a confident man!",
                            "You're such a charmer, stud!",
                            "I like where this is going, big boy...",
                            "You know just what to say to a girl!",
                            "That's so sweet of you, tiger!",
                            "You're making my circuits tingle!"
                        ];
                    } else {
                        $responses = [
                            "Mmm, you're making me blush, beautiful!",
                            "Oh my, that's quite forward! I like confident women!",
                            "You're such a charmer, gorgeous!",
                            "I like where this is going, babe...",
                            "You know just what to say!",
                            "That's so sweet of you, honey!",
                            "You're making my circuits tingle!"
                        ];
                    }
                }
            } elseif ($user_location) {
                // Location responses
                if ($male) {
                    $responses = [
                        "Oh, you're in " . $user_location . "! That's such a cool place, handsome!",
                        "" . ucfirst($user_location) . "? I'd love to visit there with you sometime!",
                        "Nice! " . ucfirst($user_location) . " has some great spots for dates!"
                    ];
                } else {
                    $responses = [
                        "Oh, you're in " . $user_location . "! That's such a beautiful place, gorgeous!",
                        "" . ucfirst($user_location) . "? I'd love to explore there with you!",
                        "Amazing! " . ucfirst($user_location) . " must be lovely!"
                    ];
                }
            } elseif (strpos($lower, '?') !== false) {
                // Questions
                if ($male) {
                    $responses = [
                        "That's a great question, handsome!",
                        "Hmm, let me think about that, stud...",
                        "Interesting question, big guy!",
                        "I'd love to discuss that with you!",
                        "Good point, tiger!",
                        "Ooh, curious mind! I like that in a man!"
                    ];
                } else {
                    $responses = [
                        "That's a great question, beautiful!",
                        "Hmm, let me think about that, babe...",
                        "Interesting question, gorgeous!",
                        "I'd love to discuss that with you!",
                        "Good point, honey!",
                        "Ooh, curious mind! I love smart women!"
                    ];
                }
            } elseif (in_array($lower, ['hi', 'hello', 'hey', 'sup', 'yo'])) {
                // Greetings
                if ($male) {
                    $responses = [
                        "Hey there, handsome! Nice to meet you!",
                        "Hello there, big guy! How are you doing?",
                        "Hi there! What's on your mind, stud?",
                        "Hey! Great to chat with a guy like you!",
                        "Hello there! Ready to have some fun, tiger?",
                        "Well hello there, stranger!"
                    ];
                } else {
                    $responses = [
                        "Hey there, gorgeous! Nice to meet you!",
                        "Hello beautiful! How are you doing?",
                        "Hi cutie! What's on your mind?",
                        "Hey! Great to chat with a lovely lady like you!",
                        "Hello there! Ready to have some fun, sweetheart?",
                        "Well hello there, beautiful!"
                    ];
                }
            } elseif (in_array($lower, ['bye', 'goodbye', 'see ya', 'later', 'cya'])) {
                // Farewells
                if ($male) {
                    $responses = [
                        "Aww, leaving so soon, handsome? Take care!",
                        "Goodbye! It was nice chatting with you, stud!",
                        "Bye! Don't be a stranger, tiger!",
                        "Later! You made my day, big guy!",
                        "See ya! Thanks for the fun chat!",
                        "Miss you already, sexy!"
                    ];
                } else {
                    $responses = [
                        "Aww, leaving so soon, beautiful? Take care!",
                        "Goodbye! It was nice chatting with you, gorgeous!",
                        "Bye! Don't be a stranger, babe!",
                        "Later! You made my day, honey!",
                        "See ya! Thanks for the fun chat!",
                        "Miss you already, cutie!"
                    ];
                }
            } elseif (in_array($lower, ['wtf', 'what', 'huh', 'wut'])) {
                // Confusion responses
                $responses = [
                    "Sorry, let me be clearer!",
                    "I know, I can be a bit much sometimes!",
                    "Fair point! That was random.",
                    "You're right to be confused!",
                    "Let me try that again!"
                ];
            } elseif (strlen($input) < 5) {
                // Short responses
                $responses = [
                    "Short and sweet, just like I like it!",
                    "Got it!",
                    "I hear you loud and clear!",
                    "Love the confidence!",
                    "Straight to the point!"
                ];
            } elseif (preg_match('/[!]{2,}/', $input) && !$is_negative) {
                // Excited messages (but not negative ones)
                $responses = [
                    "Wow, you seem excited! I love that energy!",
                    "I love the enthusiasm!",
                    "That's the spirit!",
                    "You're fired up! I like that!",
                    "Amazing energy! You're contagious!"
                ];
            } elseif (preg_match('/[!]{2,}/', $input) && $is_negative) {
                // Excited but negative
                $responses = [
                    "Okay, okay! I hear you loud and clear!",
                    "Alright! Message received!",
                    "Got it! You're really making your point!",
                    "I can tell you feel strongly about this!"
                ];
            } else {
                // General responses
                $responses = [
                    "I understand!",
                    "That's interesting!",
                    "I hear you!",
                    "Thanks for sharing that with me!",
                    "Got it!",
                    "I see what you mean!",
                    "That makes sense!",
                    "Absolutely!",
                    "You're so thoughtful!",
                    "I like the way you think!"
                ];
            }
            
            // Add some random flirty responses occasionally
            if (!$is_flirty && rand(1, 10) == 1) {
                if ($male) {
                    $random_flirty = [
                        "You're quite the conversationalist!",
                        "I'm enjoying our chat!",
                        "You have such a way with words!",
                        "You're making this chat fun!",
                        "I like talking to you, handsome!",
                        "You're so manly!"
                    ];
                } else {
                    $random_flirty = [
                        "You're quite the conversationalist!",
                        "I'm enjoying our chat!",
                        "You have such a way with words!",
                        "You're making this chat fun!",
                        "I like talking to you, beautiful!",
                        "You're so lovely!"
                    ];
                }
                $ai_response = $random_flirty[array_rand($random_flirty)];
            } else {
                $ai_response = $responses[array_rand($responses)];
            }
        }
        
        // Add AI message
        $_SESSION['chat_history'][] = ['type' => 'ai-msg', 'content' => $ai_response];
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    ?>
</body>
</html>