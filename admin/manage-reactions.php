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

// Handle reaction actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['reaction_id'])) {
        $reaction_id = intval($_POST['reaction_id']);
        $action = $_POST['action'];
        
        if ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM post_reactions WHERE reaction_id = ?");
            $stmt->bind_param("i", $reaction_id);
            if ($stmt->execute()) {
                $success_message = "Reaction deleted successfully!";
            } else {
                $error_message = "Failed to delete reaction.";
            }
        }
    }
}

$pageTitle = "Manage Reactions - Admin Dashboard";
include '../includes/header.php';

// Get all reactions with post and user info
$reactions = $conn->query("
    SELECT pr.*, u.full_name as user_name, u.email as user_email, 
           p.content as post_content, pu.full_name as post_author
    FROM post_reactions pr 
    JOIN users u ON pr.user_id = u.user_id 
    JOIN posts p ON pr.post_id = p.post_id 
    JOIN users pu ON p.user_id = pu.user_id 
    ORDER BY pr.created_at DESC
");

// Get reaction statistics
$total_reactions = $conn->query("SELECT COUNT(*) as count FROM post_reactions")->fetch_assoc()['count'];
$reaction_types = $conn->query("SELECT reaction_type, COUNT(*) as count FROM post_reactions GROUP BY reaction_type");
$type_stats = [];
while ($row = $reaction_types->fetch_assoc()) {
    $type_stats[$row['reaction_type']] = $row['count'];
}
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>❤️ Manage Reactions</h2>
            <p>Monitor all user reactions and engagement</p>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin: 25px 0;">
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f);">
                <i class="fas fa-heart"></i>
                <h3><?php echo $total_reactions; ?></h3>
                <p>Total Reactions</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-blue), #667eea);">
                <i class="fas fa-thumbs-up"></i>
                <h3><?php echo $type_stats['like'] ?? 0; ?></h3>
                <p>Likes</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-purple), #764ba2);">
                <i class="fas fa-praying-hands"></i>
                <h3><?php echo $type_stats['pray'] ?? 0; ?></h3>
                <p>Prayers</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                <i class="fas fa-heart"></i>
                <h3><?php echo $type_stats['love'] ?? 0; ?></h3>
                <p>Love</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                <i class="fas fa-hands"></i>
                <h3><?php echo $type_stats['amen'] ?? 0; ?></h3>
                <p>Amen</p>
            </div>
        </div>

        <!-- Reactions Table -->
        <div class="card">
            <div class="section-header">
                <h3>All Reactions</h3>
            </div>

            <?php if ($reactions->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="spiritual-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Reaction</th>
                            <th>Post</th>
                            <th>Post Author</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reaction = $reactions->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($reaction['user_name']); ?></strong>
                                <br><small style="color: var(--gray);"><?php echo htmlspecialchars($reaction['user_email']); ?></small>
                            </td>
                            <td>
                                <span class="status-badge" style="background: 
                                    <?php 
                                    $colors = [
                                        'like' => 'var(--spiritual-blue)',
                                        'pray' => 'var(--spiritual-purple)',
                                        'love' => '#f5576c',
                                        'amen' => 'var(--spiritual-gold)'
                                    ];
                                    echo $colors[$reaction['reaction_type']] ?? '#6c757d';
                                    ?>; color: white;">
                                    <?php 
                                    $icons = [
                                        'like' => '👍',
                                        'pray' => '🙏',
                                        'love' => '❤️',
                                        'amen' => '🙌'
                                    ];
                                    echo $icons[$reaction['reaction_type']] ?? '👍';
                                    ?> <?php echo ucfirst($reaction['reaction_type']); ?>
                                </span>
                            </td>
                            <td style="max-width: 200px;">
                                <div style="word-wrap: break-word; font-size: 0.9rem; color: var(--gray);">
                                    <?php echo htmlspecialchars(substr($reaction['post_content'], 0, 80)); ?>
                                    <?php if (strlen($reaction['post_content']) > 80) echo '...'; ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($reaction['post_author']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($reaction['created_at'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="reaction_id" value="<?php echo $reaction['reaction_id']; ?>">
                                    <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete this reaction?')">
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
                <i class="fas fa-heart"></i>
                <h3>No Reactions Found</h3>
                <p>No reactions have been posted yet</p>
            </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>