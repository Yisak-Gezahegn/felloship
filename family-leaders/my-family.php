<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('family_leader')) {
    header('Location: ../home.php');
    exit();
}

$pageTitle = "My Family - Haramaya Fellowship";
include '../includes/header.php';

// Get family
$family_query = $conn->prepare("SELECT * FROM families WHERE mom_id = ? OR dad_id = ?");
$family_query->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
$family_query->execute();
$family = $family_query->get_result()->fetch_assoc();

$members = null;
if ($family) {
    $members_stmt = $conn->prepare("SELECT * FROM users WHERE family_id = ? ORDER BY full_name");
    $members_stmt->bind_param("i", $family['family_id']);
    $members_stmt->execute();
    $members = $members_stmt->get_result();
}
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>👨‍👩‍👧‍👦 My Family</h2>
            <p>Manage and connect with your family members</p>
        </div>

        <?php if (!$family): ?>
        <div class="card" style="margin-top: 25px;">
            <div class="warning-box">
                <h3><i class="fas fa-exclamation-triangle"></i> No Family Assigned</h3>
                <p>You are not assigned to a family group yet. Contact the coordinator to be assigned.</p>
                <a href="../messages.php" class="btn btn-primary" style="margin-top: 15px;">
                    <i class="fas fa-envelope"></i> Contact Coordinator
                </a>
            </div>
        </div>
        <?php else: ?>

        <div class="card" style="margin-top: 25px;">
            <div class="section-header">
                <h3><i class="fas fa-home"></i> <?php echo htmlspecialchars($family['family_name']); ?></h3>
            </div>

            <?php if ($members && $members->num_rows > 0): ?>
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
                        <td>
                            <strong><?php echo htmlspecialchars($member['full_name']); ?></strong>
                        </td>
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
                <p>Family members will appear here once assigned</p>
            </div>
            <?php endif; ?>
        </div>

        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
