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

$pageTitle = "Developer Dashboard - Haramaya Fellowship";
include '../includes/header.php';

// Get comprehensive statistics from database
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$approved_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'approved'")->fetch_assoc()['count'];
$pending_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'pending'")->fetch_assoc()['count'];
$suspended_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'suspended'")->fetch_assoc()['count'];
$total_posts = $conn->query("SELECT COUNT(*) as count FROM posts")->fetch_assoc()['count'];
$total_sections = $conn->query("SELECT COUNT(*) as count FROM sections")->fetch_assoc()['count'];
$total_groups = $conn->query("SELECT COUNT(*) as count FROM groups")->fetch_assoc()['count'];
$total_events = $conn->query("SELECT COUNT(*) as count FROM events")->fetch_assoc()['count'];
$online_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_online = TRUE")->fetch_assoc()['count'];
$total_comments = $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];
$total_reactions = $conn->query("SELECT COUNT(*) as count FROM post_reactions")->fetch_assoc()['count'];

// Get role distribution
$role_stats = $conn->query("SELECT role, COUNT(*) as count FROM users WHERE status = 'approved' GROUP BY role");
$roles_data = [];
while ($row = $role_stats->fetch_assoc()) {
    $roles_data[$row['role']] = $row['count'];
}

// Get recent activities
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
$recent_posts = $conn->query("SELECT p.*, u.full_name FROM posts p JOIN users u ON p.user_id = u.user_id ORDER BY p.created_at DESC LIMIT 5");
?>

