<?php
// Check authentication and role BEFORE including header
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('coordinator')) {
    header('Location: ../home.php');
    exit();
}

$pageTitle = "Leaders Group - Haramaya Fellowship";
include '../includes/header.php';

// Get leaders group (coordinator's special group)
$leaders_group_result = $conn->query("SELECT * FROM groups WHERE group_name = 'Fellowship Leaders Group' AND created_by = {$_SESSION['user_id']} LIMIT 1");
$leaders_group = $leaders_group_result->fetch_assoc();

if (!$leaders_group) {
    // Create leaders group if it doesn't exist
    $stmt = $conn->prepare("INSERT INTO groups (group_name, description, group_type, created_by, created_at) VALUES (?, ?, ?, ?, NOW())");
    $group_name = 'Fellowship Leaders Group';
    $group_desc = 'Exclusive group for fellowship leaders and coordinator';
    $group_type = 'private';
    $stmt->bind_param("sssi", $group_name, $group_desc, $group_type, $_SESSION['user_id']);
    $stmt->execute();
    
    $group_id = $conn->insert_id;
    
    // Add coordinator as member
    $stmt2 = $conn->prepare("INSERT INTO group_members (group_id, user_id, joined_at) VALUES (?, ?, NOW())");
    $stmt2->bind_param("ii", $group_id, $_SESSION['user_id']);
    $stmt2->execute();
    
    // Get the created group
    $leaders_group = $conn->query("SELECT * FROM groups WHERE group_id = $group_id")->fetch_assoc();
} else {
    $group_id = $leaders_group['group_id'];
    
    // Check if coordinator is a member, if not add them
    $is_member = $conn->query("SELECT 1 FROM group_members WHERE group_id = $group_id AND user_id = {$_SESSION['user_id']}")->num_rows;
    if ($is_member == 0) {
        $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id, joined_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $group_id, $_SESSION['user_id']);
        $stmt->execute();
    }
}

// Debug: Check if we have the group and membership
echo "<!-- DEBUG: Group ID: $group_id, User ID: {$_SESSION['user_id']} -->";

// Get group members
$members = $conn->query("SELECT u.*, s.section_name 
                         FROM group_members gm
                         JOIN users u ON gm.user_id = u.user_id
                         LEFT JOIN sections s ON u.section_id = s.section_id
                         WHERE gm.group_id = $group_id
                         ORDER BY u.full_name");

// Debug: Check member count
echo "<!-- DEBUG: Member count: " . $members->num_rows . " -->";

// Get group messages
$messages = $conn->query("SELECT gm.*, u.full_name, u.profile_picture 
                          FROM group_messages gm
                          JOIN users u ON gm.user_id = u.user_id
                          WHERE gm.group_id = $group_id
                          ORDER BY gm.created_at ASC
                          LIMIT 50");

// Debug: Check message count
echo "<!-- DEBUG: Message count: " . $messages->num_rows . " -->";

$member_count = $members->num_rows;
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>👥 Leaders Group</h2>
            <p>Exclusive communication channel for fellowship leaders</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-users"></i> <?php echo $member_count; ?> Members
            </p>
        </div>

        <div class="dashboard">
            <aside class="sidebar">
                <h3>📋 Coordinator Panel</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="my-group.php" class="active"><i class="fas fa-users"></i> Leaders Group</a></li>
                    <li><a href="../messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="manage-users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="approve-leaders.php"><i class="fas fa-user-check"></i> Approve Leaders</a></li>
                    <li><a href="section-reports.php"><i class="fas fa-file-alt"></i> Section Reports</a></li>
                    <li><a href="create-post.php"><i class="fas fa-plus"></i> Create Post/Event</a></li>
                    <li><a href="search-members.php"><i class="fas fa-search"></i> Search Members</a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                </ul>
            </aside>
            
            <div class="main-content">
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

                <div style="display: grid; grid-template-columns: 1fr 300px; gap: 20px;">
                    <!-- Chat Area -->
                    <div class="card">
                        <div class="section-header">
                            <h3><?php echo htmlspecialchars($leaders_group['group_name']); ?></h3>
                        </div>

                        <!-- Messages -->
                        <div id="messagesContainer" style="height: 500px; overflow-y: auto; padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 15px;">
                            <?php 
                            // Debug: Show raw query results
                            echo "<!-- DEBUG: Checking messages for group_id: $group_id -->";
                            $debug_messages = $conn->query("SELECT COUNT(*) as count FROM group_messages WHERE group_id = $group_id");
                            $debug_count = $debug_messages->fetch_assoc()['count'];
                            echo "<!-- DEBUG: Total messages in database: $debug_count -->";
                            
                            if ($messages->num_rows > 0): ?>
                                <?php while ($msg = $messages->fetch_assoc()): ?>
                                <div id="message-<?php echo $msg['message_id']; ?>" style="display: flex; gap: 10px; margin-bottom: 15px; <?php echo $msg['user_id'] == $_SESSION['user_id'] ? 'flex-direction: row-reverse;' : ''; ?>">
                                    <?php if (!empty($msg['profile_picture']) && $msg['profile_picture'] !== 'default.jpg' && file_exists("../uploads/profiles/{$msg['profile_picture']}")): ?>
                                    <img src="../uploads/profiles/<?php echo $msg['profile_picture']; ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
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
                                                <a href="../uploads/group_files/<?php echo $msg['file_attachment']; ?>" 
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
                                <p>Start the conversation with your leaders</p>
                                <p style="font-size: 0.8rem; color: var(--gray);">Debug: Found <?php echo $debug_count; ?> messages in database</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Send Message -->
                        <form method="POST" action="../api/send-group-message.php" id="messageForm" enctype="multipart/form-data">
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
                        </div>

                        <div style="max-height: 600px; overflow-y: auto;">
                            <?php 
                            $members->data_seek(0); // Reset pointer
                            while ($member = $members->fetch_assoc()): 
                            ?>
                            <div style="display: flex; align-items: center; gap: 10px; padding: 10px; border-bottom: 1px solid #e9ecef;">
                                <?php if (!empty($member['profile_picture']) && $member['profile_picture'] !== 'default.jpg' && file_exists("../uploads/profiles/{$member['profile_picture']}")): ?>
                                <img src="../uploads/profiles/<?php echo $member['profile_picture']; ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
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
                                    
                                    <form method="POST" action="add-leader-to-group.php" style="margin: 0;">
                                        <input type="hidden" name="user_id" value="<?php echo $member['user_id']; ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 0.75rem;" onclick="return confirm('Remove <?php echo htmlspecialchars($member['full_name']); ?> from the Leaders Group?')" title="Remove from group">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>

                        <div style="margin-top: 15px;">
                            <a href="add-leader-to-group.php" class="btn btn-primary" style="width: 100%; text-align: center;">
                                <i class="fas fa-user-plus"></i> Add Leader
                            </a>
                        </div>
                    </div>
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

// Scroll to bottom on page load
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
    
    fetch('../api/delete-message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the message from the DOM
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

<?php include '../includes/footer.php'; ?>
