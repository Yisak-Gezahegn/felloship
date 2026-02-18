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

$pageTitle = "Approve Leaders - Haramaya Fellowship";
include '../includes/header.php';

// Handle approval/rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $conn->query("UPDATE users SET status = 'approved' WHERE user_id = $user_id");
        $success = "Leader approved successfully!";
    } elseif ($action === 'reject') {
        $conn->query("UPDATE users SET status = 'suspended' WHERE user_id = $user_id");
        $success = "Leader application rejected.";
    }
}

// Get pending leader applications
$pending_leaders = $conn->query("SELECT u.*, s.section_name 
                                 FROM users u 
                                 LEFT JOIN sections s ON u.section_id = s.section_id
                                 WHERE u.status = 'pending' AND u.role IN ('fellowship_leader', 'section_leader', 'family_leader')
                                 ORDER BY u.created_at DESC");

$total_pending = $pending_leaders->num_rows;
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>✅ Approve Leaders</h2>
            <p>Review and approve leadership applications</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-clock"></i> <?php echo $total_pending; ?> Pending Applications
            </p>
        </div>

        <div class="dashboard">
            <aside class="sidebar">
                <h3>📋 Coordinator Panel</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="my-group.php"><i class="fas fa-users"></i> Leaders Group</a></li>
                    <li><a href="../messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="manage-users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="approve-leaders.php" class="active"><i class="fas fa-user-check"></i> Approve Leaders</a></li>
                    <li><a href="section-reports.php"><i class="fas fa-file-alt"></i> Section Reports</a></li>
                    <li><a href="create-post.php"><i class="fas fa-plus"></i> Create Post/Event</a></li>
                    <li><a href="search-members.php"><i class="fas fa-search"></i> Search Members</a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                </ul>
            </aside>
            
            <div class="main-content">
                <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="section-header">
                        <h3>Pending Leader Applications (<?php echo $total_pending; ?>)</h3>
                    </div>

                    <?php if ($total_pending > 0): ?>
                    <div style="display: grid; gap: 20px;">
                        <?php while ($leader = $pending_leaders->fetch_assoc()): ?>
                        <div style="padding: 20px; background: #f8f9fa; border-radius: var(--border-radius); border-left: 4px solid var(--spiritual-gold);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                <div style="display: flex; gap: 15px; align-items: start;">
                                    <?php if (!empty($leader['profile_picture']) && $leader['profile_picture'] !== 'default.jpg' && file_exists("../uploads/profiles/{$leader['profile_picture']}")): ?>
                                    <img src="../uploads/profiles/<?php echo $leader['profile_picture']; ?>" alt="Profile" style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid var(--spiritual-gold);">
                                    <?php else: ?>
                                    <div class="profile-pic-default-large" style="width: 70px; height: 70px;">
                                        <i class="fas fa-user" style="font-size: 2rem;"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <h4 style="color: var(--primary-color); margin-bottom: 5px;"><?php echo htmlspecialchars($leader['full_name']); ?></h4>
                                        <span class="status-badge" style="background: var(--spiritual-purple);">
                                            <?php echo ucfirst(str_replace('_', ' ', $leader['role'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <span style="font-size: 0.85rem; color: var(--gray);">
                                    Applied: <?php echo date('M d, Y', strtotime($leader['created_at'])); ?>
                                </span>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                                <div class="info-box" style="margin: 0;">
                                    <p style="margin: 5px 0;"><i class="fas fa-envelope"></i> <strong>Email:</strong> <?php echo htmlspecialchars($leader['email']); ?></p>
                                    <p style="margin: 5px 0;"><i class="fas fa-phone"></i> <strong>Phone:</strong> <?php echo htmlspecialchars($leader['phone'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="info-box" style="margin: 0;">
                                    <p style="margin: 5px 0;"><i class="fas fa-graduation-cap"></i> <strong>Department:</strong> <?php echo htmlspecialchars($leader['department'] ?? 'N/A'); ?></p>
                                    <p style="margin: 5px 0;"><i class="fas fa-layer-group"></i> <strong>Section:</strong> <?php echo htmlspecialchars($leader['section_name'] ?? 'N/A'); ?></p>
                                </div>
                            </div>

                            <div style="display: flex; gap: 10px;">
                                <a href="?action=approve&id=<?php echo $leader['user_id']; ?>" 
                                   class="btn btn-success" 
                                   style="flex: 1; text-align: center;"
                                   onclick="return confirm('Approve this leader application?')">
                                    <i class="fas fa-check-circle"></i> Approve
                                </a>
                                <a href="?action=reject&id=<?php echo $leader['user_id']; ?>" 
                                   class="btn btn-danger" 
                                   style="flex: 1; text-align: center;"
                                   onclick="return confirm('Reject this leader application?')">
                                    <i class="fas fa-times-circle"></i> Reject
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-check"></i>
                        <h3>No Pending Applications</h3>
                        <p>All leader applications have been reviewed</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
