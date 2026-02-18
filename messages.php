<?php
$pageTitle = "Messages - Haramaya Fellowship";
include 'includes/header.php';

requireLogin();

// Get user's conversations - Fixed query
$conversations = $conn->query("SELECT 
                               conv.other_user_id,
                               u.full_name, 
                               u.profile_picture, 
                               u.is_online,
                               conv.last_message_time,
                               conv.last_message,
                               conv.unread_count
                               FROM (
                                   SELECT 
                                       CASE 
                                           WHEN m.sender_id = {$_SESSION['user_id']} THEN m.receiver_id 
                                           ELSE m.sender_id 
                                       END as other_user_id,
                                       MAX(m.created_at) as last_message_time,
                                       (SELECT m2.message_text 
                                        FROM private_messages m2 
                                        WHERE ((m2.sender_id = {$_SESSION['user_id']} AND m2.receiver_id = CASE WHEN m.sender_id = {$_SESSION['user_id']} THEN m.receiver_id ELSE m.sender_id END) 
                                            OR (m2.sender_id = CASE WHEN m.sender_id = {$_SESSION['user_id']} THEN m.receiver_id ELSE m.sender_id END AND m2.receiver_id = {$_SESSION['user_id']}))
                                        ORDER BY m2.created_at DESC LIMIT 1) as last_message,
                                       SUM(CASE WHEN m.receiver_id = {$_SESSION['user_id']} AND m.is_read = 0 THEN 1 ELSE 0 END) as unread_count
                                   FROM private_messages m
                                   WHERE m.sender_id = {$_SESSION['user_id']} OR m.receiver_id = {$_SESSION['user_id']}
                                   GROUP BY other_user_id
                               ) conv
                               JOIN users u ON u.user_id = conv.other_user_id
                               WHERE u.status = 'approved'
                               ORDER BY conv.last_message_time DESC");

// Get all users for new conversation
$all_users = $conn->query("SELECT user_id, full_name, profile_picture, is_online, role 
                           FROM users 
                           WHERE user_id != {$_SESSION['user_id']} 
                           AND status = 'approved' 
                           ORDER BY full_name");
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>💬 Messages</h2>
            <p>Private conversations with fellowship members</p>
        </div>

        <div style="display: grid; grid-template-columns: 350px 1fr; gap: 25px; margin-top: 25px;">
            <!-- Conversations List -->
            <div class="card">
                <div class="section-header">
                    <h3>Conversations</h3>
                    <button class="btn btn-primary" onclick="showNewMessageModal()" style="padding: 5px 10px; font-size: 0.85rem;">
                        <i class="fas fa-plus"></i> New
                    </button>
                </div>

                <div style="max-height: 600px; overflow-y: auto;">
                    <?php if ($conversations->num_rows > 0): ?>
                        <?php while ($conv = $conversations->fetch_assoc()): ?>
                        <div class="conversation-item" onclick="openConversation(<?php echo $conv['other_user_id']; ?>, '<?php echo htmlspecialchars($conv['full_name']); ?>', '<?php echo $conv['profile_picture']; ?>', <?php echo $conv['is_online'] ? 'true' : 'false'; ?>)" style="display: flex; align-items: center; gap: 12px; padding: 15px; border-bottom: 1px solid #e9ecef; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                            <div style="position: relative;">
                                <?php if (!empty($conv['profile_picture']) && $conv['profile_picture'] !== 'default.jpg' && file_exists("uploads/profiles/{$conv['profile_picture']}")): ?>
                                <img src="uploads/profiles/<?php echo $conv['profile_picture']; ?>" alt="Profile" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                <?php else: ?>
                                <div class="profile-pic-default" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <?php endif; ?>
                                <?php if ($conv['is_online']): ?>
                                <span style="position: absolute; bottom: 2px; right: 2px; width: 12px; height: 12px; background: var(--success-color); border-radius: 50%; border: 2px solid white;"></span>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h4 style="margin: 0 0 5px 0; font-size: 0.95rem; color: var(--primary-color);"><?php echo htmlspecialchars($conv['full_name']); ?></h4>
                                <p style="margin: 0; font-size: 0.85rem; color: var(--gray); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php 
                                    $lastMsg = $conv['last_message'] ?? '';
                                    if (empty($lastMsg)) {
                                        echo 'No messages yet';
                                    } else {
                                        echo htmlspecialchars(strlen($lastMsg) > 40 ? substr($lastMsg, 0, 40) . '...' : $lastMsg);
                                    }
                                    ?>
                                </p>
                                <span style="font-size: 0.75rem; color: var(--gray);">
                                    <?php 
                                    if ($conv['last_message_time']) {
                                        echo date('M d, h:i A', strtotime($conv['last_message_time'])); 
                                    } else {
                                        echo 'No messages';
                                    }
                                    ?>
                                </span>
                            </div>
                            <?php if ($conv['unread_count'] > 0): ?>
                            <span style="background: var(--danger-color); color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;"><?php echo $conv['unread_count']; ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>No Conversations</h3>
                        <p>Start a new conversation with a fellowship member</p>
                        <button class="btn btn-primary" onclick="showNewMessageModal()" style="margin-top: 15px;">
                            <i class="fas fa-plus"></i> New Message
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="card">
                <div id="chatArea" style="display: flex; flex-direction: column; height: 600px;">
                    <div class="empty-state" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <i class="fas fa-comment-dots" style="font-size: 4rem; color: var(--spiritual-gold); margin-bottom: 20px;"></i>
                        <h3>Select a Conversation</h3>
                        <p>Choose a conversation from the left to start messaging</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- New Message Modal -->
<div id="newMessageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: var(--border-radius); width: 90%; max-width: 500px; box-shadow: var(--shadow-lg);">
        <div style="padding: 20px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; color: var(--primary-color);">New Message</h3>
            <button onclick="hideNewMessageModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray);">&times;</button>
        </div>
        <div style="padding: 20px;">
            <div class="form-group">
                <label>Select Recipient:</label>
                <select id="recipientSelect" class="form-control">
                    <option value="">Choose a member...</option>
                    <?php 
                    $all_users->data_seek(0);
                    while ($user = $all_users->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $user['user_id']; ?>" data-name="<?php echo htmlspecialchars($user['full_name']); ?>" data-picture="<?php echo $user['profile_picture']; ?>" data-online="<?php echo $user['is_online']; ?>">
                        <?php echo htmlspecialchars($user['full_name']); ?> 
                        (<?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Message:</label>
                <textarea id="newMessageText" class="form-control" rows="4" placeholder="Type your message..." style="resize: none;"></textarea>
            </div>
        </div>
        <div style="padding: 15px 20px; border-top: 1px solid #e9ecef; display: flex; gap: 10px; justify-content: flex-end;">
            <button class="btn" style="background: var(--gray); color: white;" onclick="hideNewMessageModal()">Cancel</button>
            <button class="btn btn-primary" onclick="sendNewMessage()">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
        </div>
    </div>
</div>

<script>
let currentChatUser = null;
let messageCheckInterval = null;

function showNewMessageModal() {
    document.getElementById('newMessageModal').style.display = 'flex';
    
    // Setup auto-resize for new message textarea (same as group chat)
    const newMessageText = document.getElementById('newMessageText');
    if (newMessageText) {
        newMessageText.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Focus on textarea
        setTimeout(() => {
            newMessageText.focus();
        }, 100);
    }
}

function hideNewMessageModal() {
    document.getElementById('newMessageModal').style.display = 'none';
    document.getElementById('recipientSelect').value = '';
    document.getElementById('newMessageText').value = '';
}

function sendNewMessage() {
    const recipientSelect = document.getElementById('recipientSelect');
    const messageTextarea = document.getElementById('newMessageText');
    const recipient = recipientSelect.value;
    const message = messageTextarea.value.trim();
    
    if (!recipient || !message) {
        alert('Please select a recipient and enter a message.');
        return;
    }
    
    const selectedOption = recipientSelect.options[recipientSelect.selectedIndex];
    const recipientName = selectedOption.getAttribute('data-name');
    const recipientPicture = selectedOption.getAttribute('data-picture');
    const recipientOnline = selectedOption.getAttribute('data-online') === '1';
    
    // Send message via API (same approach as group chat)
    const formData = new FormData();
    formData.append('receiver_id', recipient);
    formData.append('message', message);
    
    fetch('api/send-private-message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideNewMessageModal();
            openConversation(recipient, recipientName, recipientPicture, recipientOnline);
            location.reload(); // Reload to update conversation list
        } else {
            alert('Failed to send message: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending the message.');
    });
}

function openConversation(userId, userName, userPicture, isOnline) {
    currentChatUser = userId;
    
    // Clear any existing interval
    if (messageCheckInterval) {
        clearInterval(messageCheckInterval);
    }
    
    // Load conversation
    loadMessages(userId, userName, userPicture, isOnline);
    
    // Auto-refresh messages every 3 seconds
    messageCheckInterval = setInterval(() => {
        loadMessages(userId, userName, userPicture, isOnline, true);
    }, 3000);
}

function loadMessages(userId, userName, userPicture, isOnline, silent = false) {
    if (!silent) {
        document.getElementById('chatArea').innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--spiritual-blue);"></i></div>';
    }
    
    fetch(`api/get-private-messages.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderChat(userId, userName, userPicture, isOnline, data.messages);
            } else {
                document.getElementById('chatArea').innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><h3>Error</h3><p>' + data.message + '</p></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (!silent) {
                document.getElementById('chatArea').innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><h3>Error</h3><p>Failed to load messages</p></div>';
            }
        });
}

function renderChat(userId, userName, userPicture, isOnline, messages) {
    const chatArea = document.getElementById('chatArea');
    const currentScrollPos = chatArea.querySelector('#messagesContainer')?.scrollTop || 0;
    const wasAtBottom = chatArea.querySelector('#messagesContainer') ? 
        (currentScrollPos + chatArea.querySelector('#messagesContainer').clientHeight >= chatArea.querySelector('#messagesContainer').scrollHeight - 50) : true;
    
    let messagesHTML = '';
    messages.forEach(msg => {
        const isMine = msg.sender_id == <?php echo $_SESSION['user_id']; ?>;
        const profilePic = isMine ? '<?php echo $_SESSION['profile_picture']; ?>' : userPicture;
        const messageText = msg.message_text || '';
        
        messagesHTML += `
            <div style="display: flex; gap: 10px; margin-bottom: 15px; ${isMine ? 'flex-direction: row-reverse;' : ''}">
                ${profilePic && profilePic !== 'default.jpg' ? 
                    `<img src="uploads/profiles/${profilePic}" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">` :
                    `<div class="profile-pic-default" style="width: 40px; height: 40px;"><i class="fas fa-user"></i></div>`
                }
                <div style="max-width: 70%;">
                    <div style="background: ${isMine ? 'var(--spiritual-blue)' : 'white'}; color: ${isMine ? 'white' : 'var(--dark-color)'}; padding: 12px 15px; border-radius: 12px; box-shadow: var(--shadow-sm); word-wrap: break-word;">
                        <p style="margin: 0; white-space: pre-wrap;">${messageText.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>')}</p>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--gray); margin-top: 3px; display: block; ${isMine ? 'text-align: right;' : ''}">
                        ${new Date(msg.created_at).toLocaleString('en-US', {month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit'})}
                    </span>
                </div>
            </div>
        `;
    });
    
    chatArea.innerHTML = `
        <div style="padding: 15px; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; gap: 12px; background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f);">
            ${userPicture && userPicture !== 'default.jpg' ? 
                `<img src="uploads/profiles/${userPicture}" alt="Profile" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 3px solid white;">` :
                `<div class="profile-pic-default" style="width: 50px; height: 50px; border: 3px solid white;"><i class="fas fa-user"></i></div>`
            }
            <div style="flex: 1;">
                <h3 style="margin: 0; color: white;">${userName}</h3>
                <p style="margin: 0; font-size: 0.85rem; color: rgba(255,255,255,0.9);">
                    ${isOnline ? '<i class="fas fa-circle" style="font-size: 0.6rem;"></i> Online' : 'Offline'}
                </p>
            </div>
        </div>
        
        <div id="messagesContainer" style="flex: 1; overflow-y: auto; padding: 15px; background: #f8f9fa; max-height: 400px;">
            ${messages.length > 0 ? messagesHTML : '<div class="empty-state"><i class="fas fa-comments"></i><h3>No Messages Yet</h3><p>Start the conversation!</p></div>'}
        </div>
        
        <div style="padding: 15px; border-top: 1px solid #e9ecef;">
            <form id="sendMessageForm" onsubmit="sendPrivateMessage(event, ${userId})">
                <input type="hidden" name="receiver_id" value="${userId}">
                
                <div style="display: flex; gap: 10px; align-items: end;">
                    <div style="flex: 1;">
                        <textarea name="message" id="messageInput" class="form-control" rows="2" placeholder="Type your message..." style="resize: none;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                </div>
            </form>
        </div>
    `;
    
    // Scroll to bottom if was at bottom before
    if (wasAtBottom) {
        const container = document.getElementById('messagesContainer');
        if (container) {
            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 100);
        }
    }
    
    // Focus on input and setup exactly like group chat
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.focus();
        
        // Auto-resize textarea (exactly like group chat)
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Enter key to send (Shift+Enter for new line) - exactly like group chat
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('sendMessageForm').submit();
            }
        });
    }
}

function sendPrivateMessage(event, receiverId) {
    event.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (message === '') {
        alert('Please enter a message.');
        messageInput.focus();
        return false;
    }
    
    // Send message via API (same as group chat approach)
    const formData = new FormData();
    formData.append('receiver_id', receiverId);
    formData.append('message', message);
    
    fetch('api/send-private-message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            messageInput.style.height = 'auto';
            loadMessages(receiverId, '', '', false, true);
        } else {
            alert('Failed to send message: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending the message.');
    });
}

// Clean up interval when leaving page
window.addEventListener('beforeunload', () => {
    if (messageCheckInterval) {
        clearInterval(messageCheckInterval);
    }
});
</script>

<style>
@media (max-width: 768px) {
    main .container > div[style*="grid-template-columns: 350px 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>