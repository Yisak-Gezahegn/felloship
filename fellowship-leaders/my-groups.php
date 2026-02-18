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

$pageTitle = "My Groups - Haramaya Fellowship";
include '../includes/header.php';

// Get my groups with detailed information
$my_groups = $conn->query("SELECT g.*, 
                           (SELECT COUNT(*) FROM group_members WHERE group_id = g.group_id) as member_count,
                           (SELECT COUNT(*) FROM group_messages WHERE group_id = g.group_id) as message_count,
                           u.full_name as creator_name
                           FROM groups g
                           JOIN group_members gm ON g.group_id = gm.group_id
                           LEFT JOIN users u ON g.created_by = u.user_id
                           WHERE gm.user_id = {$_SESSION['user_id']}
                           ORDER BY g.created_at DESC");

$total_groups = $my_groups->num_rows;
?>

<main style="padding: 25px 0;">
    <div class="container">
        <!-- Page Header -->
        <div class="dashboard-welcome">
            <h2>👥 My Groups</h2>
            <p>Manage and participate in your fellowship groups</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-users"></i> <?php echo $total_groups; ?> Groups
            </p>
        </div>

        <div class="dashboard">
            <!-- Sidebar -->
            <aside class="sidebar">
                <h3>📊 Leader Panel</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                    <li><a href="create-post.php"><i class="fas fa-plus"></i> Create Post</a></li>
                    <li><a href="my-groups.php" class="active"><i class="fas fa-users"></i> My Groups</a></li>
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
                <!-- Action Buttons -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="color: var(--primary-color); margin: 0;">All My Groups</h3>
                    <a href="create-group.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Create New Group
                    </a>
                </div>

                <?php if ($total_groups > 0): ?>
                <!-- Groups Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
                    <?php while ($group = $my_groups->fetch_assoc()): ?>
                    <div class="card" style="transition: all 0.3s ease;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                            <div>
                                <h4 style="color: var(--primary-color); margin-bottom: 8px; font-size: 1.2rem;">
                                    <?php echo htmlspecialchars($group['group_name']); ?>
                                </h4>
                                <span class="status-badge" style="background: var(--spiritual-blue); font-size: 0.75rem;">
                                    <?php echo ucfirst($group['group_type']); ?>
                                </span>
                            </div>
                            <?php if ($group['created_by'] == $_SESSION['user_id']): ?>
                            <span style="background: var(--spiritual-gold); color: var(--dark-color); padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                <i class="fas fa-crown"></i> Owner
                            </span>
                            <?php endif; ?>
                        </div>

                        <p style="color: var(--gray); font-size: 0.95rem; margin-bottom: 15px; line-height: 1.6;">
                            <?php echo htmlspecialchars(substr($group['description'], 0, 120)) . (strlen($group['description']) > 120 ? '...' : ''); ?>
                        </p>

                        <div style="display: flex; gap: 20px; margin-bottom: 15px; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                            <div style="text-align: center;">
                                <i class="fas fa-users" style="color: var(--spiritual-purple); font-size: 1.2rem;"></i>
                                <p style="margin: 5px 0 0 0; font-weight: 600; color: var(--dark-color);"><?php echo $group['member_count']; ?></p>
                                <p style="margin: 0; font-size: 0.75rem; color: var(--gray);">Members</p>
                            </div>
                            <div style="text-align: center;">
                                <i class="fas fa-comments" style="color: var(--spiritual-blue); font-size: 1.2rem;"></i>
                                <p style="margin: 5px 0 0 0; font-weight: 600; color: var(--dark-color);"><?php echo $group['message_count']; ?></p>
                                <p style="margin: 0; font-size: 0.75rem; color: var(--gray);">Messages</p>
                            </div>
                            <div style="text-align: center;">
                                <i class="fas fa-calendar" style="color: var(--spiritual-gold); font-size: 1.2rem;"></i>
                                <p style="margin: 5px 0 0 0; font-weight: 600; color: var(--dark-color); font-size: 0.85rem;">
                                    <?php echo date('M Y', strtotime($group['created_at'])); ?>
                                </p>
                                <p style="margin: 0; font-size: 0.75rem; color: var(--gray);">Created</p>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <a href="../group-chat.php?id=<?php echo $group['group_id']; ?>" class="btn btn-primary" style="flex: 1; padding: 10px; font-size: 0.9rem; text-align: center;">
                                <i class="fas fa-comments"></i> Open Chat
                            </a>
                            <?php if ($group['created_by'] == $_SESSION['user_id']): ?>
                            <a href="edit-group.php?id=<?php echo $group['group_id']; ?>" class="btn btn-gold" style="padding: 10px 15px; font-size: 0.9rem;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e9ecef;">
                            <p style="font-size: 0.8rem; color: var(--gray); margin: 0;">
                                <i class="fas fa-user"></i> Created by <strong><?php echo htmlspecialchars($group['creator_name']); ?></strong>
                            </p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <?php else: ?>
                <!-- Empty State -->
                <div class="card">
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Groups Yet</h3>
                        <p>You haven't joined any groups yet. Create your first group to start connecting with members!</p>
                        <a href="create-group.php" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus-circle"></i> Create Your First Group
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
