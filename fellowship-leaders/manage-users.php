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

// Handle user actions BEFORE including header to avoid header errors
if (isset($_GET['action']) && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $conn->query("UPDATE users SET status = 'approved' WHERE user_id = $user_id");
    } elseif ($action === 'suspend') {
        $conn->query("UPDATE users SET status = 'suspended' WHERE user_id = $user_id");
    } elseif ($action === 'activate') {
        $conn->query("UPDATE users SET status = 'approved' WHERE user_id = $user_id");
    }
    
    header("Location: manage-users.php");
    exit();
}

$pageTitle = "Manage Users - Haramaya Fellowship";
include '../includes/header.php';

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';
$section_filter = isset($_GET['section']) ? $_GET['section'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "SELECT u.*, s.section_name FROM users u LEFT JOIN sections s ON u.section_id = s.section_id WHERE 1=1";

if ($status_filter !== 'all') {
    $query .= " AND u.status = '$status_filter'";
}
if ($role_filter !== 'all') {
    $query .= " AND u.role = '$role_filter'";
}
if ($section_filter !== 'all') {
    $query .= " AND u.section_id = " . intval($section_filter);
}
if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $query .= " AND (u.full_name LIKE '%$search_safe%' OR u.email LIKE '%$search_safe%')";
}

$query .= " ORDER BY u.created_at DESC";
$users = $conn->query($query);

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$approved_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'approved'")->fetch_assoc()['count'];
$pending_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'pending'")->fetch_assoc()['count'];
$suspended_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'suspended'")->fetch_assoc()['count'];

// Get sections for filter
$sections = $conn->query("SELECT * FROM sections ORDER BY section_name");
?>

<main style="padding: 25px 0;">
    <div class="container">
        <!-- Page Header -->
        <div class="dashboard-welcome">
            <h2>👥 Manage Users</h2>
            <p>View and manage all fellowship members</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-users"></i> <?php echo $total_users; ?> Total | 
                <i class="fas fa-check-circle"></i> <?php echo $approved_users; ?> Approved | 
                <i class="fas fa-clock"></i> <?php echo $pending_users; ?> Pending
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
                    <li><a href="my-groups.php"><i class="fas fa-users"></i> My Groups</a></li>
                    <li><a href="create-group.php"><i class="fas fa-plus-circle"></i> Create Group</a></li>
                    <li><a href="../messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="financial-reports.php"><i class="fas fa-dollar-sign"></i> Financial Reports</a></li>
                    <li><a href="manage-users.php" class="active"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </aside>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Statistics Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px;">
                        <i class="fas fa-users" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 20px;">
                        <i class="fas fa-check-circle" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $approved_users; ?></h3>
                        <p>Approved</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 20px;">
                        <i class="fas fa-clock" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $pending_users; ?></h3>
                        <p>Pending</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); padding: 20px;">
                        <i class="fas fa-ban" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $suspended_users; ?></h3>
                        <p>Suspended</p>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="card">
                    <form method="GET" action="">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                            <div class="form-group" style="margin: 0;">
                                <label for="search" style="font-size: 0.9rem; margin-bottom: 5px;">
                                    <i class="fas fa-search"></i> Search
                                </label>
                                <input type="text" 
                                       id="search" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Name or email..."
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>

                            <div class="form-group" style="margin: 0;">
                                <label for="status" style="font-size: 0.9rem; margin-bottom: 5px;">
                                    <i class="fas fa-filter"></i> Status
                                </label>
                                <select id="status" name="status" class="form-control">
                                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="suspended" <?php echo $status_filter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                </select>
                            </div>

                            <div class="form-group" style="margin: 0;">
                                <label for="role" style="font-size: 0.9rem; margin-bottom: 5px;">
                                    <i class="fas fa-user-tag"></i> Role
                                </label>
                                <select id="role" name="role" class="form-control">
                                    <option value="all" <?php echo $role_filter === 'all' ? 'selected' : ''; ?>>All Roles</option>
                                    <option value="fellowship_leader" <?php echo $role_filter === 'fellowship_leader' ? 'selected' : ''; ?>>Fellowship Leader</option>
                                    <option value="section_leader" <?php echo $role_filter === 'section_leader' ? 'selected' : ''; ?>>Section Leader</option>
                                    <option value="family_leader" <?php echo $role_filter === 'family_leader' ? 'selected' : ''; ?>>Family Leader</option>
                                    <option value="member" <?php echo $role_filter === 'member' ? 'selected' : ''; ?>>Member</option>
                                </select>
                            </div>

                            <div class="form-group" style="margin: 0;">
                                <label for="section" style="font-size: 0.9rem; margin-bottom: 5px;">
                                    <i class="fas fa-layer-group"></i> Section
                                </label>
                                <select id="section" name="section" class="form-control">
                                    <option value="all" <?php echo $section_filter === 'all' ? 'selected' : ''; ?>>All Sections</option>
                                    <?php while ($section = $sections->fetch_assoc()): ?>
                                    <option value="<?php echo $section['section_id']; ?>" <?php echo $section_filter == $section['section_id'] ? 'selected' : ''; ?>>
                                        <?php echo $section['section_name']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <a href="manage-users.php" class="btn" style="background: var(--gray); color: white;">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="card">
                    <div class="section-header">
                        <h3>All Users (<?php echo $users->num_rows; ?>)</h3>
                    </div>

                    <?php if ($users->num_rows > 0): ?>
                    <div style="overflow-x: auto;">
                        <table class="spiritual-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Section</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                        <?php if ($user['is_online']): ?>
                                        <span style="color: var(--success-color); font-size: 0.8rem;"> ● Online</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></td>
                                    <td><?php echo $user['section_name'] ?? 'N/A'; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $user['status']; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <button onclick="viewUser(<?php echo $user['user_id']; ?>)" 
                                                    class="btn btn-primary" 
                                                    style="padding: 6px 12px; font-size: 0.85rem;"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($user['status'] === 'pending'): ?>
                                            <a href="?action=approve&user_id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-success" 
                                               style="padding: 6px 12px; font-size: 0.85rem;"
                                               title="Approve"
                                               onclick="return confirm('Approve this user?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($user['status'] === 'approved'): ?>
                                            <a href="?action=suspend&user_id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-danger" 
                                               style="padding: 6px 12px; font-size: 0.85rem;"
                                               title="Suspend"
                                               onclick="return confirm('Suspend this user?')">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($user['status'] === 'suspended'): ?>
                                            <a href="?action=activate&user_id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-success" 
                                               style="padding: 6px 12px; font-size: 0.85rem;"
                                               title="Activate"
                                               onclick="return confirm('Activate this user?')">
                                                <i class="fas fa-check-circle"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
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
                        <p>Try adjusting your filters or search criteria</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- User Details Modal (Simple version) -->
<div id="userModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: var(--border-radius); max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: var(--primary-color); margin: 0;">User Details</h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray);">×</button>
        </div>
        <div id="userDetails"></div>
    </div>
</div>

<script>
function viewUser(userId) {
    // Simple implementation - you can enhance this with AJAX
    document.getElementById('userModal').style.display = 'flex';
    document.getElementById('userDetails').innerHTML = '<p>Loading user details...</p>';
    
    // You can add AJAX call here to fetch user details
    setTimeout(() => {
        document.getElementById('userDetails').innerHTML = '<p>User ID: ' + userId + '</p><p>Full details would be loaded via AJAX</p>';
    }, 500);
}

function closeModal() {
    document.getElementById('userModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
