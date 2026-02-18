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

// Handle comment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['comment_id'])) {
        $comment_id = intval($_POST['comment_id']);
        $action = $_POST['action'];
        
        if ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
            $stmt->bind_param("i", $comment_id);
            if ($stmt->execute()) {
                $success_message = "Comment deleted successfully!";
            } else {
                $error_message = "Failed to delete comment.";
            }
        }
    }
}

$pageTitle = "Manage Comments - Admin Dashboard";
include '../includes/header.php';

// Get all comments with post and user info
$comments = $conn->query("
    SELECT c.*, u.full_name as author_name, u.email as author_email, 
           p.content as post_content, pu.full_name as post_author
    FROM comments c 
    JOIN users u ON c.user_id = u.user_id 
    JOIN posts p ON c.post_id = p.post_id 
    JOIN users pu ON p.user_id = pu.user_id 
    ORDER BY c.created_at DESC
");

$total_comments = $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];
$recent_comments = $conn->query("SELECT COUNT(*) as count FROM comments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>💬 Manage Comments</h2>
            <p>Monitor and moderate all user comments</p>
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
                <i class="fas fa-comments"></i>
                <h3><?php echo $total_comments; ?></h3>
                <p>Total Comments</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f);">
                <i class="fas fa-comment-dots"></i>
                <h3><?php echo $recent_comments; ?></h3>
                <p>This Week</p>
            </div>
        </div>

        <!-- Comments Table -->
        <div class="card">
            <div class="section-header">
                <h3>All Comments</h3>
            </div>

            <?php if ($comments->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="spiritual-table">
                    <thead>
                        <tr>
                            <th>Comment</th>
                            <th>Author</th>
                            <th>Post</th>
                            <th>Post Author</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($comment = $comments->fetch_assoc()): ?>
                        <tr>
                            <td style="max-width: 300px;">
                                <div style="word-wrap: break-word;">
                                    <?php echo nl2br(htmlspecialchars(substr($comment['comment_text'], 0, 100))); ?>
                                    <?php if (strlen($comment['comment_text']) > 100) echo '...'; ?>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($comment['author_name']); ?></strong>
                                <br><small style="color: var(--gray);"><?php echo htmlspecialchars($comment['author_email']); ?></small>
                            </td>
                            <td style="max-width: 200px;">
                                <div style="word-wrap: break-word; font-size: 0.9rem; color: var(--gray);">
                                    <?php echo htmlspecialchars(substr($comment['post_content'], 0, 80)); ?>
                                    <?php if (strlen($comment['post_content']) > 80) echo '...'; ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($comment['post_author']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                    <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete this comment?')">
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
                <i class="fas fa-comments"></i>
                <h3>No Comments Found</h3>
                <p>No comments have been posted yet</p>
            </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>