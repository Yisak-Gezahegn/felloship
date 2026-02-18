<?php
$pageTitle = "Member Dashboard - Haramaya Fellowship";
include '../includes/header.php';

requireLogin();

// Get comprehensive member statistics
$my_posts = $conn->query("SELECT COUNT(*) as count FROM posts WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['count'];
$my_comments = $conn->query("SELECT COUNT(*) as count FROM comments WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['count'];
$my_groups = $conn->query("SELECT COUNT(*) as count FROM group_members WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['count'];
$my_reactions = $conn->query("SELECT COUNT(*) as count FROM post_reactions WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['count'];
$unread_messages = getUnreadCount($conn, $_SESSION['user_id']);
$upcoming_events_count = $conn->query("SELECT COUNT(*) as count FROM events WHERE event_date >= NOW()")->fetch_assoc()['count'];

// Get my recent comments
$recent_comments = $conn->query("SELECT c.*, p.content as post_content, p.post_id, u.full_name as post_author
                                 FROM comments c 
                                 JOIN posts p ON c.post_id = p.post_id 
                                 JOIN users u ON p.user_id = u.user_id
                                 WHERE c.user_id = {$_SESSION['user_id']} 
                                 ORDER BY c.created_at DESC LIMIT 5");

// Get my groups
$my_groups_list = $conn->query("SELECT g.*, 
                                (SELECT COUNT(*) FROM group_members WHERE group_id = g.group_id) as member_count
                                FROM groups g
                                JOIN group_members gm ON g.group_id = gm.group_id
                                WHERE gm.user_id = {$_SESSION['user_id']}
                                ORDER BY g.created_at DESC LIMIT 6");

// Get upcoming events
$upcoming_events = $conn->query("SELECT * FROM events WHERE event_date >= NOW() ORDER BY event_date LIMIT 5");

// Get user info
$user_info = $conn->query("SELECT u.*, s.section_name FROM users u 
                           LEFT JOIN sections s ON u.section_id = s.section_id 
                           WHERE u.user_id = {$_SESSION['user_id']}")->fetch_assoc();
?>

<main style="padding: 25px 0;">
    <div class="container">
        <!-- Welcome Section -->
        <div class="dashboard-welcome">
            <h2>🙏 Welcome, <?php echo $_SESSION['full_name']; ?>!</h2>
            <p>Stay connected with the fellowship community</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-layer-group"></i> <?php echo $user_info['section_name'] ?? 'No Section'; ?> | 
                <i class="fas fa-comments"></i> <?php echo $my_comments; ?> Comments | 
                <i class="fas fa-heart"></i> <?php echo $my_reactions; ?> Reactions
            </p>
        </div>

        <div class="dashboard">
            <!-- Sidebar -->
            <aside class="sidebar">
                <h3>👤 Member Panel</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                    <li><a href="../groups.php"><i class="fas fa-users"></i> My Groups</a></li>
                    <li><a href="../messages.php"><i class="fas fa-envelope"></i> Messages 
                        <?php if ($unread_messages > 0) echo "<span class='badge'>$unread_messages</span>"; ?>
                    </a></li>
                    <li><a href="../events.php"><i class="fas fa-calendar"></i> Events</a></li>
                    <li><a href="../sections.php"><i class="fas fa-layer-group"></i> Sections</a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </aside>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Statistics Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-comments"></i>
                        <h3><?php echo $my_comments; ?></h3>
                        <p>My Comments</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-heart"></i>
                        <h3><?php echo $my_reactions; ?></h3>
                        <p>Reactions Given</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $my_groups; ?></h3>
                        <p>My Groups</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-envelope"></i>
                        <h3><?php echo $unread_messages; ?></h3>
                        <p>Unread Messages</p>
                    </div>
                    
                    <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-calendar"></i>
                        <h3><?php echo $upcoming_events_count; ?></h3>
                        <p>Upcoming Events</p>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="section-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="quick-actions">
                        <a href="../home.php" class="quick-action-btn">
                            <i class="fas fa-home"></i>
                            <h4>Home Feed</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">View posts</p>
                        </a>
                        <a href="../groups.php" class="quick-action-btn">
                            <i class="fas fa-users"></i>
                            <h4>Join Groups</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">Connect more</p>
                        </a>
                        <a href="../messages.php" class="quick-action-btn">
                            <i class="fas fa-envelope"></i>
                            <h4>Messages</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">Chat with others</p>
                        </a>
                        <a href="../events.php" class="quick-action-btn">
                            <i class="fas fa-calendar"></i>
                            <h4>View Events</h4>
                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 5px;">See schedule</p>
                        </a>
                    </div>
                </div>
                
                <!-- Profile Summary -->
                <div class="card">
                    <div class="section-header">
                        <h3>My Profile Summary</h3>
                        <a href="../profile.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.9rem;">Edit Profile</a>
                    </div>
                    <div style="display: grid; grid-template-columns: auto 1fr; gap: 25px; align-items: center;">
                        <?php if (!empty($user_info['profile_picture']) && $user_info['profile_picture'] !== 'default.jpg' && file_exists("../uploads/profiles/{$user_info['profile_picture']}")): ?>
                        <img src="../uploads/profiles/<?php echo $user_info['profile_picture']; ?>" alt="Profile" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--spiritual-gold); box-shadow: var(--shadow-md);">
                        <?php else: ?>
                        <div class="profile-pic-default-large" style="width: 100px; height: 100px;">
                            <i class="fas fa-user" style="font-size: 3rem;"></i>
                        </div>
                        <?php endif; ?>
                        <div>
                            <h4 style="font-size: 1.5rem; color: var(--primary-color); margin-bottom: 10px;"><?php echo $user_info['full_name']; ?></h4>
                            <p style="margin-bottom: 8px;"><i class="fas fa-envelope" style="color: var(--spiritual-purple); width: 20px;"></i> <strong>Email:</strong> <?php echo $user_info['email']; ?></p>
                            <p style="margin-bottom: 8px;"><i class="fas fa-user-tag" style="color: var(--spiritual-purple); width: 20px;"></i> <strong>Role:</strong> <?php echo ucfirst(str_replace('_', ' ', $user_info['role'])); ?></p>
                            <?php if ($user_info['section_name']): ?>
                            <p style="margin-bottom: 8px;"><i class="fas fa-layer-group" style="color: var(--spiritual-purple); width: 20px;"></i> <strong>Section:</strong> <?php echo $user_info['section_name']; ?></p>
                            <?php endif; ?>
                            <?php if ($user_info['department']): ?>
                            <p style="margin-bottom: 8px;"><i class="fas fa-graduation-cap" style="color: var(--spiritual-purple); width: 20px;"></i> <strong>Department:</strong> <?php echo $user_info['department']; ?></p>
                            <?php endif; ?>
                            <?php if ($user_info['current_year']): ?>
                            <p style="margin-bottom: 8px;"><i class="fas fa-calendar-alt" style="color: var(--spiritual-purple); width: 20px;"></i> <strong>Year:</strong> <?php echo $user_info['current_year']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- My Recent Comments -->
                <div class="card">
                    <div class="section-header">
                        <h3>My Recent Comments</h3>
                        <a href="../home.php" class="btn btn-gold" style="padding: 8px 16px; font-size: 0.9rem;">View Feed</a>
                    </div>
                    <?php if ($recent_comments->num_rows > 0): ?>
                    <div style="display: grid; gap: 15px;">
                        <?php while ($comment = $recent_comments->fetch_assoc()): ?>
                        <div style="padding: 15px; background: #f8f9fa; border-radius: var(--border-radius); border-left: 4px solid var(--spiritual-blue);">
                            <div style="margin-bottom: 10px;">
                                <span style="font-size: 0.85rem; color: var(--gray);">
                                    Commented on <strong style="color: var(--primary-color);"><?php echo $comment['post_author']; ?>'s</strong> post
                                </span>
                                <span style="float: right; font-size: 0.85rem; color: var(--gray);"><?php echo timeAgo($comment['created_at']); ?></span>
                            </div>
                            <p style="color: var(--dark-color); margin-bottom: 8px; font-style: italic;">"<?php echo substr($comment['comment_text'], 0, 100) . (strlen($comment['comment_text']) > 100 ? '...' : ''); ?>"</p>
                            <p style="font-size: 0.85rem; color: var(--gray); padding: 10px; background: white; border-radius: 6px;">
                                <strong>Post:</strong> <?php echo substr($comment['post_content'], 0, 80) . '...'; ?>
                            </p>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>No Comments Yet</h3>
                        <p>Start engaging with posts in the home feed</p>
                        <a href="../home.php" class="btn btn-primary" style="margin-top: 15px;">Go to Home Feed</a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- My Groups -->
                <div class="card">
                    <div class="section-header">
                        <h3>My Groups</h3>
                        <a href="../groups.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.9rem;">View All</a>
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
                        <p>Join groups to connect with other members</p>
                        <a href="../groups.php" class="btn btn-primary" style="margin-top: 15px;">Browse Groups</a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Upcoming Events -->
                <div class="card">
                    <div class="section-header">
                        <h3>Upcoming Events</h3>
                        <a href="../events.php" class="btn btn-gold" style="padding: 8px 16px; font-size: 0.9rem;">View All</a>
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
                        <p>Check back later for fellowship events</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
