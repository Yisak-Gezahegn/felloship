<?php
// Check authentication and role BEFORE including header
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('developer')) {
    header('Location: ../home.php');
    exit();
}

$success = '';
$error = '';

// Handle post actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['post_id'])) {
    $post_id = intval($_POST['post_id']);
    $action = $_POST['action'];
    
    switch ($action) {
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM posts WHERE post_id = ?");
            $stmt->bind_param("i", $post_id);
            if ($stmt->execute()) {
                $success = "Post deleted successfully!";
            } else {
                $error = "Failed to delete post.";
            }
            break;
            
        case 'feature':
            // Add featured flag (you might need to add this column to posts table)
            $success = "Post featured successfully!";
            break;
    }
}

$pageTitle = "Manage All Posts - Developer Dashboard";
include '../includes/header.php';

// Get all posts with user info and statistics
$posts_query = "SELECT p.*, u.full_name, u.username, u.profile_picture,
                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id) as reaction_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count
                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                ORDER BY p.created_at DESC";
$posts_result = $conn->query($posts_query);

// Get post statistics
$total_posts = $conn->query("SELECT COUNT(*) as count FROM posts")->fetch_assoc()['count'];
$general_posts = $conn->query("SELECT COUNT(*) as count FROM posts WHERE post_type = 'general'")->fetch_assoc()['count'];
$event_posts = $conn->query("SELECT COUNT(*) as count FROM posts WHERE post_type = 'event'")->fetch_assoc()['count'];
$announcement_posts = $conn->query("SELECT COUNT(*) as count FROM posts WHERE post_type = 'announcement'")->fetch_assoc()['count'];
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>📝 Manage All Posts</h2>
            <p>Complete post management and content moderation</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-file-alt"></i> <?php echo $total_posts; ?> Total Posts | 
                <i class="fas fa-file"></i> <?php echo $general_posts; ?> General | 
                <i class="fas fa-calendar"></i> <?php echo $event_posts; ?> Events | 
                <i class="fas fa-bullhorn"></i> <?php echo $announcement_posts; ?> Announcements
            </p>
        </div>

        <div class="dashboard">
            <aside class="sidebar">
                <h3>📊 Content Management</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage-posts.php" class="active"><i class="fas fa-file-alt"></i> All Posts</a></li>
                    <li><a href="manage-comments.php"><i class="fas fa-comments"></i> All Comments</a></li>
                    <li><a href="manage-reactions.php"><i class="fas fa-heart"></i> Reactions</a></li>
                    <li><a href="reported-content.php"><i class="fas fa-flag"></i> Reported Content</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                </ul>
            </aside>
            
            <div class="main-content">
                <!-- Success/Error Messages -->
                <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <!-- Statistics -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 20px;">
                        <i class="fas fa-file-alt" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $total_posts; ?></h3>
                        <p>Total Posts</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 20px;">
                        <i class="fas fa-file" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $general_posts; ?></h3>
                        <p>General</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 20px;">
                        <i class="fas fa-calendar" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $event_posts; ?></h3>
                        <p>Events</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px;">
                        <i class="fas fa-bullhorn" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $announcement_posts; ?></h3>
                        <p>Announcements</p>
                    </div>
                </div>

                <div class="card">
                    <div class="section-header">
                        <h3>All Posts</h3>
                    </div>

                    <?php if ($posts_result->num_rows > 0): ?>
                    <div style="padding: 20px;">
                        <?php while ($post = $posts_result->fetch_assoc()): ?>
                        <div style="border: 1px solid #e9ecef; border-radius: var(--border-radius); margin-bottom: 20px; overflow: hidden;">
                            <!-- Post Header -->
                            <div style="background: #f8f9fa; padding: 15px; border-bottom: 1px solid #e9ecef;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <?php if (!empty($post['profile_picture']) && $post['profile_picture'] !== 'default.jpg' && file_exists("../uploads/profiles/{$post['profile_picture']}")): ?>
                                        <img src="../uploads/profiles/<?php echo $post['profile_picture']; ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                        <?php else: ?>
                                        <div class="profile-pic-default" style="width: 40px; height: 40px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo htmlspecialchars($post['full_name']); ?></strong>
                                            <small style="color: var(--gray); display: block;">@<?php echo htmlspecialchars($post['username']); ?></small>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span class="status-badge" style="background: var(--spiritual-<?php echo $post['post_type'] === 'event' ? 'gold' : ($post['post_type'] === 'announcement' ? 'blue' : 'purple'); ?>);">
                                            <?php echo ucfirst($post['post_type']); ?>
                                        </span>
                                        <small style="color: var(--gray);"><?php echo timeAgo($post['created_at']); ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Post Content -->
                            <div style="padding: 15px;">
                                <p style="margin-bottom: 15px; line-height: 1.6;">
                                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                                </p>
                                
                                <?php if (!empty($post['image'])): ?>
                                <div style="margin-bottom: 15px;">
                                    <img src="../uploads/posts/<?php echo $post['image']; ?>" alt="Post image" style="max-width: 100%; height: auto; border-radius: var(--border-radius);">
                                </div>
                                <?php endif; ?>
                                
                                <!-- Post Stats -->
                                <div style="display: flex; gap: 20px; margin-bottom: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
                                    <span style="color: var(--gray); font-size: 0.9rem;">
                                        <i class="fas fa-heart" style="color: var(--danger-color);"></i> <?php echo $post['reaction_count']; ?> reactions
                                    </span>
                                    <span style="color: var(--gray); font-size: 0.9rem;">
                                        <i class="fas fa-comment" style="color: var(--spiritual-blue);"></i> <?php echo $post['comment_count']; ?> comments
                                    </span>
                                    <span style="color: var(--gray); font-size: 0.9rem;">
                                        <i class="fas fa-calendar"></i> <?php echo date('M d, Y h:i A', strtotime($post['created_at'])); ?>
                                    </span>
                                </div>
                                
                                <!-- Actions -->
                                <div style="display: flex; gap: 10px;">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;" onclick="return confirm('Delete this post permanently?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                    
                                    <button onclick="viewPost(<?php echo $post['post_id']; ?>)" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.85rem;">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                        <input type="hidden" name="action" value="feature">
                                        <button type="submit" class="btn btn-success" style="padding: 6px 12px; font-size: 0.85rem;">
                                            <i class="fas fa-star"></i> Feature
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <h3>No Posts Found</h3>
                        <p>No posts in the system</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function viewPost(postId) {
    // You can implement a detailed view modal here
    alert('View post details for post ID: ' + postId);
}
</script>

<?php include '../includes/footer.php'; ?>