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

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];
    
    switch ($action) {
        case 'approve':
            $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $success = "User approved successfully!";
            } else {
                $error = "Failed to approve user.";
            }
            break;
            
        case 'suspend':
            $stmt = $conn->prepare("UPDATE users SET status = 'suspended' WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $success = "User suspended successfully!";
            } else {
                $error = "Failed to suspend user.";
            }
            break;
            
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $success = "User deleted successfully!";
            } else {
                $error = "Failed to delete user.";
            }
            break;
            
        case 'change_role':
            $new_role = $_POST['new_role'];
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
            $stmt->bind_param("si", $new_role, $user_id);
            if ($stmt->execute()) {
                $success = "User role updated successfully!";
            } else {
                $error = "Failed to update user role.";
            }
            break;
    }
}

$pageTitle = "Manage All Users - Developer Dashboard";
include '../includes/header.php';

// Get all users with statistics
$users_query = "SELECT u.*, s.section_name,
                (SELECT COUNT(*) FROM posts WHERE user_id = u.user_id) as post_count,
                (SELECT COUNT(*) FROM comments WHERE user_id = u.user_id) as comment_count,
                (SELECT COUNT(*) FROM post_reactions WHERE user_id = u.user_id) as reaction_count
                FROM users u
                LEFT JOIN sections s ON u.section_id = s.section_id
                ORDER BY u.created_at DESC";
$users_result = $conn->query($users_query);

