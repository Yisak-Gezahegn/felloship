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

// Handle group actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'delete' && isset($_POST['group_id'])) {
            $group_id = intval($_POST['group_id']);
            
            // Delete group members first, then messages, then group
            $conn->query("DELETE FROM group_members WHERE group_id = $group_id");
            $conn->query("DELETE FROM group_messages WHERE group_id = $group_id");
            
            $stmt = $conn->prepare("DELETE FROM groups WHERE group_id = ?");
            $stmt->bind_param("i", $group_id);
            if ($stmt->execute()) {
                $success_message = "Group deleted successfully!";
            } else {
                $error_message = "Failed to delete group.";
            }
        } elseif ($action === 'update_privacy' && isset($_POST['group_id']) && isset($_POST['group_type'])) {
            $group_id = intval($_POST['group_id']);
            $group_type = $_POST['group_type'];
            
            // Validate group type
            $valid_types = ['public', 'private', 'family'];
            if (in_array($group_type, $valid_types)) {
                $stmt = $conn->prepare("UPDATE groups SET group_type = ? WHERE group_id = ?");
                $stmt->bind_param("si", $group_type, $group_id);
                if ($stmt->execute()) {
                    $success_message = "Group type updated successfully!";
                } else {
                    $error_message = "Failed to update group type.";
                }
            } else {
                $error_message = "Invalid group type selected.";
            }
        }
    }
}

$pageTitle = "Manage Groups - Admin Dashboard";
include '../includes/header.php';

// Get all groups with member counts and creator info
$groups = $conn->query("
    SELECT g.*, u.full_name as creator_name, COUNT(gm.user_id) as member_count,
           (SELECT COUNT(*) FROM group_messages WHERE group_id = g.group_id) as message_count
    FROM groups g 
    JOIN users u ON g.created_by = u.user_id 
    LEFT JOIN group_members gm ON g.group_id = gm.group_id 
    GROUP BY g.group_id 
    ORDER BY g.created_at DESC
");

$total_groups = $conn->query("SELECT COUNT(*) as count FROM groups")->fetch_assoc()['count'];
$private_groups = $conn->query("SELECT COUNT(*) as count FROM groups WHERE group_type = 'private'")->fetch_assoc()['count'];
$public_groups = $conn->query("SELECT COUNT(*) as count FROM groups WHERE group_type = 'public'")->fetch_assoc()['count'];
$family_groups = $conn->query("SELECT COUNT(*) as count FROM groups WHERE group_type = 'family'")->fetch_assoc()['count'];
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>👥 Manage Groups</h2>
            <p>Monitor and manage all fellowship groups</p>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 25px 0;">
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-blue), var(--spiritual-purple));">
                <i class="fas fa-users"></i>
                <h3><?php echo $total_groups; ?></h3>
                <p>Total Groups</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f);">
                <i class="fas fa-globe"></i>
                <h3><?php echo $public_groups; ?></h3>
                <p>Public Groups</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                <i class="fas fa-lock"></i>
                <h3><?php echo $private_groups; ?></h3>
                <p>Private Groups</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                <i class="fas fa-home"></i>
                <h3><?php echo $family_groups; ?></h3>
                <p>Family Groups</p>
            </div>
        </div>

        <!-- Groups Table -->
        <div class="card">
            <div class="section-header">
                <h3>All Groups</h3>
            </div>

            <?php if ($groups->num_rows > 0): ?>
            <!-- Desktop Table View -->
            <div style="overflow-x: auto;">
                <table class="spiritual-table">
                    <thead>
                        <tr>
                            <th>Group Name</th>
                            <th>Creator</th>
                            <th>Members</th>
                            <th>Messages</th>
                            <th>Group Type</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $groups_data = [];
                        while ($group = $groups->fetch_assoc()) {
                            $groups_data[] = $group;
                        }
                        foreach ($groups_data as $group): 
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($group['group_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($group['creator_name']); ?></td>
                            <td><?php echo $group['member_count']; ?></td>
                            <td><?php echo $group['message_count']; ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update_privacy">
                                    <input type="hidden" name="group_id" value="<?php echo $group['group_id']; ?>">
                                    <select name="group_type" class="form-control" style="display: inline-block; width: auto; padding: 6px 10px; border-radius: 20px; border: 2px solid 
                                        <?php 
                                        $type_colors = [
                                            'public' => 'var(--spiritual-gold)',
                                            'private' => '#f5576c',
                                            'family' => '#43e97b'
                                        ];
                                        echo $type_colors[$group['group_type']] ?? '#6c757d';
                                        ?>; font-weight: 600;" onchange="this.form.submit()">
                                        <option value="public" <?php echo $group['group_type'] == 'public' ? 'selected' : ''; ?>>🌐 Public</option>
                                        <option value="private" <?php echo $group['group_type'] == 'private' ? 'selected' : ''; ?>>🔒 Private</option>
                                        <option value="family" <?php echo $group['group_type'] == 'family' ? 'selected' : ''; ?>>🏠 Family</option>
                                    </select>
                                </form>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($group['created_at'])); ?></td>
                            <td>
                                <a href="../group-chat.php?id=<?php echo $group['group_id']; ?>" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <form method="POST" style="display: inline; margin-left: 5px;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="group_id" value="<?php echo $group['group_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this group? This will remove all members and messages!')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <?php foreach ($groups_data as $group): ?>
            <div class="group-mobile-card">
                <h4><?php echo htmlspecialchars($group['group_name']); ?></h4>
                
                <div class="group-mobile-info">
                    <div class="group-mobile-info-item">
                        <label>Creator</label>
                        <strong><?php echo htmlspecialchars($group['creator_name']); ?></strong>
                    </div>
                    <div class="group-mobile-info-item">
                        <label>Members</label>
                        <strong><?php echo $group['member_count']; ?> <i class="fas fa-users"></i></strong>
                    </div>
                    <div class="group-mobile-info-item">
                        <label>Messages</label>
                        <strong><?php echo $group['message_count']; ?> <i class="fas fa-comments"></i></strong>
                    </div>
                    <div class="group-mobile-info-item">
                        <label>Created</label>
                        <strong><?php echo date('M d, Y', strtotime($group['created_at'])); ?></strong>
                    </div>
                </div>

                <div class="group-type-selector">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--primary-color);">
                        <i class="fas fa-cog"></i> Group Type
                    </label>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_privacy">
                        <input type="hidden" name="group_id" value="<?php echo $group['group_id']; ?>">
                        <select name="group_type" class="form-control" style="padding: 10px; border-radius: 8px; border: 2px solid 
                            <?php 
                            $type_colors = [
                                'public' => 'var(--spiritual-gold)',
                                'private' => '#f5576c',
                                'family' => '#43e97b'
                            ];
                            echo $type_colors[$group['group_type']] ?? '#6c757d';
                            ?>; font-weight: 600;" onchange="this.form.submit()">
                            <option value="public" <?php echo $group['group_type'] == 'public' ? 'selected' : ''; ?>>🌐 Public</option>
                            <option value="private" <?php echo $group['group_type'] == 'private' ? 'selected' : ''; ?>>🔒 Private</option>
                            <option value="family" <?php echo $group['group_type'] == 'family' ? 'selected' : ''; ?>>🏠 Family</option>
                        </select>
                    </form>
                </div>

                <div class="group-mobile-actions">
                    <a href="../group-chat.php?id=<?php echo $group['group_id']; ?>" class="btn btn-primary" target="_blank">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="group_id" value="<?php echo $group['group_id']; ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this group? This will remove all members and messages!')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No Groups Found</h3>
                <p>No groups have been created yet</p>
            </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>