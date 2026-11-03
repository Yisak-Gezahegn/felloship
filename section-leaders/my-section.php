<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('section_leader')) {
    header('Location: ../home.php');
    exit();
}

$pageTitle = "My Section - Haramaya Fellowship";
include '../includes/header.php';

// Get section info
$section_info = $conn->query("SELECT * FROM sections WHERE section_id = {$_SESSION['section_id']}")->fetch_assoc();

// Get all section members
$members_stmt = $conn->prepare("SELECT * FROM users WHERE section_id = ? AND status = 'approved' ORDER BY full_name");
$members_stmt->bind_param("i", $_SESSION['section_id']);
$members_stmt->execute();
$members = $members_stmt->get_result();
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($section_info['section_name'] ?? 'My Section'); ?></h2>
            <p>Manage and view all members in your section</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-users"></i> <?php echo $members->num_rows; ?> Members &nbsp;|&nbsp;
                <i class="fas fa-tag"></i> Code: <?php echo htmlspecialchars($section_info['section_code'] ?? ''); ?>
            </p>
        </div>

        <div class="card" style="margin-top: 25px;">
            <div class="section-header">
                <h3>Section Members</h3>
            </div>

            <?php if ($members->num_rows > 0): ?>
            <table class="spiritual-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Year</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($member = $members->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($member['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                        <td><?php echo htmlspecialchars($member['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($member['department'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($member['current_year'] ?? 'N/A'); ?></td>
                        <td>
                            <span style="color: <?php echo $member['is_online'] ? 'var(--success-color)' : 'var(--gray)'; ?>">
                                <?php echo $member['is_online'] ? '● Online' : '○ Offline'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No Members Yet</h3>
                <p>Members who join your section will appear here</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