// Get user statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$approved_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'approved'")->fetch_assoc()['count'];
$pending_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'pending'")->fetch_assoc()['count'];
$suspended_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'suspended'")->fetch_assoc()['count'];
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>👥 Manage All Users</h2>
            <p>Complete user management and administration</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-users"></i> <?php echo $total_users; ?> Total Users | 
                <i class="fas fa-user-check"></i> <?php echo $approved_users; ?> Approved | 
                <i class="fas fa-user-clock"></i> <?php echo $pending_users; ?> Pending | 
                <i class="fas fa-user-slash"></i> <?php echo $suspended_users; ?> Suspended
            </p>
        </div>

        <div class="dashboard" style="display: flex; gap: 25px; max-width: 100%; overflow: hidden;">
            <aside class="sidebar" style="width: 250px; flex-shrink: 0;">
                <h3>👥 User Management</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage-all-users.php" class="active"><i class="fas fa-users"></i> All Users</a></li>
                    <li><a href="approve-users.php"><i class="fas fa-user-check"></i> Approve Users</a></li>
                    <li><a href="user-roles.php"><i class="fas fa-user-tag"></i> Manage Roles</a></li>
                    <li><a href="suspended-users.php"><i class="fas fa-user-slash"></i> Suspended Users</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                </ul>
            </aside>
            
            <div class="main-content" style="flex: 1; min-width: 0; max-width: calc(100% - 275px);">
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
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px; max-width: 100%;">
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px;">
                        <i class="fas fa-users" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 20px;">
                        <i class="fas fa-user-check" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $approved_users; ?></h3>
                        <p>Approved</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 20px;">
                        <i class="fas fa-user-clock" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $pending_users; ?></h3>
                        <p>Pending</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #ff6b6b 0%, #ffa500 100%); padding: 20px;">
                        <i class="fas fa-user-slash" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $suspended_users; ?></h3>
                        <p>Suspended</p>
                    </div>
                </div>

                <div class="card" style="max-width: 100%; overflow: hidden;">
                    <div class="section-header">
                        <h3>All Users</h3>
                    </div>

                    <?php if ($users_result->num_rows > 0): ?>
                    <!-- Desktop Table View -->
                    <div class="desktop-table" style="overflow-x: auto; display: block; max-width: 100%;">
                        <table class="spiritual-table" style="width: 100%; min-width: 800px;">
                            <thead>
                                <tr>
                                    <th style="min-width: 200px;">User</th>
                                    <th style="min-width: 180px;">Email</th>
                                    <th style="min-width: 120px;">Role</th>
                                    <th style="min-width: 100px;">Section</th>
                                    <th style="min-width: 100px;">Status</th>
                                    <th style="min-width: 100px;">Activity</th>
                                    <th style="min-width: 100px;">Joined</th>
                                    <th style="min-width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Reset the result pointer
                                $users_result->data_seek(0);
                                while ($user = $users_result->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td style="max-width: 200px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'default.jpg' && file_exists("../uploads/profiles/{$user['profile_picture']}")): ?>
                                            <img src="../uploads/profiles/<?php echo $user['profile_picture']; ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                            <?php else: ?>
                                            <div class="profile-pic-default" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <?php endif; ?>
                                            <div style="min-width: 0; overflow: hidden;">
                                                <strong style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                                <small style="color: var(--gray); display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">@<?php echo htmlspecialchars($user['username']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="max-width: 180px;">
                                        <span style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($user['email']); ?></span>
                                    </td>
                                    <td style="max-width: 120px;">
                                        <span class="status-badge" style="background: var(--spiritual-blue); white-space: nowrap; font-size: 0.75rem;">
                                            <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                        </span>
                                    </td>
                                    <td style="max-width: 100px;">
                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;"><?php echo $user['section_name'] ?: 'None'; ?></span>
                                    </td>
                                    <td style="max-width: 100px;">
                                        <span class="status-badge status-<?php echo $user['status']; ?>" style="font-size: 0.75rem;">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td style="max-width: 100px;">
                                        <small style="line-height: 1.3;">
                                            <?php echo $user['post_count']; ?> posts<br>
                                            <?php echo $user['comment_count']; ?> comments<br>
                                            <?php echo $user['reaction_count']; ?> reactions
                                        </small>
                                    </td>
                                    <td style="max-width: 100px;">
                                        <span style="font-size: 0.85rem; white-space: nowrap;"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                                    </td>
                                    <td style="max-width: 140px;">
                                        <div style="display: flex; gap: 3px; flex-wrap: wrap; justify-content: flex-start;">
                                            <?php if ($user['status'] === 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-success" style="padding: 4px 6px; font-size: 0.7rem;" title="Approve User">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($user['status'] !== 'suspended' && $user['user_id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <input type="hidden" name="action" value="suspend">
                                                <button type="submit" class="btn btn-warning" style="padding: 4px 6px; font-size: 0.7rem;" onclick="return confirm('Suspend this user?')" title="Suspend User">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-danger" style="padding: 4px 6px; font-size: 0.7rem;" onclick="return confirm('Delete this user permanently?')" title="Delete User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            
                                            <button onclick="editUser(<?php echo $user['user_id']; ?>, '<?php echo $user['role']; ?>')" class="btn btn-primary" style="padding: 4px 6px; font-size: 0.7rem;" title="Edit Role">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="mobile-cards" style="display: none;">
                        <?php 
                        // Reset the result pointer for mobile view
                        $users_result->data_seek(0);
                        while ($user = $users_result->fetch_assoc()): 
                        ?>
                        <div class="user-card" style="background: #f8f9fa; border-radius: var(--border-radius); padding: 20px; margin-bottom: 15px; border-left: 4px solid var(--spiritual-gold);">
                            <!-- User Header -->
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                                <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'default.jpg' && file_exists("../uploads/profiles/{$user['profile_picture']}")): ?>
                                <img src="../uploads/profiles/<?php echo $user['profile_picture']; ?>" alt="Profile" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                <?php else: ?>
                                <div class="profile-pic-default" style="width: 60px; height: 60px; flex-shrink: 0;">
                                    <i class="fas fa-user" style="font-size: 1.8rem;"></i>
                                </div>
                                <?php endif; ?>
                                <div style="flex: 1; min-width: 0;">
                                    <h4 style="margin: 0 0 5px 0; color: var(--primary-color); word-wrap: break-word;"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                    <p style="margin: 0; color: var(--gray); font-size: 0.9rem; word-wrap: break-word;">@<?php echo htmlspecialchars($user['username']); ?></p>
                                    <p style="margin: 5px 0 0 0; color: var(--gray); font-size: 0.85rem; word-wrap: break-word;"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                            </div>

                            <!-- User Info Grid -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; margin-bottom: 15px;">
                                <div>
                                    <strong style="color: var(--spiritual-blue); font-size: 0.85rem;">Role</strong>
                                    <div style="margin-top: 5px;">
                                        <span class="status-badge" style="background: var(--spiritual-blue); font-size: 0.8rem;">
                                            <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <strong style="color: var(--spiritual-purple); font-size: 0.85rem;">Status</strong>
                                    <div style="margin-top: 5px;">
                                        <span class="status-badge status-<?php echo $user['status']; ?>" style="font-size: 0.8rem;">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <strong style="color: var(--spiritual-gold); font-size: 0.85rem;">Section</strong>
                                    <p style="margin: 5px 0 0 0; font-size: 0.9rem;"><?php echo $user['section_name'] ?: 'None'; ?></p>
                                </div>
                                <div>
                                    <strong style="color: var(--success-color); font-size: 0.85rem;">Joined</strong>
                                    <p style="margin: 5px 0 0 0; font-size: 0.9rem;"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                                </div>
                            </div>

                            <!-- Activity Stats -->
                            <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                <strong style="color: var(--primary-color); font-size: 0.9rem; display: block; margin-bottom: 10px;">Activity</strong>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; text-align: center;">
                                    <div>
                                        <div style="font-size: 1.2rem; font-weight: bold; color: var(--spiritual-blue);"><?php echo $user['post_count']; ?></div>
                                        <div style="font-size: 0.8rem; color: var(--gray);">Posts</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 1.2rem; font-weight: bold; color: var(--spiritual-purple);"><?php echo $user['comment_count']; ?></div>
                                        <div style="font-size: 0.8rem; color: var(--gray);">Comments</div>
                                    </div>
                                    <div>
                                        <div style="font-size: 1.2rem; font-weight: bold; color: var(--spiritual-gold);"><?php echo $user['reaction_count']; ?></div>
                                        <div style="font-size: 0.8rem; color: var(--gray);">Reactions</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                                <?php if ($user['status'] === 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success" style="padding: 10px 15px;">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <?php if ($user['status'] !== 'suspended' && $user['user_id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="action" value="suspend">
                                    <button type="submit" class="btn btn-warning" style="padding: 10px 15px;" onclick="return confirm('Suspend this user?')">
                                        <i class="fas fa-ban"></i> Suspend
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <button onclick="editUser(<?php echo $user['user_id']; ?>, '<?php echo $user['role']; ?>')" class="btn btn-primary" style="padding: 10px 15px;">
                                    <i class="fas fa-edit"></i> Edit Role
                                </button>
                                
                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger" style="padding: 10px 15px;" onclick="return confirm('Delete this user permanently?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Users Found</h3>
                        <p>No users in the system</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Edit User Modal -->
<div id="editUserModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
    <div style="background: white; padding: 30px; border-radius: var(--border-radius); max-width: 500px; width: 100%; box-shadow: var(--shadow-lg); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: var(--primary-color); margin: 0; font-size: 1.3rem;">Edit User Role</h3>
            <button onclick="closeEditModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray); padding: 5px; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='none'">×</button>
        </div>
        <form method="POST" id="editUserForm">
            <input type="hidden" name="action" value="change_role">
            <input type="hidden" name="user_id" id="editUserId">
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="new_role" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark-color);">User Role:</label>
                <select name="new_role" id="editUserRole" class="form-control" required style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: var(--border-radius); font-size: 1rem; transition: border-color 0.3s ease;" onfocus="this.style.borderColor='var(--spiritual-blue)'" onblur="this.style.borderColor='#e9ecef'">
                    <option value="member">Member</option>
                    <option value="family_leader">Family Leader</option>
                    <option value="section_leader">Section Leader</option>
                    <option value="fellowship_leader">Fellowship Leader</option>
                    <option value="coordinator">Coordinator</option>
                    <option value="developer">Developer</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary" style="flex: 1; padding: 12px; font-size: 1rem;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px; font-size: 1rem;">
                    <i class="fas fa-save"></i> Update Role
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editUser(userId, currentRole) {
    document.getElementById('editUserId').value = userId;
    document.getElementById('editUserRole').value = currentRole;
    document.getElementById('editUserModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editUserModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('editUserModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

// Responsive view switching
function handleResponsiveView() {
    const desktopTable = document.querySelector('.desktop-table');
    const mobileCards = document.querySelector('.mobile-cards');
    
    if (window.innerWidth <= 768) {
        // Mobile view
        if (desktopTable) desktopTable.style.display = 'none';
        if (mobileCards) mobileCards.style.display = 'block';
    } else {
        // Desktop view
        if (desktopTable) desktopTable.style.display = 'block';
        if (mobileCards) mobileCards.style.display = 'none';
    }
}

// Initialize responsive view
document.addEventListener('DOMContentLoaded', handleResponsiveView);
window.addEventListener('resize', handleResponsiveView);
</script>

<style>
/* Responsive improvements */
.container {
    max-width: 100%;
    margin: 0 auto;
    padding: 0 15px;
    box-sizing: border-box;
}

.dashboard {
    max-width: 100%;
    overflow: hidden;
}

.main-content {
    overflow: hidden;
    box-sizing: border-box;
}

.card {
    max-width: 100%;
    overflow: hidden;
    box-sizing: border-box;
}

.desktop-table {
    max-width: 100%;
    overflow-x: auto;
    box-sizing: border-box;
}

.spiritual-table {
    table-layout: fixed;
    width: 100%;
    border-collapse: collapse;
}

.spiritual-table th,
.spiritual-table td {
    padding: 8px 6px;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
    overflow: hidden;
    box-sizing: border-box;
}

@media (max-width: 768px) {
    .dashboard {
        display: block !important;
        max-width: 100%;
    }
    
    .sidebar {
        position: static !important;
        width: 100% !important;
        margin-bottom: 20px;
        max-width: 100%;
    }
    
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
        width: 100%;
    }
    
    .dashboard-welcome {
        text-align: center;
        padding: 20px 15px;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    .dashboard-welcome h2 {
        font-size: 1.5rem;
    }
    
    .dashboard-welcome p {
        font-size: 0.9rem;
        line-height: 1.6;
        word-wrap: break-word;
    }
    
    /* Statistics cards responsive */
    .stat-card {
        text-align: center;
        padding: 15px !important;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    .stat-card h3 {
        font-size: 1.5rem !important;
    }
    
    .stat-card p {
        font-size: 0.9rem;
    }
    
    /* Modal responsive */
    #editUserModal > div {
        width: 95% !important;
        max-width: none !important;
        margin: 20px;
        max-height: 90vh;
        overflow-y: auto;
        box-sizing: border-box;
    }
    
    /* Button improvements */
    .btn {
        font-size: 0.9rem;
        padding: 8px 12px;
    }
    
    /* Card improvements */
    .card {
        margin: 0 5px 20px 5px;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
        max-width: calc(100% - 10px);
        box-sizing: border-box;
    }
    
    .section-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .section-header h3 {
        font-size: 1.2rem;
        margin: 0;
    }
    
    .mobile-cards {
        padding: 0 10px;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    .user-card {
        max-width: 100%;
        box-sizing: border-box;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0 5px;
    }
    
    .dashboard-welcome {
        padding: 15px 10px;
    }
    
    .dashboard-welcome h2 {
        font-size: 1.3rem;
    }
    
    .user-card {
        margin: 0 2px 15px 2px !important;
        padding: 15px !important;
        max-width: calc(100% - 4px);
    }
    
    .stat-card {
        padding: 12px !important;
    }
    
    .stat-card h3 {
        font-size: 1.3rem !important;
    }
    
    /* Mobile card button improvements */
    .user-card .btn {
        font-size: 0.8rem;
        padding: 8px 10px;
        flex: 1;
        min-width: 80px;
    }
    
    .user-card > div:last-child {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px !important;
    }
    
    .card {
        margin: 0 2px 20px 2px;
        max-width: calc(100% - 4px);
    }
}

/* Table improvements for larger screens */
@media (min-width: 769px) {
    .container {
        max-width: 1400px;
        padding: 0 20px;
    }
    
    .dashboard {
        max-width: 100%;
    }
    
    .main-content {
        max-width: calc(100% - 275px);
        overflow: hidden;
    }
    
    .spiritual-table {
        font-size: 0.85rem;
        min-width: 1000px;
    }
    
    .spiritual-table th,
    .spiritual-table td {
        padding: 10px 8px;
        vertical-align: middle;
    }
    
    .desktop-table {
        max-width: 100%;
        overflow-x: auto;
    }
}

/* Sidebar responsive improvements */
@media (max-width: 768px) {
    .sidebar-menu {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        max-width: 100%;
    }
    
    .sidebar-menu li {
        flex: 0 0 auto;
    }
    
    .sidebar-menu a {
        padding: 8px 12px;
        font-size: 0.85rem;
        white-space: nowrap;
    }
    
    .sidebar h3 {
        text-align: center;
        font-size: 1rem;
        margin-bottom: 15px;
    }
}

/* Enhanced mobile card styling */
.user-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    max-width: 100%;
    box-sizing: border-box;
}

.user-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Status badge improvements */
.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.status-approved {
    background: #28a745;
    color: white;
}

.status-pending {
    background: #ffc107;
    color: #212529;
}

.status-suspended {
    background: #dc3545;
    color: white;
}

/* Text overflow handling */
.text-ellipsis {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

/* Ensure no horizontal overflow */
* {
    box-sizing: border-box;
}

body {
    overflow-x: hidden;
}

.container {
    overflow-x: hidden;
}
</style>

<?php include '../includes/footer.php'; ?>