<main style="padding: 25px 0;">
    <div class="container">
        <!-- Welcome Section -->
        <div class="dashboard-welcome">
            <h2>🙏 Developer Dashboard</h2>
            <p>Welcome back! Here's your fellowship system overview</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-clock"></i> Last login: <?php echo date('F d, Y - h:i A'); ?>
            </p>
        </div>

        <div class="dashboard">
            <!-- Sidebar -->
            <aside class="sidebar">
                <h3>⚙️ System Control</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="../groups.php"><i class="fas fa-users-cog"></i> Groups</a></li>
                    <li><a href="../events.php"><i class="fas fa-calendar"></i> Events</a></li>
                    <li><a href="../sections.php"><i class="fas fa-layer-group"></i> Sections</a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                </ul>
                
                <h3 style="margin-top: 30px;">👥 User Management</h3>
                <ul class="sidebar-menu">
                    <li><a href="manage-all-users.php"><i class="fas fa-users"></i> All Users</a></li>
                    <li><a href="approve-users.php"><i class="fas fa-user-check"></i> Approve Users</a></li>
                    <li><a href="user-roles.php"><i class="fas fa-user-tag"></i> Manage Roles</a></li>
                    <li><a href="suspended-users.php"><i class="fas fa-user-slash"></i> Suspended Users</a></li>
                </ul>
                
                <h3 style="margin-top: 30px;">📊 Content Management</h3>
                <ul class="sidebar-menu">
                    <li><a href="manage-posts.php"><i class="fas fa-file-alt"></i> All Posts</a></li>
                    <li><a href="manage-comments.php"><i class="fas fa-comments"></i> All Comments</a></li>
                    <li><a href="manage-reactions.php"><i class="fas fa-heart"></i> Reactions</a></li>
                    <li><a href="reported-content.php"><i class="fas fa-flag"></i> Reported Content</a></li>
                </ul>
                
                <h3 style="margin-top: 30px;">🏢 Organization</h3>
                <ul class="sidebar-menu">
                    <li><a href="manage-sections.php"><i class="fas fa-layer-group"></i> Manage Sections</a></li>
                    <li><a href="manage-groups.php"><i class="fas fa-users"></i> Manage Groups</a></li>
                    <li><a href="section-reports.php"><i class="fas fa-file-alt"></i> Section Reports</a></li>
                    <li><a href="financial-reports.php"><i class="fas fa-dollar-sign"></i> Financial Reports</a></li>
                </ul>
                
                <h3 style="margin-top: 30px;">🔧 System Admin</h3>
                <ul class="sidebar-menu">
                    <li><a href="system-settings.php"><i class="fas fa-cogs"></i> System Settings</a></li>
                    <li><a href="database-backup.php"><i class="fas fa-database"></i> Database Backup</a></li>
                    <li><a href="system-logs.php"><i class="fas fa-file-text"></i> System Logs</a></li>
                    <li><a href="maintenance.php"><i class="fas fa-tools"></i> Maintenance</a></li>
                </ul>
            </aside>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Statistics Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-user-check"></i>
                        <h3><?php echo $approved_users; ?></h3>
                        <p>Approved Users</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-user-clock"></i>
                        <h3><?php echo $pending_users; ?></h3>
                        <p>Pending Approval</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-globe"></i>
                        <h3><?php echo $online_users; ?></h3>
                        <p>Online Now</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-file-alt"></i>
                        <h3><?php echo $total_posts; ?></h3>
                        <p>Total Posts</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                        <i class="fas fa-comments"></i>
                        <h3><?php echo $total_comments; ?></h3>
                        <p>Comments</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333;">
                        <i class="fas fa-heart"></i>
                        <h3><?php echo $total_reactions; ?></h3>
                        <p>Reactions</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: #333;">
                        <i class="fas fa-calendar"></i>
                        <h3><?php echo $total_events; ?></h3>
                        <p>Events</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #333;">
                        <i class="fas fa-layer-group"></i>
                        <h3><?php echo $total_sections; ?></h3>
                        <p>Sections</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%); color: #333;">
                        <i class="fas fa-users-cog"></i>
                        <h3><?php echo $total_groups; ?></h3>
                        <p>Groups</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #fad0c4 0%, #ffd1ff 100%); color: #333;">
                        <i class="fas fa-user-slash"></i>
                        <h3><?php echo $suspended_users; ?></h3>
                        <p>Suspended</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #d299c2 0%, #fef9d7 100%); color: #333;">
                        <i class="fas fa-server"></i>
                        <h3>Active</h3>
                        <p>System Status</p>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card" style="margin-bottom: 25px;">
                    <div class="section-header">
                        <h3>⚡ Quick Actions</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; padding: 20px;">
                        <a href="manage-all-users.php" class="quick-action-btn" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; padding: 20px; border-radius: var(--border-radius); text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                            <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            <strong>Manage Users</strong>
                            <p style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.9;">View, edit, approve users</p>
                        </a>
                        
                        <a href="manage-posts.php" class="quick-action-btn" style="background: linear-gradient(135deg, #fa709a, #fee140); color: white; text-decoration: none; padding: 20px; border-radius: var(--border-radius); text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                            <i class="fas fa-file-alt" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            <strong>Manage Posts</strong>
                            <p style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.9;">Moderate all content</p>
                        </a>
                        
                        <a href="system-settings.php" class="quick-action-btn" style="background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; text-decoration: none; padding: 20px; border-radius: var(--border-radius); text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                            <i class="fas fa-cogs" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            <strong>System Settings</strong>
                            <p style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.9;">Configure system</p>
                        </a>
                        
                        <a href="database-backup.php" class="quick-action-btn" style="background: linear-gradient(135deg, #43e97b, #38f9d7); color: white; text-decoration: none; padding: 20px; border-radius: var(--border-radius); text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                            <i class="fas fa-database" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            <strong>Database Backup</strong>
                            <p style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.9;">Backup & restore</p>
                        </a>
                        
                        <a href="section-reports.php" class="quick-action-btn" style="background: linear-gradient(135deg, #a8edea, #fed6e3); color: #333; text-decoration: none; padding: 20px; border-radius: var(--border-radius); text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                            <i class="fas fa-file-alt" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            <strong>Section Reports</strong>
                            <p style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.8;">Review reports</p>
                        </a>
                        
                        <a href="system-logs.php" class="quick-action-btn" style="background: linear-gradient(135deg, #ff9a9e, #fecfef); color: #333; text-decoration: none; padding: 20px; border-radius: var(--border-radius); text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                            <i class="fas fa-file-text" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            <strong>System Logs</strong>
                            <p style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.8;">View activity logs</p>
                        </a>
                    </div>
                </div>
                
                <!-- Role Distribution -->
                <div class="card">
                    <div class="section-header">
                        <h3>User Role Distribution</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                        <?php
                        $role_colors = [
                            'developer' => '#667eea',
                            'coordinator' => '#f093fb',
                            'fellowship_leader' => '#4facfe',
                            'section_leader' => '#43e97b',
                            'family_leader' => '#fa709a',
                            'member' => '#30cfd0'
                        ];
                        foreach ($roles_data as $role => $count):
                            $color = $role_colors[$role] ?? '#6c757d';
                        ?>
                        <div style="background: <?php echo $color; ?>; color: white; padding: 20px; border-radius: var(--border-radius); text-align: center; box-shadow: var(--shadow-sm);">
                            <h4 style="font-size: 1.8rem; margin-bottom: 5px;"><?php echo $count; ?></h4>
                            <p style="font-size: 0.9rem; opacity: 0.95;"><?php echo ucfirst(str_replace('_', ' ', $role)); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Recent User Registrations -->
                <div class="card">
                    <div class="section-header">
                        <h3>Recent User Registrations</h3>
                    </div>
                    <?php if ($recent_users->num_rows > 0): ?>
                    <table class="spiritual-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $recent_users->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Users Yet</h3>
                        <p>User registrations will appear here</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Posts Activity -->
                <div class="card">
                    <div class="section-header">
                        <h3>Recent Posts</h3>
                    </div>
                    <?php if ($recent_posts->num_rows > 0): ?>
                    <div style="display: grid; gap: 15px;">
                        <?php while ($post = $recent_posts->fetch_assoc()): ?>
                        <div style="padding: 15px; background: #f8f9fa; border-radius: var(--border-radius); border-left: 4px solid var(--spiritual-purple);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                <strong style="color: var(--primary-color);"><?php echo htmlspecialchars($post['full_name']); ?></strong>
                                <span style="font-size: 0.85rem; color: var(--gray);"><?php echo timeAgo($post['created_at']); ?></span>
                            </div>
                            <p style="color: var(--dark-color); margin-bottom: 10px;"><?php echo htmlspecialchars(substr($post['content'], 0, 150)) . (strlen($post['content']) > 150 ? '...' : ''); ?></p>
                            <div style="display: flex; gap: 15px; font-size: 0.9rem; color: var(--gray);">
                                <span><i class="fas fa-heart"></i> <?php 
                                    $reactions = $conn->query("SELECT COUNT(*) as count FROM post_reactions WHERE post_id = {$post['post_id']}")->fetch_assoc()['count'];
                                    echo $reactions;
                                ?></span>
                                <span><i class="fas fa-comment"></i> <?php 
                                    $comments = $conn->query("SELECT COUNT(*) as count FROM comments WHERE post_id = {$post['post_id']}")->fetch_assoc()['count'];
                                    echo $comments;
                                ?></span>
                                <span class="status-badge" style="padding: 4px 10px; font-size: 0.75rem;">
                                    <?php echo ucfirst($post['post_type']); ?>
                                </span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <h3>No Posts Yet</h3>
                        <p>Posts will appear here</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- System Information -->
                <div class="card">
                    <div class="section-header">
                        <h3>System Information</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div class="info-box">
                            <i class="fas fa-server"></i>
                            <strong>PHP Version:</strong> <?php echo phpversion(); ?>
                        </div>
                        <div class="info-box">
                            <i class="fas fa-database"></i>
                            <strong>MySQL Version:</strong> <?php echo $conn->server_info; ?>
                        </div>
                        <div class="info-box">
                            <i class="fas fa-hdd"></i>
                            <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                        </div>
                        <div class="info-box">
                            <i class="fas fa-table"></i>
                            <strong>Database:</strong> Connected
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
