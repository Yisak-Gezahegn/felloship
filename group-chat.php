<?php
$pageTitle = "Group Chat - Haramaya Fellowship";
include 'includes/header.php';

requireLogin();

$group_id = intval($_GET['id'] ?? 0);

if ($group_id <= 0) {
    header('Location: groups.php?error=invalid_group');
    exit();
}

// Handle member management actions BEFORE any output
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        $action = $_POST['action'];
        
        // Get group details to check permissions
        $group_check = $conn->query("SELECT created_by FROM groups WHERE group_id = $group_id")->fetch_assoc();
        $is_creator = ($group_check['created_by'] == $_SESSION['user_id']);
        $is_leader = hasRole('fellowship_leader') || hasRole('coordinator') || hasRole('developer');
        
        if ($is_creator || $is_leader) {
            if ($action === 'add_member') {
                // Check if user is already a member
                $existing = $conn->query("SELECT 1 FROM group_members WHERE group_id = $group_id AND user_id = $user_id")->num_rows;
                
                if ($existing > 0) {
                    $error = "User is already a member of this group!";
                } else {
                    $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id, joined_at) VALUES (?, ?, NOW())");
                    $stmt->bind_param("ii", $group_id, $user_id);
                    
                    if ($stmt->execute()) {
                        $success = "Member added successfully!";
                    } else {
                        $error = "Failed to add member.";
                    }
                }
            } elseif ($action === 'remove_member') {
                // Don't allow removing the group creator
                if ($user_id == $group_check['created_by']) {
                    $error = "Cannot remove the group creator!";
                } else {
                    $stmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
                    $stmt->bind_param("ii", $group_id, $user_id);
                    
                    if ($stmt->execute()) {
                        $success = "Member removed successfully!";
                    } else {
                        $error = "Failed to remove member.";
                    }
                }
            }
        } else {
            $error = "You don't have permission to manage members!";
        }
    }
}

