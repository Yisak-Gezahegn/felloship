<?php
// Check authentication and role BEFORE including header
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('fellowship_leader') && !hasRole('coordinator') && !hasRole('developer')) {
    header('Location: ../home.php');
    exit();
}

$pageTitle = "Create Post/Event - Haramaya Fellowship";
include '../includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_type = $_POST['post_type'];
    $content = trim($_POST['content']);
    
    if (empty($content)) {
        $error = "Content is required!";
    } else {
        // Handle image upload
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = '../uploads/posts/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = $new_filename;
            }
        }
        
        if ($post_type === 'event') {
            // Create event
            $event_title = trim($_POST['event_title']);
            $event_date = $_POST['event_date'];
            $event_location = trim($_POST['event_location']);
            
            $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, location, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssi", $event_title, $content, $event_date, $event_location, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                // Also create a post about the event
                $event_content = "📅 New Event: $event_title\n\n$content\n\n📍 Location: $event_location\n🕐 Date: " . date('M d, Y h:i A', strtotime($event_date));
                $stmt2 = $conn->prepare("INSERT INTO posts (user_id, content, post_type, image, created_at) VALUES (?, ?, 'event', ?, NOW())");
                $stmt2->bind_param("iss", $_SESSION['user_id'], $event_content, $image_path);
                $stmt2->execute();
                
                $success = "Event created successfully!";
            } else {
                $error = "Failed to create event.";
            }
        } else {
            // Create regular post
            $stmt = $conn->prepare("INSERT INTO posts (user_id, content, post_type, image, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("isss", $_SESSION['user_id'], $content, $post_type, $image_path);
            
            if ($stmt->execute()) {
                $success = "Post created successfully!";
            } else {
                $error = "Failed to create post.";
            }
        }
    }
}
?>

