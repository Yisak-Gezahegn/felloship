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

$pageTitle = "Fellowship Leader Dashboard - Haramaya Fellowship";
include '../includes/header.php';

// Get comprehensive statistics
$total_members = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'approved'")->fetch_assoc()['count'];
$my_posts = $conn->query("SELECT COUNT(*) as count FROM posts WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['count'];
$my_groups = $conn->query("SELECT COUNT(*) as count FROM group_members WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['count'];
$total_events = $conn->query("SELECT COUNT(*) as count FROM events WHERE event_date >= NOW()")->fetch_assoc()['count'];
$online_members = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_online = TRUE")->fetch_assoc()['count'];
$total_sections = $conn->query("SELECT COUNT(*) as count FROM sections")->fetch_assoc()['count'];
$unread_messages = getUnreadCount($conn, $_SESSION['user_id']);

// Get my recent posts
$my_recent_posts = $conn->query("SELECT p.*, 
                                 (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id) as reactions,
                                 (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comments
                                 FROM posts p 
                                 WHERE p.user_id = {$_SESSION['user_id']} 
                                 ORDER BY p.created_at DESC LIMIT 5");

// Get my groups
$my_groups_list = $conn->query("SELECT g.*, 
                                (SELECT COUNT(*) FROM group_members WHERE group_id = g.group_id) as member_count
                                FROM groups g
                                JOIN group_members gm ON g.group_id = gm.group_id
                                WHERE gm.user_id = {$_SESSION['user_id']}
                                ORDER BY g.created_at DESC LIMIT 5");

// Get upcoming events
$upcoming_events = $conn->query("SELECT * FROM events WHERE event_date >= NOW() ORDER BY event_date LIMIT 5");
?>

<main style="padding: 25px 0;">
    <div class="container">
        <!-- Welcome Section -->
        <div class="dashboard-welcome">
            <h2>🙏 Fellowship Leader Dashboard</h2>
            <p>Welcome, <?php echo $_SESSION['full_name']; ?>! Lead and inspire the fellowship community</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-users"></i> <?php echo $total_members; ?> Members | 
                <i class="fas fa-globe"></i> <?php echo $online_members; ?> Online
            </p>
        </div>

        <div class="dashboard">
            <!-- Sidebar -->
            <aside class="sidebar">
                <h3>📊 Leader Panel</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                    <li><a href="create-post.php"><i class="fas fa-plus"></i> Create Post</a></li>
                    <li><a href="my-groups.php"><i class="fas fa-users"></i> My Groups</a></li>
                    <li><a href="create-group.php"><i class="fas fa-plus-circle"></i> Create Group</a></li>
                    <li><a href="../messages.php"><i class="fas fa-envelope"></i> Messages
                        <?php if ($unread_messages > 0) echo "<span class='badge'>$unread_messages</span>"; ?>
                    </a></li>
                    <li><a href="financial-reports.php"><i class="fas fa-dollar-sign"></i> Financial Reports</a></li>
                    <li><a href="manage-users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </aside>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Statistics Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $total_members; ?></h3>
                        <p>Total Members</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-file-alt"></i>
                        <h3><?php echo $my_posts; ?></h3>
                        <p>My Posts</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-users-cog"></i>
                        <h3><?php echo $my_groups; ?></h3>
                        <p>My Groups</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-calendar"></i>
                        <h3><?php echo $total_events; ?></h3>
                        <p>Upcoming Events</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-globe"></i>
                        <h3><?php echo $online_members; ?></h3>
                        <p>Online Now</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                        <i class="fas fa-layer-group"></i>
                        <h3><?php echo $total_sections; ?></h3>
                        <p>Active Sections</p>
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
                        <a href="create-group.php" class="quick-action-btn">
                            <i class="fas fa-users"></i>
                            <h4>Create Group</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">Start new group</p>
                        </a>
                        <a href="financial-reports.php" class="quick-action-btn">
                            <i class="fas fa-chart-line"></i>
                            <h4>View Reports</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">Financial data</p>
                        </a>
                        <a href="../events.php" class="quick-action-btn">
                            <i class="fas fa-calendar-plus"></i>
                            <h4>Create Event</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">Schedule event</p>
                        </a>
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
                
                <!-- My Groups -->
                <div class="card">
                    <div class="section-header">
                        <h3>My Groups</h3>
                        <a href="my-groups.php" class="btn btn-gold" style="padding: 8px 16px; font-size: 0.9rem;">View All</a>
                    </div>
                    <?php if ($my_groups_list->num_rows > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        <?php while ($group = $my_groups_list->fetch_assoc()): ?>
                        <div style="padding: 20px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: var(--border-radius); border-top: 3px solid var(--spiritual-gold);">
                            <h4 style="color: var(--primary-color); margin-bottom: 8px;"><?php echo $group['group_name']; ?></h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-bottom: 10px;"><?php echo substr($group['description'], 0, 80) . '...'; ?></p>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.85rem; color: var(--gray);">
                                    <i class="fas fa-users"></i> <?php echo $group['member_count']; ?> members
                                </span>
                                <span class="status-badge" style="font-size: 0.75rem;">
                                    <?php echo ucfirst($group['group_type']); ?>
                                </span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Groups Yet</h3>
                        <p>Create or join groups to connect with members</p>
                        <a href="create-group.php" class="btn btn-primary" style="margin-top: 15px;">Create Group</a>
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
                        <p>Schedule events for the fellowship</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
