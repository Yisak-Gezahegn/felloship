<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('family_leader')) {
    header('Location: ../home.php');
    exit();
}

// Get family
$fq = $conn->prepare("SELECT * FROM families WHERE mom_id = ? OR dad_id = ?");
$fq->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
$fq->execute();
$family = $fq->get_result()->fetch_assoc();

// If the family has a linked group, redirect to group chat
if ($family && !empty($family['group_id'])) {
    header('Location: ../group-chat.php?id=' . intval($family['group_id']));
    exit();
}

$pageTitle = "Family Group - Haramaya Fellowship";
include '../includes/header.php';
?>

<main style="padding: 40px 0;">
    <div class="container" style="max-width: 600px;">
        <div class="card" style="text-align: center; padding: 50px 30px;">
            <i class="fas fa-comments" style="font-size: 4rem; color: var(--spiritual-gold); margin-bottom: 20px; display: block;"></i>
            <h2 style="margin-bottom: 15px;">Family Group Chat</h2>
            <?php if (!$family): ?>
            <p style="color: var(--gray); margin-bottom: 25px;">You are not assigned to a family group yet. Contact your coordinator.</p>
            <a href="../messages.php" class="btn btn-primary">
                <i class="fas fa-envelope"></i> Message Coordinator
            </a>
            <?php else: ?>
            <p style="color: var(--gray); margin-bottom: 25px;">
                Your family group chat is not set up yet. Ask your coordinator to create a group for
                <strong><?php echo htmlspecialchars($family['family_name']); ?></strong>.
            </p>
            <a href="../groups.php" class="btn btn-primary">
                <i class="fas fa-users"></i> Browse Groups
            </a>
            <?php endif; ?>
            <br><br>
            <a href="dashboard.php" style="color: var(--gray); font-size: 0.9rem;">← Back to Dashboard</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
