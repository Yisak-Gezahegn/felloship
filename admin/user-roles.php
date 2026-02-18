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

// Handle role changes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        $action = $_POST['action'];
        
        if ($action === 'change_role' && isset($_POST['new_role'])) {
            $new_role = $_POST['new_role'];
            $valid_roles = ['member', 'family_leader', 'section_leader', 'fellowship_leader', 'coordinator', 'developer'];
            
            if (in_array($new_role, $valid_roles)) {
                $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
                $stmt->bind_param("si", $new_role, $user_id);
                if ($stmt->execute()) {
                    $success_message = "User role updated successfully!";
                } else {
                    $error_message = "Failed to update user role.";
                }
            } else {
                $error_message = "Invalid role selected.";
            }
        }
    }
}

$pageTitle = "Manage User Roles - Admin Dashboard";
include '../includes/header.php';

// Get all users with their roles
$users = $conn->query("SELECT u.*, s.section_name FROM users u LEFT JOIN sections s ON u.section_id = s.section_id ORDER BY u.role, u.full_name");

// Get role statistics
$role_stats = $conn->query("SELECT role, COUNT(*) as count FROM users WHERE status = 'approved' GROUP BY role");
$roles_data = [];
while ($row = $role_stats->fetch_assoc()) {
    $roles_data[$row['role']] = $row['count'];
}
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>👥 Manage User Roles</h2>
            <p>Assign and manage user roles across the fellowship</p>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Role Statistics -->
        <div class="card" style="margin-bottom: 25px;">
            <div class="section-header">
                <h3>Role Distribution</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; padding: 20px;">
                <?php
                $role_colors = [
                    'developer' => '#667eea',
                    'coordinator' => '#f093fb',
                    'fellowship_leader' => '#4facfe',
                    'section_leader' => '#43e97b',
                    'family_leader' => '#fa709a',
                    'member' => '#30cfd0'
                ];
                $role_names = [
                    'developer' => 'Developer',
                    'coordinator' => 'Coordinator',
                    'fellowship_leader' => 'Fellowship Leader',
                    'section_leader' => 'Section Leader',
                    'family_leader' => 'Family Leader',
                    'member' => 'Member'
                ];
                foreach ($role_names as $role => $name):
                    $count = $roles_data[$role] ?? 0;
                    $color = $role_colors[$role] ?? '#6c757d';
                ?>
                <div style="background: <?php echo $color; ?>; color: white; padding: 20px; border-radius: var(--border-radius); text-align: center; box-shadow: var(--shadow-sm);">
                    <h4 style="font-size: 1.8rem; margin-bottom: 5px;"><?php echo $count; ?></h4>
                    <p style="font-size: 0.9rem; opacity: 0.95;"><?php echo $name; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="section-header">
                <h3>All Users</h3>
            </div>

            <?php if ($users->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="spiritual-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Current Role</th>
                            <th>Section</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="status-badge" style="background: <?php echo $role_colors[$user['role']] ?? '#6c757d'; ?>; color: white;">
                                    <?php echo $role_names[$user['role']] ?? ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                </span>
                            </td>
                            <td><?php echo $user['section_name'] ?? 'None'; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $user['status']; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['user_id'] != $_SESSION['user_id']): // Don't allow changing own role ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="action" value="change_role">
                                    <select name="new_role" class="form-control" style="display: inline-block; width: auto; margin-right: 5px;" onchange="this.form.submit()">
                                        <option value="">Change Role...</option>
                                        <?php foreach ($role_names as $role => $name): ?>
                                        <option value="<?php echo $role; ?>" <?php echo $user['role'] === $role ? 'selected' : ''; ?>>
                                            <?php echo $name; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                                <?php else: ?>
                                <span style="color: var(--gray); font-size: 0.9rem;">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No Users Found</h3>
                <p>No users in the system</p>
            </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>