<?php
// Check authentication and role BEFORE including header
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('family_leader')) {
    header('Location: ../home.php');
    exit();
}

$pageTitle = "Family Leader Dashboard - Haramaya Fellowship";
include '../includes/header.php';

// Get family info
$family_query = "SELECT * FROM families WHERE mom_id = {$_SESSION['user_id']} OR dad_id = {$_SESSION['user_id']}";
$family_result = $conn->query($family_query);
$family = $family_result->fetch_assoc();

$family_members = 0;
$online_family_members = 0;
if ($family) {
    $family_members = $conn->query("SELECT COUNT(*) as count FROM users WHERE family_id = {$family['family_id']}")->fetch_assoc()['count'];
    $online_family_members = $conn->query("SELECT COUNT(*) as count FROM users WHERE family_id = {$family['family_id']} AND is_online = TRUE")->fetch_assoc()['count'];
}

// Get my statistics
$my_posts = $conn->query("SELECT COUNT(*) as count FROM posts WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['count'];
$my_groups = $conn->query("SELECT COUNT(*) as count FROM group_members WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['count'];
$unread_messages = getUnreadCount($conn, $_SESSION['user_id']);

// Get my recent posts
$my_recent_posts = $conn->query("SELECT p.*, 
                                 (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id) as reactions,
                                 (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comments
                                 FROM posts p 
                                 WHERE p.user_id = {$_SESSION['user_id']} 
                                 ORDER BY p.created_at DESC LIMIT 5");

// Get upcoming events
$upcoming_events = $conn->query("SELECT * FROM events WHERE event_date >= NOW() ORDER BY event_date LIMIT 5");
?>

<main style="padding: 25px 0;">
    <div class="container">
        <!-- Welcome Section -->
        <div class="dashboard-welcome">
            <h2>🙏 Family Leader Dashboard</h2>
            <p>Welcome, <?php echo $_SESSION['full_name']; ?>! Guide your family with love and wisdom</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <?php if ($family): ?>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-home"></i> <?php echo $family['family_name']; ?> | 
                <i class="fas fa-users"></i> <?php echo $family_members; ?> Members | 
                <i class="fas fa-globe"></i> <?php echo $online_family_members; ?> Online
            </p>
            <?php endif; ?>
        </div>

        <div class="dashboard">
            <!-- Sidebar -->
            <aside class="sidebar">
                <h3>👨‍👩‍👧‍👦 Family Panel</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                    <li><a href="my-family.php"><i class="fas fa-users"></i> My Family</a></li>
                    <li><a href="family-group.php"><i class="fas fa-comments"></i> Family Group</a></li>
                    <li><a href="create-post.php"><i class="fas fa-plus"></i> Create Post</a></li>
                    <li><a href="../messages.php"><i class="fas fa-envelope"></i> Messages
                        <?php if ($unread_messages > 0) echo "<span class='badge'>$unread_messages</span>"; ?>
                    </a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </aside>
            
            <!-- Main Content -->
            <div class="main-content">
                <?php if ($family): ?>
                <!-- Statistics Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $family_members; ?></h3>
                        <p>Family Members</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-globe"></i>
                        <h3><?php echo $online_family_members; ?></h3>
                        <p>Online Now</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-comments"></i>
                        <h3><?php echo $my_posts; ?></h3>
                        <p>My Posts</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-users-cog"></i>
                        <h3><?php echo $my_groups; ?></h3>
                        <p>My Groups</p>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="section-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="quick-actions">
                        <a href="create-post.php" class="quick-action-btn">
                            <i class="fas fa-plus-circle"></i>
                            <h4>Create Post</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">Share updates</p>
                        </a>
                        <a href="my-family.php" class="quick-action-btn">
                            <i class="fas fa-users"></i>
                            <h4>View Family</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">Manage members</p>
                        </a>
                        <a href="family-group.php" class="quick-action-btn">
                            <i class="fas fa-comments"></i>
                            <h4>Family Chat</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">Group messages</p>
                        </a>
                        <a href="../messages.php" class="quick-action-btn">
                            <i class="fas fa-envelope"></i>
                            <h4>Messages</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">Private chats</p>
                        </a>
                    </div>
                </div>
                
                <!-- Family Information -->
                <div class="card">
                    <div class="section-header">
                        <h3>Family Information</h3>
                    </div>
                    <div class="info-box">
                        <p><strong><i class="fas fa-home"></i> Family Name:</strong> <?php echo $family['family_name']; ?></p>
                        <p><strong><i class="fas fa-users"></i> Total Members:</strong> <?php echo $family_members; ?> members</p>
                        <p><strong><i class="fas fa-calendar"></i> Created:</strong> <?php echo date('M d, Y', strtotime($family['created_at'])); ?></p>
                        <p><strong><i class="fas fa-globe"></i> Online Members:</strong> <?php echo $online_family_members; ?> online</p>
                    </div>
                </div>
                
                <!-- My Recent Posts -->
                <div class="card">
                    <div class="section-header">
                        <h3>My Recent Posts</h3>
                        <a href="create-post.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.9rem;">Create New</a>
                    </div>
                    <?php if ($my_recent_posts->num_rows > 0): ?>
                    <div style="display: grid; gap: 15px;">
                        <?php while ($post = $my_recent_posts->fetch_assoc()): ?>
                        <div style="padding: 15px; background: #f8f9fa; border-radius: var(--border-radius); border-left: 4px solid var(--spiritual-purple);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span class="status-badge" style="background: var(--spiritual-blue);">
                                    <?php echo ucfirst($post['post_type']); ?>
                                </span>
                                <span style="font-size: 0.85rem; color: var(--gray);"><?php echo timeAgo($post['created_at']); ?></span>
                            </div>
                            <p style="color: var(--dark-color); margin-bottom: 10px;"><?php echo substr($post['content'], 0, 150) . (strlen($post['content']) > 150 ? '...' : ''); ?></p>
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
                        <p>Create your first post to share with the fellowship</p>
                        <a href="create-post.php" class="btn btn-primary" style="margin-top: 15px;">Create Post</a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Family Members -->
                <div class="card">
                    <div class="section-header">
                        <h3>Family Members</h3>
                        <a href="my-family.php" class="btn btn-gold" style="padding: 8px 16px; font-size: 0.9rem;">View All</a>
                    </div>
                    <?php
                    $members = $conn->query("SELECT * FROM users WHERE family_id = {$family['family_id']} ORDER BY full_name LIMIT 8");
                    if ($members->num_rows > 0):
                    ?>
                    <table class="spiritual-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($member = $members->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $member['full_name']; ?></strong></td>
                                <td><?php echo $member['email']; ?></td>
                                <td><?php echo $member['phone'] ?? 'N/A'; ?></td>
                                <td>
                                    <span style="color: <?php echo $member['is_online'] ? 'var(--success-color)' : 'var(--gray)'; ?>">
                                        <?php echo $member['is_online'] ? '● Online' : '○ Offline'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Members Yet</h3>
                        <p>Family members will appear here</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Upcoming Events -->
                <div class="card">
                    <div class="section-header">
                        <h3>Upcoming Events</h3>
                    </div>
                    <?php if ($upcoming_events->num_rows > 0): ?>
                    <div style="display: grid; gap: 12px;">
                        <?php while ($event = $upcoming_events->fetch_assoc()): ?>
                        <div style="padding: 15px; background: #f8f9fa; border-radius: var(--border-radius); border-left: 4px solid var(--spiritual-gold);">
                            <h4 style="color: var(--primary-color); margin-bottom: 8px;"><?php echo $event['title']; ?></h4>
                            <p style="font-size: 0.9rem; color: var(--gray); margin-bottom: 8px;">
                                <i class="fas fa-calendar"></i> <?php echo date('M d, Y - h:i A', strtotime($event['event_date'])); ?>
                            </p>
                            <p style="font-size: 0.9rem; color: var(--gray);">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $event['location']; ?>
                            </p>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar"></i>
                        <h3>No Upcoming Events</h3>
                        <p>Fellowship events will appear here</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php else: ?>
                <!-- No Family Assigned -->
                <div class="card">
                    <div class="warning-box">
                        <h3><i class="fas fa-exclamation-triangle"></i> No Family Assigned</h3>
                        <p>You don't have a family group yet. Contact the coordinator or fellowship leader to be assigned to a family.</p>
                        <a href="../messages.php" class="btn btn-primary" style="margin-top: 15px;">
                            <i class="fas fa-envelope"></i> Contact Coordinator
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
