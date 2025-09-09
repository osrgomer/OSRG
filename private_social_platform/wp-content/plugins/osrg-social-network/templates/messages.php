<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is logged in
if (!osrg_social_is_logged_in()) {
    return '<p>Please log in to view messages.</p>';
}

$current_user = osrg_social_get_current_user();
global $wpdb;

// Handle new message
if ($_POST['message'] ?? false) {
    $wpdb->insert(
        $wpdb->prefix . 'social_messages',
        [
            'sender_id' => $current_user->ID,
            'receiver_id' => $_POST['receiver_id'],
            'content' => $_POST['message']
        ]
    );
}

// Get conversations
$conversations = $wpdb->get_results($wpdb->prepare("
    SELECT DISTINCT 
        CASE WHEN sender_id = %d THEN receiver_id ELSE sender_id END as other_user_id,
        u.user_login as username,
        u.social_avatar as avatar,
        MAX(m.created_at) as last_message_time
    FROM {$wpdb->prefix}social_messages m
    JOIN {$wpdb->prefix}users u ON (
        CASE WHEN sender_id = %d THEN receiver_id ELSE sender_id END = u.ID
    )
    WHERE sender_id = %d OR receiver_id = %d
    GROUP BY other_user_id
    ORDER BY last_message_time DESC
", $current_user->ID, $current_user->ID, $current_user->ID, $current_user->ID));

$selected_user = $_GET['user'] ?? ($conversations[0]->other_user_id ?? null);

if ($selected_user) {
    $messages = $wpdb->get_results($wpdb->prepare("
        SELECT m.*, u.user_login as sender_name
        FROM {$wpdb->prefix}social_messages m
        JOIN {$wpdb->prefix}users u ON m.sender_id = u.ID
        WHERE (sender_id = %d AND receiver_id = %d) OR (sender_id = %d AND receiver_id = %d)
        ORDER BY created_at ASC
    ", $current_user->ID, $selected_user, $selected_user, $current_user->ID));
}
?>

<style>
.osrg-messages { max-width: 800px; margin: 0 auto; display: flex; height: 500px; }
.osrg-conversations { width: 300px; border-right: 1px solid #ddd; overflow-y: auto; }
.osrg-chat { flex: 1; display: flex; flex-direction: column; }
.osrg-message { padding: 10px; margin: 5px; border-radius: 8px; max-width: 70%; }
.osrg-message.sent { background: #1877f2; color: white; margin-left: auto; }
.osrg-message.received { background: #f1f3f4; }
.osrg-conversation { padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; }
.osrg-conversation:hover { background: #f8f9fa; }
.osrg-conversation.active { background: #e3f2fd; }
</style>

<div class="osrg-messages">
    <div class="osrg-conversations">
        <h3 style="padding: 15px;">Conversations</h3>
        <?php foreach ($conversations as $conv): ?>
        <div class="osrg-conversation <?= $selected_user == $conv->other_user_id ? 'active' : '' ?>" 
             onclick="location.href='<?= add_query_arg('user', $conv->other_user_id) ?>'">
            <div style="display: flex; align-items: center; gap: 10px;">
                <?= osrg_social_get_avatar($conv->other_user_id, 40) ?>
                <div>
                    <strong><?= esc_html($conv->username) ?></strong>
                    <small style="display: block; color: #666;"><?= osrg_social_time_ago($conv->last_message_time) ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="osrg-chat">
        <?php if ($selected_user): ?>
        <div style="padding: 15px; border-bottom: 1px solid #ddd; background: #f8f9fa;">
            <strong>Chat with <?= esc_html($wpdb->get_var($wpdb->prepare("SELECT user_login FROM {$wpdb->prefix}users WHERE ID = %d", $selected_user))) ?></strong>
        </div>
        
        <div style="flex: 1; overflow-y: auto; padding: 15px;">
            <?php if (isset($messages)): ?>
                <?php foreach ($messages as $message): ?>
                <div class="osrg-message <?= $message->sender_id == $current_user->ID ? 'sent' : 'received' ?>">
                    <?= esc_html($message->content) ?>
                    <small style="display: block; margin-top: 5px; opacity: 0.7;">
                        <?= osrg_social_time_ago($message->created_at) ?>
                    </small>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <form method="POST" style="padding: 15px; border-top: 1px solid #ddd;">
            <input type="hidden" name="receiver_id" value="<?= $selected_user ?>">
            <div style="display: flex; gap: 10px;">
                <input type="text" name="message" placeholder="Type a message..." style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 20px;" required>
                <button type="submit" style="background: #1877f2; color: white; padding: 10px 20px; border: none; border-radius: 20px;">Send</button>
            </div>
        </form>
        <?php else: ?>
        <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666;">
            Select a conversation to start messaging
        </div>
        <?php endif; ?>
    </div>
</div>