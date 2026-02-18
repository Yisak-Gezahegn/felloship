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

$pageTitle = "Coordinator Dashboard - Haramaya Fellowship";
include '../includes/header.php';

// Get comprehensive statistics
$total_members = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'approved'")->fetch_assoc()['count'];
$pending_approvals = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'pending' AND role IN ('fellowship_leader', 'section_leader')")->fetch_assoc()['count'];
$total_sections = $conn->query("SELECT COUNT(*) as count FROM sections")->fetch_assoc()['count'];
$total_reports = $conn->query("SELECT COUNT(*) as count FROM section_reports")->fetch_assoc()['count'];
$pending_reports = $conn->query("SELECT COUNT(*) as count FROM section_reports WHERE status = 'pending'")->fetch_assoc()['count'];
$total_events = $conn->query("SELECT COUNT(*) as count FROM events WHERE event_date >= NOW()")->fetch_assoc()['count'];
$online_members = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_online = TRUE")->fetch_assoc()['count'];
$total_groups = $conn->query("SELECT COUNT(*) as count FROM groups")->fetch_assoc()['count'];

// Get section statistics
$section_stats = $conn->query("SELECT s.section_name, s.section_code, COUNT(u.user_id) as member_count 
                               FROM sections s 
                               LEFT JOIN users u ON s.section_id = u.section_id AND u.status = 'approved'
                               GROUP BY s.section_id 
                               ORDER BY member_count DESC");

// Get recent activities
$pending_leaders = $conn->query("SELECT * FROM users WHERE status = 'pending' AND role IN ('fellowship_leader', 'section_leader') ORDER BY created_at DESC LIMIT 5");
$recent_reports = $conn->query("SELECT sr.*, s.section_name, u.full_name 
                                FROM section_reports sr 
                                JOIN sections s ON sr.section_id = s.section_id 
                                JOIN users u ON sr.submitted_by = u.user_id 
                                ORDER BY sr.created_at DESC LIMIT 5");
?>

<main style="padding: 25px 0;">
    <div class="container">
        <!-- Welcome Section -->
        <div class="dashboard-welcome">
            <h2>🙏 Coordinator Dashboard</h2>
            <p>Welcome, <?php echo $_SESSION['full_name']; ?>! Oversee and guide the fellowship community</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-users"></i> <?php echo $total_members; ?> Active Members | 
                <i class="fas fa-globe"></i> <?php echo $online_members; ?> Online Now
            </p>
        </div>

        <div class="dashboard">
            <!-- Sidebar -->
            <aside class="sidebar">
                <h3>📋 Coordinator Panel</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="my-group.php"><i class="fas fa-users"></i> Leaders Group</a></li>
                    <li><a href="../messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="manage-users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="approve-leaders.php"><i class="fas fa-user-check"></i> Approve Leaders 
                        <?php if ($pending_approvals > 0) echo "<span class='badge'>$pending_approvals</span>"; ?>
                    </a></li>
                    <li><a href="section-reports.php"><i class="fas fa-file-alt"></i> Section Reports 
                        <?php if ($pending_reports > 0) echo "<span class='badge'>$pending_reports</span>"; ?>
                    </a></li>
                    <li><a href="create-post.php"><i class="fas fa-plus"></i> Create Post/Event</a></li>
                    <li><a href="search-members.php"><i class="fas fa-search"></i> Search Members</a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                </ul>
            </aside>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Statistics Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $total_members; ?></h3>
                        <p>Total Members</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-user-clock"></i>
                        <h3><?php echo $pending_approvals; ?></h3>
                        <p>Pending Leaders</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-layer-group"></i>
                        <h3><?php echo $total_sections; ?></h3>
                        <p>Active Sections</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-globe"></i>
                        <h3><?php echo $online_members; ?></h3>
                        <p>Online Now</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-file-alt"></i>
                        <h3><?php echo $total_reports; ?></h3>
                        <p>Total Reports</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                        <i class="fas fa-clock"></i>
                        <h3><?php echo $pending_reports; ?></h3>
                        <p>Pending Reports</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333;">
                        <i class="fas fa-calendar"></i>
                        <h3><?php echo $total_events; ?></h3>
                        <p>Upcoming Events</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: #333;">
                        <i class="fas fa-users-cog"></i>
                        <h3><?php echo $total_groups; ?></h3>
                        <p>Active Groups</p>
                    </div>
                </div>
                
                <!-- Section Distribution -->
                <div class="card">
                    <div class="section-header">
                        <h3>Section Member Distribution</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <?php
                        $colors = ['#667eea', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#30cfd0', '#a8edea', '#ff9a9e', '#fbc2eb', '#a6c1ee'];
                        $i = 0;
                        while ($section = $section_stats->fetch_assoc()):
                            $color = $colors[$i % count($colors)];
                            $i++;
                        ?>
                        <div style="background: <?php echo $color; ?>; color: white; padding: 25px; border-radius: var(--border-radius); text-align: center; box-shadow: var(--shadow-md); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                            <h4 style="font-size: 2rem; margin-bottom: 8px;"><?php echo $section['member_count']; ?></h4>
                            <p style="font-size: 0.95rem; opacity: 0.95; font-weight: 500;"><?php echo $section['section_name']; ?></p>
                            <span style="font-size: 0.8rem; opacity: 0.85;"><?php echo $section['section_code']; ?></span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <!-- Pending Leader Approvals -->
                <?php if ($pending_approvals > 0): ?>
                <div class="card">
                    <div class="section-header">
                        <h3>Pending Leader Approvals</h3>
                        <a href="approve-leaders.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.9rem;">View All</a>
                    </div>
                    <table class="spiritual-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Requested Role</th>
                                <th>Department</th>
                                <th>Registered</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($leader = $pending_leaders->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $leader['full_name']; ?></strong></td>
                                <td><?php echo $leader['email']; ?></td>
                                <td>
                                    <span class="status-badge" style="background: var(--spiritual-purple);">
                                        <?php echo ucfirst(str_replace('_', ' ', $leader['role'])); ?>
                                    </span>
                                </td>
                                <td><?php echo $leader['department'] ?? 'N/A'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($leader['created_at'])); ?></td>
                                <td>
                                    <a href="approve-leaders.php?id=<?php echo $leader['user_id']; ?>" class="btn btn-success" style="padding: 6px 12px; font-size: 0.85rem;">
                                        <i class="fas fa-check"></i> Review
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <!-- Recent Section Reports -->
                <div class="card">
                    <div class="section-header">
                        <h3>Recent Section Reports</h3>
                        <a href="section-reports.php" class="btn btn-gold" style="padding: 8px 16px; font-size: 0.9rem;">View All</a>
                    </div>
                    <?php if ($recent_reports->num_rows > 0): ?>
                    <table class="spiritual-table">
                        <thead>
                            <tr>
                                <th>Section</th>
                                <th>Title</th>
                                <th>Submitted By</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($report = $recent_reports->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $report['section_name']; ?></strong></td>
                                <td><?php echo $report['report_title']; ?></td>
                                <td><?php echo $report['full_name']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($report['report_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $report['status']; ?>">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="section-reports.php?id=<?php echo $report['report_id']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.85rem;">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <h3>No Reports Yet</h3>
                        <p>Section reports will appear here</p>
                    </div>
                    <?php endif; ?>
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
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">Share updates with fellowship</p>
                        </a>
                        <a href="approve-leaders.php" class="quick-action-btn">
                            <i class="fas fa-user-check"></i>
                            <h4>Approve Leaders</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;"><?php echo $pending_approvals; ?> pending approvals</p>
                        </a>
                        <a href="search-members.php" class="quick-action-btn">
                            <i class="fas fa-search"></i>
                            <h4>Search Members</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">Find members by section</p>
                        </a>
                        <a href="section-reports.php" class="quick-action-btn">
                            <i class="fas fa-file-alt"></i>
                            <h4>View Reports</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;"><?php echo $pending_reports; ?> need review</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
