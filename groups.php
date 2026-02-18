<?php
$pageTitle = "Groups - Haramaya Fellowship";
include 'includes/header.php';

requireLogin();

// Get user's groups with creator info and recent activity
$user_groups = $conn->query("SELECT g.*, 
                             COUNT(DISTINCT gm.user_id) as member_count,
                             u.full_name as creator_name,
                             (SELECT COUNT(*) FROM group_messages WHERE group_id = g.group_id) as message_count,
                             (SELECT created_at FROM group_messages WHERE group_id = g.group_id ORDER BY created_at DESC LIMIT 1) as last_activity
                             FROM groups g 
                             LEFT JOIN group_members gm ON g.group_id = gm.group_id 
                             LEFT JOIN users u ON g.created_by = u.user_id
                             WHERE g.group_id IN (
                                 SELECT group_id FROM group_members WHERE user_id = {$_SESSION['user_id']}
                             ) 
                             GROUP BY g.group_id 
                             ORDER BY last_activity DESC, g.created_at DESC");

// Get available groups to join with creator info
$available_groups = $conn->query("SELECT g.*, 
                                  COUNT(DISTINCT gm.user_id) as member_count,
                                  u.full_name as creator_name,
                                  (SELECT COUNT(*) FROM group_messages WHERE group_id = g.group_id) as message_count
                                  FROM groups g 
                                  LEFT JOIN group_members gm ON g.group_id = gm.group_id 
                                  LEFT JOIN users u ON g.created_by = u.user_id
                                  WHERE g.group_type = 'public' 
                                  AND g.group_id NOT IN (
                                      SELECT group_id FROM group_members WHERE user_id = {$_SESSION['user_id']}
                                  ) 
                                  GROUP BY g.group_id 
                                  ORDER BY member_count DESC, g.created_at DESC");

// Get total statistics
$total_groups = $conn->query("SELECT COUNT(*) as count FROM groups")->fetch_assoc()['count'];
$total_members_in_groups = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM group_members")->fetch_assoc()['count'];
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>👥 Groups</h2>
            <p>Connect with fellowship members through groups</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" style="margin: 20px 0;">
            <i class="fas fa-check-circle"></i>
            <?php 
            switch($_GET['success']) {
                case 'joined': echo 'Successfully joined the group!'; break;
                case 'left': echo 'Successfully left the group!'; break;
                default: echo 'Action completed successfully!'; break;
            }
            ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger" style="margin: 20px 0;">
            <i class="fas fa-exclamation-circle"></i>
            <?php 
            switch($_GET['error']) {
                case 'invalid_group': echo 'Invalid group selected!'; break;
                case 'group_not_found': echo 'Group not found or not available!'; break;
                case 'already_member': echo 'You are already a member of this group!'; break;
                case 'not_member': echo 'You are not a member of this group!'; break;
                case 'creator_cannot_leave': echo 'Group creators cannot leave their own groups!'; break;
                case 'join_failed': echo 'Failed to join group. Please try again.'; break;
                case 'leave_failed': echo 'Failed to leave group. Please try again.'; break;
                default: echo 'An error occurred!'; break;
            }
            ?>
        </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 25px 0;">
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f);">
                <i class="fas fa-users"></i>
                <h3><?php echo $total_groups; ?></h3>
                <p>Total Groups</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-blue), #667eea);">
                <i class="fas fa-user-friends"></i>
                <h3><?php echo $total_members_in_groups; ?></h3>
                <p>Active Members</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-purple), #764ba2);">
                <i class="fas fa-user-check"></i>
                <h3><?php echo $user_groups->num_rows; ?></h3>
                <p>My Groups</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                <i class="fas fa-plus-circle"></i>
                <h3><?php echo $available_groups->num_rows; ?></h3>
                <p>Available to Join</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 25px;">
            <!-- My Groups -->
            <div class="card">
                <div class="section-header">
                    <h3>My Groups (<?php echo $user_groups->num_rows; ?>)</h3>
                </div>

                <?php if ($user_groups->num_rows > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php while ($group = $user_groups->fetch_assoc()): ?>
                    <div style="padding: 20px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: var(--border-radius); border-left: 4px solid var(--spiritual-gold); transition: all 0.3s ease;" onmouseover="this.style.transform='translateX(5px)'; this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                            <div style="flex: 1;">
                                <h4 style="color: var(--primary-color); margin-bottom: 5px; font-size: 1.1rem;">
                                    <?php echo htmlspecialchars($group['group_name']); ?>
                                </h4>
                                <span style="display: inline-block; padding: 3px 10px; background: <?php echo $group['group_type'] == 'public' ? 'var(--success-color)' : 'var(--spiritual-purple)'; ?>; color: white; border-radius: 12px; font-size: 0.75rem; margin-bottom: 8px;">
                                    <i class="fas fa-<?php echo $group['group_type'] == 'public' ? 'globe' : 'lock'; ?>"></i>
                                    <?php echo ucfirst($group['group_type']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 12px; line-height: 1.5;">
                            <?php echo htmlspecialchars($group['description']); ?>
                        </p>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 12px; padding: 10px; background: white; border-radius: 8px;">
                            <div style="text-align: center;">
                                <i class="fas fa-users" style="color: var(--spiritual-blue);"></i>
                                <strong style="display: block; font-size: 1.2rem; color: var(--primary-color);"><?php echo $group['member_count']; ?></strong>
                                <span style="font-size: 0.75rem; color: var(--gray);">Members</span>
                            </div>
                            <div style="text-align: center;">
                                <i class="fas fa-comments" style="color: var(--spiritual-gold);"></i>
                                <strong style="display: block; font-size: 1.2rem; color: var(--primary-color);"><?php echo $group['message_count']; ?></strong>
                                <span style="font-size: 0.75rem; color: var(--gray);">Messages</span>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 10px; border-top: 1px solid #dee2e6;">
                            <div style="font-size: 0.8rem; color: var(--gray);">
                                <i class="fas fa-user-circle"></i> Created by <?php echo htmlspecialchars($group['creator_name']); ?>
                                <?php if ($group['last_activity']): ?>
                                <br><i class="fas fa-clock"></i> Last activity: <?php echo date('M d, Y', strtotime($group['last_activity'])); ?>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <a href="group-chat.php?id=<?php echo $group['group_id']; ?>" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.85rem;">
                                    <i class="fas fa-comments"></i> Open
                                </a>
                                <?php if ($group['created_by'] != $_SESSION['user_id']): ?>
                                <form method="POST" action="api/leave-group.php" style="margin: 0;">
                                    <input type="hidden" name="group_id" value="<?php echo $group['group_id']; ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 8px 16px; font-size: 0.85rem;" onclick="return confirm('Are you sure you want to leave this group?')">
                                        <i class="fas fa-sign-out-alt"></i> Leave
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="status-badge" style="background: var(--spiritual-gold); color: var(--dark-color); padding: 8px 16px; font-size: 0.85rem;">
                                    <i class="fas fa-crown"></i> Creator
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No Groups Yet</h3>
                    <p>Join a group to start connecting with other members</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Available Groups -->
            <div class="card">
                <div class="section-header">
                    <h3>Available Groups (<?php echo $available_groups->num_rows; ?>)</h3>
                </div>

                <?php if ($available_groups->num_rows > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php while ($group = $available_groups->fetch_assoc()): ?>
                    <div style="padding: 20px; background: linear-gradient(135deg, #fff, #f8f9fa); border-radius: var(--border-radius); border-left: 4px solid var(--spiritual-blue); transition: all 0.3s ease; border: 1px solid #e9ecef;" onmouseover="this.style.transform='translateX(5px)'; this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='none'">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                            <div style="flex: 1;">
                                <h4 style="color: var(--primary-color); margin-bottom: 5px; font-size: 1.1rem;">
                                    <?php echo htmlspecialchars($group['group_name']); ?>
                                </h4>
                                <span style="display: inline-block; padding: 3px 10px; background: var(--success-color); color: white; border-radius: 12px; font-size: 0.75rem; margin-bottom: 8px;">
                                    <i class="fas fa-globe"></i> Public
                                </span>
                            </div>
                        </div>
                        
                        <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 12px; line-height: 1.5;">
                            <?php echo htmlspecialchars($group['description']); ?>
                        </p>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 12px; padding: 10px; background: #f8f9fa; border-radius: 8px;">
                            <div style="text-align: center;">
                                <i class="fas fa-users" style="color: var(--spiritual-blue);"></i>
                                <strong style="display: block; font-size: 1.2rem; color: var(--primary-color);"><?php echo $group['member_count']; ?></strong>
                                <span style="font-size: 0.75rem; color: var(--gray);">Members</span>
                            </div>
                            <div style="text-align: center;">
                                <i class="fas fa-comments" style="color: var(--spiritual-gold);"></i>
                                <strong style="display: block; font-size: 1.2rem; color: var(--primary-color);"><?php echo $group['message_count']; ?></strong>
                                <span style="font-size: 0.75rem; color: var(--gray);">Messages</span>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 10px; border-top: 1px solid #dee2e6;">
                            <div style="font-size: 0.8rem; color: var(--gray);">
                                <i class="fas fa-user-circle"></i> Created by <?php echo htmlspecialchars($group['creator_name']); ?>
                                <br><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($group['created_at'])); ?>
                            </div>
                            <form method="POST" action="api/join-group.php" style="margin: 0;">
                                <input type="hidden" name="group_id" value="<?php echo $group['group_id']; ?>">
                                <button type="submit" class="btn btn-gold" style="padding: 8px 16px; font-size: 0.85rem;">
                                    <i class="fas fa-plus"></i> Join Group
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>No Available Groups</h3>
                    <p>All public groups have been joined or no groups exist yet</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
@media (max-width: 768px) {
    main .container > div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
    
    main .container > div[style*="grid-template-columns: repeat(auto-fit"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>