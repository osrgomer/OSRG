jQuery(document).ready(function($) {
    var floatingButton = $('#wp-ai-helper-floating-button');
    var chatWidget = $('#wp-ai-helper-widget');
    var closeButton = $('#wp-ai-helper-close-button');
    var messagesContainer = $('#wp-ai-helper-messages');
    
    // Login form elements
    var loginForm = $('#wp-ai-helper-login-form');
    var usernameInput = $('#wp-ai-helper-username');
    var passwordInput = $('#wp-ai-helper-password');
    var loginButton = $('#wp-ai-helper-login-button');
    var showPasswordBtn = $('#wp-ai-helper-show-password');

    // Chat elements
    var chatInputArea = $('.wp-ai-helper-input-area');
    var chatInput = $('#wp-ai-helper-input');
    var sendButton = $('#wp-ai-helper-send');

    // Function to open the chat widget
    function openChat() {
        chatWidget.removeClass('wp-ai-helper-closed').addClass('wp-ai-helper-open');
        floatingButton.removeClass('wp-ai-helper-closed').addClass('wp-ai-helper-hidden');
        usernameInput.focus();
        messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
    }

    // Function to close the chat widget
    function closeChat() {
        chatWidget.removeClass('wp-ai-helper-open').addClass('wp-ai-helper-closed');
        floatingButton.removeClass('wp-ai-helper-hidden').addClass('wp-ai-helper-closed');
    }

    // Event listener for the floating button to open the chat
    floatingButton.on('click', openChat);

    // Event listener for the close button in the chat header
    closeButton.on('click', closeChat);

    // Function to handle login
    function login() {
        var username = usernameInput.val().trim();
        var password = passwordInput.val().trim();

        if (!username || !password) {
            addMessage('System', 'Please enter a username and password.', 'error-message');
            return;
        }

        // We use a dummy message for the credentials check
        $.post(wpAIHelper.ajax_url, {
            action: 'wp_ai_helper_chat',
            nonce: wpAIHelper.nonce,
            username: username,
            password: password,
            message: 'login_check_only'
        }, function(response) {
            if (response.reply && response.reply.includes('Access Denied')) {
                addMessage('System', 'Access Denied: Invalid username or password.', 'error-message');
            } else {
                addMessage('System', 'Login successful! You can now chat with Echo.', 'ai-message');
                loginForm.hide();
                chatInputArea.removeClass('wp-ai-helper-hidden');
                chatInput.focus();
            }
        }).fail(function() {
            addMessage('System', 'Login failed. Please try again.', 'error-message');
        });
    }

    // Event listener for the login button and Enter key
    loginButton.on('click', login);
    passwordInput.on('keypress', function(e) {
        if (e.which === 13) {
            login();
        }
    });

    // Event listeners for sending messages after login
    sendButton.on('click', sendMessage);
    chatInput.on('keypress', function(e) {
        if (e.which === 13) {
            sendMessage();
        }
    });

    // Function to toggle password visibility
    showPasswordBtn.on('click', function() {
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            $(this).text('🙈'); // Change to "hide" icon
        } else {
            passwordInput.attr('type', 'password');
            $(this).text('👁️'); // Change to "show" icon
        }
    });

    function sendMessage() {
        var message = chatInput.val().trim();
        if (!message) return;

        addMessage('You', message, 'user-message');
        chatInput.val('');

        chatInput.prop('disabled', true);
        sendButton.prop('disabled', true);
        addMessage('Echo', 'Thinking...', 'loading-message');
        var loadingMessageElement = messagesContainer.find('div:last-child');

        // Pass credentials with every chat message for persistent validation
        var username = usernameInput.val().trim();
        var password = passwordInput.val().trim();

        $.post(wpAIHelper.ajax_url, {
            action: 'wp_ai_helper_chat',
            nonce: wpAIHelper.nonce,
            message: message,
            username: username,
            password: password
        }, function(response) {
            loadingMessageElement.remove();
            addMessage('Echo', response.reply, 'ai-message');
        }).fail(function() {
            loadingMessageElement.remove();
            addMessage('Echo', 'Sorry, something went wrong. Please try again!', 'error-message');
        }).always(function() {
            chatInput.prop('disabled', false);
            sendButton.prop('disabled', false);
            chatInput.focus();
        });
    }

    function addMessage(sender, text, className = '') {
        var messageDiv = $('<div>').addClass(className);
        messageDiv.append('<strong>' + sender + ':</strong> ' + $('<div>').text(text).html());
        messagesContainer.append(messageDiv);
        messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
    }
});