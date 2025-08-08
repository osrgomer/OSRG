<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>Chat Application</title>
        <meta name="description" content="Chat Application" />
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
        <style>
            /* Basic Reset */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box; /* Include padding and border in the element's total width and height */
            }

            /* Body Styling */
            body {
                margin: 20px auto;
                font-family: "Lato", sans-serif; /* Fallback font */
                font-weight: 300;
                background-color: #f0f2f5; /* Light grey background */
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: calc(100vh - 40px); /* Adjust for body margin */
            }

            /* Form Styling */
            form {
                padding: 15px 25px;
                display: flex;
                gap: 10px;
                justify-content: center;
                align-items: center;
                background-color: #f8f8f8; /* Slightly different background for form */
                border-top: 1px solid #ccc;
            }

            form label {
                font-size: 1.5rem;
                font-weight: bold;
                color: #333;
            }

            /* Input Field Styling */
            input {
                font-family: "Lato", sans-serif;
                padding: 8px 12px;
                border-radius: 4px;
                border: 1px solid #ccc;
                outline: none; /* Remove outline on focus */
                transition: border-color 0.3s ease;
            }

            input:focus {
                border-color: #ff9800; /* Highlight on focus */
            }

            /* Anchor Tag Styling */
            a {
                color: #0000ff;
                text-decoration: none;
            }

            a:hover {
                text-decoration: underline;
            }

            /* Wrapper and LoginForm Styling */
            #wrapper,
            #loginform {
                margin: 0 auto;
                padding-bottom: 25px;
                background: #eee;
                width: 600px;
                max-width: 95%; /* Responsive width */
                border: 2px solid #212121;
                border-radius: 8px; /* More rounded corners */
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
                overflow: hidden; /* Ensures content stays within rounded corners */
            }

            #loginform {
                padding-top: 18px;
                text-align: center;
            }

            #loginform p {
                padding: 15px 25px;
                font-size: 1.4rem;
                font-weight: bold;
                color: #333;
            }

            /* Chatbox Styling */
            #chatbox {
                text-align: left;
                margin: 0 auto;
                margin-bottom: 25px;
                padding: 10px;
                background: #fff;
                height: 300px;
                width: calc(100% - 40px); /* Adjust width for padding */
                max-width: 530px; /* Max width for chatbox */
                border: 1px solid #a7a7a7;
                overflow-y: auto; /* Use y for vertical scrolling */
                border-radius: 4px;
                border-bottom: 4px solid #a7a7a7;
            }

            /* User Message Input Styling */
            #usermsg {
                flex: 1; /* Takes available space */
                border-radius: 4px;
                border: 1px solid #ff9800;
                padding: 10px;
                font-size: 1rem;
            }

            /* Name Input Styling (if applicable) */
            #name {
                border-radius: 4px;
                border 8px 12px;
                font-size: 1rem;
            }

            /* Submit Button Styling */
            #submitmsg,
            #enter {
                background: linear-gradient(to bottom, #ff9800, #e65100); /* Gradient background */
                border: none; /* Remove default border */
                color: white;
                padding: 8px 15px; /* Increased padding */
                font-weight: bold;
                border-radius: 4px;
                cursor: pointer; /* Indicate clickable */
                transition: background 0.3s ease, transform 0.1s ease;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Button shadow */
            }

            #submitmsg:hover,
            #enter:hover {
                background: linear-gradient(to bottom, #e65100, #ff9800); /* Invert gradient on hover */
                transform: translateY(-1px); /* Slight lift on hover */
            }

            #submitmsg:active,
            #enter:active {
                transform: translateY(0); /* Return to normal on click */
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            }

            /* Error Message Styling */
            .error {
                color: #ff0000;
                font-weight: bold;
                text-align: center;
                margin-top: 10px;
            }

            /* Menu Styling */
            #menu {
                padding: 15px 25px;
                display: flex;
                justify-content: space-between; /* Space out items */
                align-items: center;
                background-color: #333; /* Darker background for menu */
                color: white;
                border-bottom: 1px solid #555;
            }

            #menu p.welcome {
                flex: 1;
                font-weight: 400;
                font-size: 1.1rem;
            }

            #menu p.welcome b {
                color: #ffcc80; /* Highlight welcome name */
            }

            /* Exit Button Styling */
            a#exit {
                color: white;
                background: #c62828;
                padding: 6px 12px; /* Increased padding */
                border-radius: 4px;
                font-weight: bold;
                transition: background 0.3s ease;
            }

            a#exit:hover {
                background: #b71c1c; /* Darker red on hover */
                text-decoration: none; /* Remove underline on hover */
            }

            /* Message Line Styling */
            .msgln {
                margin: 0 0 8px 0; /* Increased bottom margin */
                line-height: 1.4;
            }

            .msgln span.left-info {
                color: orangered;
                font-weight: 600;
            }

            .msgln span.chat-time {
                color: #666;
                font-size: 0.7em; /* Relative font size */
                vertical-align: super;
                margin-left: 5px;
            }

            .msgln b.user-name,
            .msgln b.user-name-left {
                font-weight: bold;
                background: #546e7a; /* Greyish blue */
                color: white;
                padding: 3px 6px; /* Increased padding */
                font-size: 0.9em; /* Relative font size */
                border-radius: 4px;
                margin: 0 5px 0 0;
            }

            .msgln b.user-name-left {
                background: orangered;
            }

            /* Responsive Adjustments */
            @media (max-width: 600px) {
                html, body {
                    width: 100vw;
                    overflow-x: hidden;
                    max-width: 100vw;
                }
                #wrapper, #loginform {
                    max-width: 100vw;
                    width: 100vw;
                    margin: 0;
                    border-radius: 0;
                    border-width: 0 0 2px 0;
                    box-shadow: none;
                    padding-bottom: 10px;
                }
                #chatbox {
                    width: 98vw;
                    max-width: 98vw;
                    min-width: 0;
                    height: 180px;
                    font-size: 1em;
                    padding: 6px;
                }
                form {
                    flex-direction: column;
                    gap: 7px;
                    padding: 10px 5px;
                    width: 100vw;
                    box-sizing: border-box;
                }
                #usermsg, #submitmsg, #emoji-btn, #voice-btn {
                    width: 100%;
                    font-size: 1.1em;
                    min-height: 44px;
                    box-sizing: border-box;
                }
                #emoji-btn, #voice-btn {
                    font-size: 1.5em;
                    padding: 8px 0;
                    touch-action: manipulation;
                }
                #menu {
                    flex-direction: column;
                    gap: 10px;
                    padding: 10px 5px;
                    width: 100vw;
                    box-sizing: border-box;
                }
                #menu p.welcome, #menu p.logout {
                    text-align: center;
                }
                #emoji-picker {
                    left: 5vw !important;
                    right: 5vw !important;
                    min-width: 90vw !important;
                    max-width: 98vw !important;
                    font-size: 2em !important;
                    padding: 10px 0 10px 0 !important;
                    top: 60vh !important;
                }
            }

            /* Add more space between message and time */
            .msgln .chat-time {
                margin-left: 60px !important;
            }
        </style>
    </head>
    <body>
        <?php
        session_start();

        // PHP for handling logout (moved to top for early execution)
        if(isset($_GET['logout'])){
            if (isset($_SESSION['name'])) {
                $logout_message = "<div class='msgln'><span class='left-info'>User <b class='user-name-left'>". $_SESSION['name'] ."</b> has left the chat session.</span><br></div>";
                file_put_contents("log.html", $logout_message, FILE_APPEND | LOCK_EX);
            }
            unset($_SESSION['name']);
            unset($_SESSION['avatar']);
            // Instead of redirect, just show the login form below
        }

        // PHP login logic
        if(isset($_POST['enter'])){
            if(!empty($_POST['name'])){
                $_SESSION['name'] = stripslashes(htmlspecialchars($_POST['name']));
                if (!empty($_POST['avatar'])) {
                    $_SESSION['avatar'] = $_POST['avatar'];
                } else {
                    $_SESSION['avatar'] = 'cat'; // default avatar
                }
            } else {
                echo '<span class="error">Please type in a name</span>';
            }
        }

        // Function to display the login form
        function loginForm(){
            echo '
            <div id="loginform">
                <p>Please enter your name to continue!</p>
                <form action="chat_app.php" method="post">
                    <label for="name">Name:</label>
                    <input type="text" name="name" id="name" />
                    <label for="avatar">Choose an avatar:</label>
                    <select name="avatar" id="avatar">
                        <option value="cat">😺 Cat</option>
                        <option value="dog">🐶 Dog</option>
                        <option value="fox">🦊 Fox</option>
                        <option value="panda">🐼 Panda</option>
                        <option value="alien">👽 Alien</option>
                        <option value="robot">🤖 Robot</option>
                        <option value="unicorn">🦄 Unicorn</option>
                        <option value="penguin">🐧 Penguin</option>
                    </select>
                    <input type="submit" name="enter" id="enter" value="Enter" />
                </form>
            </div>
            ';
        }
        ?>
        <?php
        // Conditional display of chat or login form
        if(!isset($_SESSION['name'])){ // If the session name is NOT set, show login form
            loginForm();
        } else { // Otherwise, show the chat interface
            $userAvatar = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : 'cat';
        ?>
            <div id="wrapper">
                <div id="menu">
                    <p class="welcome">Welcome, <b><?php echo $_SESSION['name']; ?></b></p>
                    <p class="logout"><a id="exit" href="#">Exit Chat</a></p>
                </div>

                <div id="chatbox">
                <?php
                if(file_exists("log.html") && filesize("log.html") > 0){
                    $contents = file_get_contents("log.html");
                    echo $contents;
                }
                ?>
                </div>

                <!-- Add to the chat input form: emoji picker and voice message button -->
                <form name="message" action="">
                    <input name="usermsg" type="text" id="usermsg" placeholder="Type your message here... 😊" autocomplete="off" />
                    <button type="button" id="emoji-btn" title="Insert emoji">😀</button>
                    <button type="button" id="voice-btn" title="Send voice message">🎤</button>
                    <input name="submitmsg" type="submit" id="submitmsg" value="Send" />
                </form>
            </div>
            <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script type="text/javascript">
            $(document).ready(function () {
                if ($('#chatbox').length) {
                    const userName = <?php echo json_encode($_SESSION['name']); ?>;
                    const userAvatar = <?php echo json_encode($userAvatar); ?>;
                    const avatarMap = {cat:'😺',dog:'🐶',fox:'🦊',panda:'🐼',alien:'👽',robot:'🤖',unicorn:'🦄',penguin:'🐧'};
                    const avatarEmoji = avatarMap[userAvatar] || '😺';

                    // --- Emoji Picker ---
                    const emojiList = ['😀','😂','😍','😎','😭','😡','👍','🙏','🎉','😺','🐶','🦊','🐼','👽','🤖','🦄','🐧'];
                    let emojiPicker = $('<div id="emoji-picker" style="display:none;position:absolute;z-index:1000;background:#fff;border:1px solid #ccc;padding:5px 8px 5px 8px;border-radius:6px;"></div>');
                    emojiList.forEach(e => {
                        emojiPicker.append('<span style="font-size:1.5em;cursor:pointer;padding:2px 6px;">'+e+'</span>');
                    });
                    $('body').append(emojiPicker);
                    $('#emoji-btn').on('click', function(e){
                        e.stopPropagation();
                        let offset = $(this).offset();
                        let isMobile = window.innerWidth <= 600;
                        if (isMobile) {
                            $('#emoji-picker').css({top: '60vh', left: '5vw', right: '5vw', minWidth: '90vw', maxWidth: '98vw', fontSize: '2em', padding: '10px 0'}).toggle();
                        } else {
                            $('#emoji-picker').css({top: offset.top + 30, left: offset.left, minWidth: '', maxWidth: '', fontSize: '', padding: ''}).toggle();
                        }
                    });
                    $(document).on('click', function(){ emojiPicker.hide(); });
                    emojiPicker.on('click', 'span', function(){
                        let emoji = $(this).text();
                        let input = $('#usermsg');
                        input.val(input.val() + emoji);
                        emojiPicker.hide();
                        input.focus();
                    });

                    // --- Voice Message ---
                    let mediaRecorder;
                    let audioChunks = [];
                    let isRecording = false;
                    $('#voice-btn').on('click', function(){
                        if (!isRecording) {
                            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                                alert('Voice messages are not supported in this browser.');
                                return;
                            }
                            navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
                                mediaRecorder = new MediaRecorder(stream);
                                mediaRecorder.start();
                                $('#voice-btn').text('⏹️ Stop');
                                audioChunks = [];
                                isRecording = true;
                                mediaRecorder.ondataavailable = e => {
                                    audioChunks.push(e.data);
                                };
                                mediaRecorder.onstop = () => {
                                    let audioBlob = new Blob(audioChunks, {type: 'audio/webm'});
                                    let formData = new FormData();
                                    formData.append('voice', audioBlob, 'voice.webm');
                                    formData.append('avatar', userAvatar); // Pass avatar with voice message
                                    $.ajax({
                                        url: 'post.php',
                                        type: 'POST',
                                        data: formData,
                                        processData: false,
                                        contentType: false,
                                        success: function(){
                                            loadLog();
                                        }
                                    });
                                    $('#voice-btn').text('🎤');
                                    isRecording = false;
                                };
                            });
                        } else {
                            if (mediaRecorder && mediaRecorder.state === 'recording') {
                                mediaRecorder.stop();
                            }
                        }
                    });

                    // --- Send message ---
                    $('#submitmsg').click(function(e) {
                        e.preventDefault();
                        var usermsg = $('#usermsg').val();
                        if (usermsg.trim() !== '') {
                            $.post("post.php", { text: usermsg, avatar: userAvatar }, function() {
                                loadLog();
                            });
                            $('#usermsg').val('');
                        }
                        return false;
                    });

                    // --- Enter key submits ---
                    $('#usermsg').keypress(function(e) {
                        if (e.which == 13) {
                            $('#submitmsg').click();
                            return false;
                        }
                    });

                    // --- Load chat log ---
                    function loadLog(){
                        var oldscrollHeight = $("#chatbox")[0].scrollHeight - 20;
                        $.ajax({
                            url: "log.html",
                            cache: false,
                            success: function(html){
                                $("#chatbox").html(html);
                                var newscrollHeight = $("#chatbox")[0].scrollHeight - 20;
                                if(newscrollHeight > oldscrollHeight){
                                    $("#chatbox").animate({ scrollTop: newscrollHeight }, 'normal');
                                }
                            }
                        });
                    }
                    setInterval(loadLog, 2500);

                    // --- Exit chat ---
                    $("#exit").click(function(){
                        var exit = window.confirm("Are you sure you want to end the session?");
                        if(exit==true){
                            window.location = 'test.php?logout'; // This triggers the PHP logout logic
                        }
                        return false;
                    });
                }
            });
            </script>
        <?php
        } // Closing the else block for displaying the chat interface
        ?>
    </body>
</html>