<main style="padding: 25px 0;">
    <div class="container">
        <!-- Page Header -->
        <div class="dashboard-welcome">
            <h2>✍️ Create Post or Event</h2>
            <p>Share updates, announcements, or schedule events with the fellowship</p>
        </div>

        <div class="dashboard">
            <!-- Sidebar -->
            <aside class="sidebar">
                <h3>📊 Leader Panel</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                    <li><a href="create-post.php" class="active"><i class="fas fa-plus"></i> Create Post</a></li>
                    <li><a href="my-groups.php"><i class="fas fa-users"></i> My Groups</a></li>
                    <li><a href="create-group.php"><i class="fas fa-plus-circle"></i> Create Group</a></li>
                    <li><a href="../messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="financial-reports.php"><i class="fas fa-dollar-sign"></i> Financial Reports</a></li>
                    <li><a href="manage-users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </aside>
            
            <!-- Main Content -->
            <div class="main-content">
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <a href="../home.php" style="margin-left: 15px;">View in Feed</a>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="section-header">
                        <h3>What would you like to share?</h3>
                    </div>

                    <!-- Post Type Selector -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 25px;">
                        <button type="button" 
                                onclick="selectPostType('announcement')" 
                                id="btn-announcement"
                                class="quick-action-btn"
                                style="border: 2px solid var(--spiritual-purple); cursor: pointer;">
                            <i class="fas fa-bullhorn"></i>
                            <h4>Announcement</h4>
                        </button>
                        <button type="button" 
                                onclick="selectPostType('update')" 
                                id="btn-update"
                                class="quick-action-btn"
                                style="border: 2px solid var(--spiritual-blue); cursor: pointer;">
                            <i class="fas fa-info-circle"></i>
                            <h4>Update</h4>
                        </button>
                        <button type="button" 
                                onclick="selectPostType('prayer')" 
                                id="btn-prayer"
                                class="quick-action-btn"
                                style="border: 2px solid var(--spiritual-gold); cursor: pointer;">
                            <i class="fas fa-praying-hands"></i>
                            <h4>Prayer Request</h4>
                        </button>
                        <button type="button" 
                                onclick="selectPostType('event')" 
                                id="btn-event"
                                class="quick-action-btn"
                                style="border: 2px solid var(--success-color); cursor: pointer;">
                            <i class="fas fa-calendar"></i>
                            <h4>Event</h4>
                        </button>
                    </div>

                    <form method="POST" action="" enctype="multipart/form-data" id="postForm">
                        <input type="hidden" name="post_type" id="post_type" value="announcement">

                        <!-- Event-specific fields (hidden by default) -->
                        <div id="eventFields" style="display: none;">
                            <div class="form-group">
                                <label for="event_title">
                                    <i class="fas fa-heading"></i> Event Title <span style="color: var(--danger-color);">*</span>
                                </label>
                                <input type="text" 
                                       id="event_title" 
                                       name="event_title" 
                                       class="form-control" 
                                       placeholder="Enter event title">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label for="event_date">
                                        <i class="fas fa-calendar"></i> Event Date & Time <span style="color: var(--danger-color);">*</span>
                                    </label>
                                    <input type="datetime-local" 
                                           id="event_date" 
                                           name="event_date" 
                                           class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="event_location">
                                        <i class="fas fa-map-marker-alt"></i> Location <span style="color: var(--danger-color);">*</span>
                                    </label>
                                    <input type="text" 
                                           id="event_location" 
                                           name="event_location" 
                                           class="form-control" 
                                           placeholder="Event location">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="content">
                                <i class="fas fa-align-left"></i> Content <span style="color: var(--danger-color);">*</span>
                            </label>
                            <textarea id="content" 
                                      name="content" 
                                      class="form-control" 
                                      rows="8" 
                                      placeholder="Write your message here..."
                                      required></textarea>
                            <small style="color: var(--gray); display: block; margin-top: 5px;">
                                <i class="fas fa-info-circle"></i> Share your thoughts, updates, or prayer requests
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="image">
                                <i class="fas fa-image"></i> Add Image (Optional)
                            </label>
                            <input type="file" 
                                   id="image" 
                                   name="image" 
                                   class="form-control" 
                                   accept="image/*"
                                   onchange="previewImage(this)">
                            <div id="imagePreview" style="margin-top: 15px; display: none;">
                                <img id="preview" src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: var(--border-radius); box-shadow: var(--shadow-md);">
                            </div>
                        </div>

                        <div class="info-box">
                            <h4 style="margin-bottom: 10px;"><i class="fas fa-lightbulb"></i> Posting Guidelines</h4>
                            <ul style="margin: 0; padding-left: 20px; color: var(--gray);">
                                <li>Keep content respectful and appropriate</li>
                                <li>Use clear and concise language</li>
                                <li>Add images to make posts more engaging</li>
                                <li>For events, provide complete details</li>
                                <li>Prayer requests should be specific and heartfelt</li>
                            </ul>
                        </div>

                        <div style="display: flex; gap: 15px; margin-top: 25px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-paper-plane"></i> Publish Post
                            </button>
                            <button type="reset" class="btn" style="flex: 1; background: var(--gray); color: white;" onclick="resetForm()">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Recent Posts Preview -->
                <div class="card" style="margin-top: 20px;">
                    <div class="section-header">
                        <h3>My Recent Posts</h3>
                        <a href="../home.php" class="btn btn-gold" style="padding: 8px 16px; font-size: 0.9rem;">View All</a>
                    </div>
                    <?php
                    $recent_posts = $conn->query("SELECT p.*, 
                                                   (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id) as reactions,
                                                   (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comments
                                                   FROM posts p 
                                                   WHERE p.user_id = {$_SESSION['user_id']} 
                                                   ORDER BY p.created_at DESC LIMIT 3");
                    
                    if ($recent_posts->num_rows > 0):
                    ?>
                    <div style="display: grid; gap: 15px;">
                        <?php while ($post = $recent_posts->fetch_assoc()): ?>
                        <div style="padding: 15px; background: #f8f9fa; border-radius: var(--border-radius); border-left: 4px solid var(--spiritual-purple);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span class="status-badge" style="background: var(--spiritual-blue);">
                                    <?php echo ucfirst($post['post_type']); ?>
                                </span>
                                <span style="font-size: 0.85rem; color: var(--gray);"><?php echo timeAgo($post['created_at']); ?></span>
                            </div>
                            <p style="color: var(--dark-color); margin-bottom: 10px;"><?php echo substr($post['content'], 0, 100) . '...'; ?></p>
                            <div style="display: flex; gap: 15px; font-size: 0.9rem; color: var(--gray);">
                                <span><i class="fas fa-heart"></i> <?php echo $post['reactions']; ?></span>
                                <span><i class="fas fa-comment"></i> <?php echo $post['comments']; ?></span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <h3>No Posts Yet</h3>
                        <p>This will be your first post!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function selectPostType(type) {
    // Update hidden input
    document.getElementById('post_type').value = type;
    
    // Update button styles
    const buttons = ['announcement', 'update', 'prayer', 'event'];
    buttons.forEach(btn => {
        const element = document.getElementById('btn-' + btn);
        if (btn === type) {
            element.style.background = 'linear-gradient(135deg, var(--spiritual-purple), var(--spiritual-blue))';
            element.style.color = 'white';
            element.style.transform = 'scale(1.05)';
        } else {
            element.style.background = 'white';
            element.style.color = 'var(--dark-color)';
            element.style.transform = 'scale(1)';
        }
    });
    
    // Show/hide event fields
    const eventFields = document.getElementById('eventFields');
    const eventInputs = eventFields.querySelectorAll('input');
    
    if (type === 'event') {
        eventFields.style.display = 'block';
        eventInputs.forEach(input => input.required = true);
    } else {
        eventFields.style.display = 'none';
        eventInputs.forEach(input => input.required = false);
    }
}

function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

function resetForm() {
    document.getElementById('imagePreview').style.display = 'none';
    selectPostType('announcement');
}

// Initialize with announcement selected
selectPostType('announcement');
</script>

<?php include '../includes/footer.php'; ?>