// Get group details
$group_result = $conn->query("SELECT g.*, u.full_name as creator_name 
                              FROM groups g 
                              LEFT JOIN users u ON g.created_by = u.user_id 
                              WHERE g.group_id = $group_id");

if ($group_result->num_rows == 0) {
    header('Location: groups.php?error=group_not_found');
    exit();
}

$group = $group_result->fetch_assoc();

// Check if user is a member
$is_member = $conn->query("SELECT 1 FROM group_members WHERE group_id = $group_id AND user_id = {$_SESSION['user_id']}")->num_rows;

if ($is_member == 0) {
    header('Location: groups.php?error=not_member');
    exit();
}

// Check if current user can manage members
$is_creator = ($group['created_by'] == $_SESSION['user_id']);
$is_leader = hasRole('fellowship_leader') || hasRole('coordinator') || hasRole('developer');
$can_manage = $is_creator || $is_leader;

// Get group members
$members = $conn->query("SELECT u.*, s.section_name 
                         FROM group_members gm
                         JOIN users u ON gm.user_id = u.user_id
                         LEFT JOIN sections s ON u.section_id = s.section_id
                         WHERE gm.group_id = $group_id
                         ORDER BY u.full_name");

// Get available users to add (only if user can manage members)
$available_users = null;
if ($can_manage) {
    $available_users = $conn->query("SELECT u.*, s.section_name 
                                     FROM users u 
                                     LEFT JOIN sections s ON u.section_id = s.section_id
                                     WHERE u.status = 'approved' 
                                     AND u.user_id NOT IN (
                                         SELECT user_id FROM group_members WHERE group_id = $group_id
                                     )
                                     ORDER BY u.full_name");
}

// Get group messages
$messages = $conn->query("SELECT gm.*, u.full_name, u.profile_picture 
                          FROM group_messages gm
                          JOIN users u ON gm.user_id = u.user_id
                          WHERE gm.group_id = $group_id
                          ORDER BY gm.created_at ASC
                          LIMIT 100");

$member_count = $members->num_rows;
$is_creator = ($group['created_by'] == $_SESSION['user_id']);
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h2>💬 <?php echo htmlspecialchars($group['group_name']); ?></h2>
                    <p><?php echo htmlspecialchars($group['description']); ?></p>
                    <div style="margin-top: 10px;">
                        <span style="display: inline-block; padding: 5px 12px; background: <?php echo $group['group_type'] == 'public' ? 'var(--success-color)' : 'var(--spiritual-purple)'; ?>; color: white; border-radius: 15px; font-size: 0.85rem; margin-right: 10px;">
                            <i class="fas fa-<?php echo $group['group_type'] == 'public' ? 'globe' : 'lock'; ?>"></i>
                            <?php echo ucfirst($group['group_type']); ?>
                        </span>
                        <span style="font-size: 0.9rem; color: var(--gray);">
                            <i class="fas fa-users"></i> <?php echo $member_count; ?> Members
                        </span>
                        <span style="font-size: 0.9rem; color: var(--gray); margin-left: 15px;">
                            <i class="fas fa-user-circle"></i> Created by <?php echo htmlspecialchars($group['creator_name']); ?>
                        </span>
                    </div>
                </div>
                <div>
                    <a href="groups.php" class="btn" style="background: var(--gray); color: white;">
                        <i class="fas fa-arrow-left"></i> Back to Groups
                    </a>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Message sent successfully!
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> 
            <?php 
            switch($_GET['error']) {
                case 'empty_message': echo 'Message cannot be empty!'; break;
                case 'not_member': echo 'You are not a member of this group!'; break;
                case 'send_failed': echo 'Failed to send message. Please try again.'; break;
                default: echo 'An error occurred.'; break;
            }
            ?>
        </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 300px; gap: 20px; margin-top: 20px;">
            <!-- Chat Area -->
            <div class="card">
                <div class="section-header">
                    <h3>Group Chat</h3>
                </div>

                <!-- Messages -->
                <div id="messagesContainer" style="height: 500px; overflow-y: auto; padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 15px;">
                    <?php if ($messages->num_rows > 0): ?>
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                        <div id="message-<?php echo $msg['message_id']; ?>" style="display: flex; gap: 10px; margin-bottom: 15px; <?php echo $msg['user_id'] == $_SESSION['user_id'] ? 'flex-direction: row-reverse;' : ''; ?>">
                            <?php if (!empty($msg['profile_picture']) && $msg['profile_picture'] !== 'default.jpg' && file_exists("uploads/profiles/{$msg['profile_picture']}")): ?>
                            <img src="uploads/profiles/<?php echo $msg['profile_picture']; ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                            <div class="profile-pic-default" style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <?php endif; ?>
                            <div style="max-width: 70%; position: relative;">
                                <div style="background: <?php echo $msg['user_id'] == $_SESSION['user_id'] ? 'var(--spiritual-blue)' : 'white'; ?>; color: <?php echo $msg['user_id'] == $_SESSION['user_id'] ? 'white' : 'var(--dark-color)'; ?>; padding: 12px 15px; border-radius: 12px; box-shadow: var(--shadow-sm); position: relative;">
                                    <?php if ($msg['user_id'] != $_SESSION['user_id']): ?>
                                    <strong style="font-size: 0.85rem; display: block; margin-bottom: 5px;"><?php echo htmlspecialchars($msg['full_name']); ?></strong>
                                    <?php endif; ?>
                                    <p style="margin: 0;"><?php echo nl2br(htmlspecialchars($msg['message_text'])); ?></p>
                                    
                                    <?php if (!empty($msg['file_attachment'])): ?>
                                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.2);">
                                        <a href="uploads/group_files/<?php echo $msg['file_attachment']; ?>" 
                                           target="_blank" 
                                           style="color: <?php echo $msg['user_id'] == $_SESSION['user_id'] ? 'white' : 'var(--spiritual-blue)'; ?>; text-decoration: none;">
                                            <i class="fas fa-paperclip"></i> <?php echo $msg['file_attachment']; ?>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($msg['user_id'] == $_SESSION['user_id']): ?>
                                    <button onclick="deleteMessage(<?php echo $msg['message_id']; ?>)" 
                                            style="position: absolute; top: 5px; right: 5px; background: rgba(255,255,255,0.2); border: none; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 0.7rem; cursor: pointer; opacity: 0.7; transition: opacity 0.3s;" 
                                            onmouseover="this.style.opacity='1'" 
                                            onmouseout="this.style.opacity='0.7'"
                                            title="Delete message">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <span style="font-size: 0.75rem; color: var(--gray); margin-top: 3px; display: block; <?php echo $msg['user_id'] == $_SESSION['user_id'] ? 'text-align: right;' : ''; ?>">
                                    <?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>No Messages Yet</h3>
                        <p>Start the conversation with your group members</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Send Message -->
                <form method="POST" action="api/send-group-message.php" id="messageForm" enctype="multipart/form-data">
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    
                    <!-- File preview area -->
                    <div id="filePreview" style="display: none; margin-bottom: 10px; padding: 10px; background: #e9ecef; border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span id="fileName" style="font-size: 0.9rem;"></span>
                            <button type="button" onclick="removeFile()" style="background: none; border: none; color: var(--danger-color); cursor: pointer;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; align-items: end;">
                        <div style="flex: 1;">
                            <textarea name="message" id="messageInput" class="form-control" rows="2" placeholder="Type your message..." style="resize: none;"></textarea>
                        </div>
                        <div style="display: flex; gap: 5px;">
                            <label for="fileInput" class="btn" style="background: var(--spiritual-gold); color: var(--dark-color); padding: 10px 15px; cursor: pointer; margin: 0;">
                                <i class="fas fa-paperclip"></i>
                            </label>
                            <input type="file" id="fileInput" name="file" style="display: none;" onchange="previewFile(this)">
                            
                            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                                <i class="fas fa-paper-plane"></i> Send
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Members Sidebar -->
            <div class="card">
                <div class="section-header">
                    <h3>Members (<?php echo $member_count; ?>)</h3>
                    <?php if ($can_manage): ?>
                    <button class="btn btn-primary" onclick="showAddMemberModal()" style="padding: 5px 10px; font-size: 0.85rem;">
                        <i class="fas fa-user-plus"></i> Add
                    </button>
                    <?php endif; ?>
                </div>

                <div style="max-height: 600px; overflow-y: auto;">
                    <?php 
                    $members->data_seek(0);
                    while ($member = $members->fetch_assoc()): 
                    ?>
                    <div style="display: flex; align-items: center; gap: 10px; padding: 10px; border-bottom: 1px solid #e9ecef;">
                        <?php if (!empty($member['profile_picture']) && $member['profile_picture'] !== 'default.jpg' && file_exists("uploads/profiles/{$member['profile_picture']}")): ?>
                        <img src="uploads/profiles/<?php echo $member['profile_picture']; ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                        <div class="profile-pic-default" style="width: 40px; height: 40px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <?php endif; ?>
                        <div style="flex: 1;">
                            <strong style="font-size: 0.9rem; display: block;"><?php echo htmlspecialchars($member['full_name']); ?></strong>
                            <span style="font-size: 0.75rem; color: var(--gray);">
                                <?php echo ucfirst(str_replace('_', ' ', $member['role'])); ?>
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <?php if ($member['is_online']): ?>
                            <span style="width: 10px; height: 10px; background: var(--success-color); border-radius: 50%; box-shadow: 0 0 6px var(--success-color);"></span>
                            <?php endif; ?>
                            
                            <?php if ($member['user_id'] == $group['created_by']): ?>
                            <span class="status-badge" style="background: var(--spiritual-gold); color: var(--dark-color); font-size: 0.7rem; padding: 3px 8px;">
                                <i class="fas fa-crown"></i>
                            </span>
                            <?php elseif ($can_manage): ?>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="user_id" value="<?php echo $member['user_id']; ?>">
                                <input type="hidden" name="action" value="remove_member">
                                <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 0.75rem;" onclick="return confirm('Remove <?php echo htmlspecialchars($member['full_name']); ?> from this group?')" title="Remove from group">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Auto-scroll to bottom of messages
function scrollToBottom() {
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

window.addEventListener('load', scrollToBottom);

// Handle form submission
document.getElementById('messageForm').addEventListener('submit', function(e) {
    const messageInput = document.getElementById('messageInput');
    const fileInput = document.getElementById('fileInput');
    
    if (messageInput.value.trim() === '' && !fileInput.files.length) {
        e.preventDefault();
        alert('Please enter a message or select a file to share.');
        return false;
    }
});

// Auto-resize textarea
document.getElementById('messageInput').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});

// File preview function
function previewFile(input) {
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        fileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
        filePreview.style.display = 'block';
    } else {
        filePreview.style.display = 'none';
    }
}

// Remove file function
function removeFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('filePreview').style.display = 'none';
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Focus on message input
document.getElementById('messageInput').focus();

// Enter key to send (Shift+Enter for new line)
document.getElementById('messageInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('messageForm').submit();
    }
});

