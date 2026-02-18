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

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        $action = $_POST['action'];
        
        if ($action === 'reactivate') {
            $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $success_message = "User reactivated successfully!";
            } else {
                $error_message = "Failed to reactivate user.";
            }
        } elseif ($action === 'delete') {
            // Note: In a real system, you might want to soft delete or archive instead
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $success_message = "User deleted successfully!";
            } else {
                $error_message = "Failed to delete user.";
            }
        }
    }
}

$pageTitle = "Suspended Users - Admin Dashboard";
include '../includes/header.php';

// Get suspended users
$suspended_users = $conn->query("SELECT u.*, s.section_name FROM users u LEFT JOIN sections s ON u.section_id = s.section_id WHERE u.status = 'suspended' ORDER BY u.updated_at DESC");
$suspended_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'suspended'")->fetch_assoc()['count'];
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>🚫 Suspended Users</h2>
            <p>Manage suspended user accounts</p>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 25px 0;">
            <div class="stat-card" style="background: linear-gradient(135deg, #f5576c, #f093fb);">
                <i class="fas fa-user-slash"></i>
                <h3><?php echo $suspended_count; ?></h3>
                <p>Suspended Users</p>
            </div>
        </div>

        <!-- Suspended Users -->
        <div class="card">
            <div class="section-header">
                <h3>Suspended User Accounts</h3>
            </div>

            <?php if ($suspended_users->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="spiritual-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Section</th>
                            <th>Suspended Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $suspended_users->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></td>
                            <td><?php echo $user['section_name'] ?? 'None'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['updated_at'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <button type="submit" name="action" value="reactivate" class="btn btn-success btn-sm" onclick="return confirm('Reactivate this user?')">
                                        <i class="fas fa-user-check"></i> Reactivate
                                    </button>
                                    <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Permanently delete this user? This action cannot be undone!')" style="margin-left: 5px;">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-user-check"></i>
                <h3>No Suspended Users</h3>
                <p>All users are in good standing</p>
            </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>