// Delete message function
function deleteMessage(messageId) {
    if (!confirm('Are you sure you want to delete this message?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('message_id', messageId);
    
    fetch('api/delete-message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const messageElement = document.getElementById('message-' + messageId);
            if (messageElement) {
                messageElement.style.opacity = '0';
                messageElement.style.transform = 'scale(0.8)';
                messageElement.style.transition = 'all 0.3s ease';
                setTimeout(() => {
                    messageElement.remove();
                }, 300);
            }
        } else {
            alert('Failed to delete message: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the message.');
    });
}
</script>

<style>
@media (max-width: 768px) {
    main .container > div > div[style*="grid-template-columns: 1fr 300px"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<!-- Add Member Modal -->
<?php if ($can_manage && $available_users && $available_users->num_rows > 0): ?>
<div id="addMemberModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: var(--border-radius); width: 90%; max-width: 500px; box-shadow: var(--shadow-lg); max-height: 80vh; overflow-y: auto;">
        <div style="padding: 20px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; color: var(--primary-color);">Add Member to Group</h3>
            <button onclick="hideAddMemberModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray);">&times;</button>
        </div>
        <div style="padding: 20px;">
            <p style="margin-bottom: 15px; color: var(--gray);">Select users to add to this group:</p>
            
            <?php 
            $available_users->data_seek(0);
            while ($user = $available_users->fetch_assoc()): 
            ?>
            <div style="display: flex; align-items: center; gap: 15px; padding: 12px; border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 10px; transition: all 0.3s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'default.jpg' && file_exists("uploads/profiles/{$user['profile_picture']}")): ?>
                <img src="uploads/profiles/<?php echo $user['profile_picture']; ?>" alt="Profile" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                <div class="profile-pic-default" style="width: 45px; height: 45px;">
                    <i class="fas fa-user"></i>
                </div>
                <?php endif; ?>
                
                <div style="flex: 1;">
                    <h4 style="margin: 0 0 3px 0; color: var(--primary-color); font-size: 0.95rem;"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p style="margin: 0; font-size: 0.8rem; color: var(--gray);">
                        <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                        <?php if ($user['section_name']): ?>
                        • <?php echo $user['section_name']; ?>
                        <?php endif; ?>
                    </p>
                </div>
                
                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    <input type="hidden" name="action" value="add_member">
                    <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem;" onclick="return confirm('Add <?php echo htmlspecialchars($user['full_name']); ?> to this group?')">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </form>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Add Member Modal functions
function showAddMemberModal() {
    const modal = document.getElementById('addMemberModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function hideAddMemberModal() {
    const modal = document.getElementById('addMemberModal');
    if (modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
