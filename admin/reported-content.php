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

// Create content_reports table if it doesn't exist
$create_table_query = "CREATE TABLE IF NOT EXISTS content_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    content_type ENUM('post', 'comment', 'user', 'message') NOT NULL,
    content_id INT NOT NULL,
    reporter_id INT NOT NULL,
    reason ENUM('inappropriate', 'spam', 'harassment', 'false_info', 'other') NOT NULL,
    description TEXT,
    status ENUM('pending', 'resolved', 'dismissed') DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_content (content_type, content_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$conn->query($create_table_query);

// Handle report actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['report_id'])) {
        $report_id = intval($_POST['report_id']);
        $action = $_POST['action'];
        $reviewer_id = $_SESSION['user_id'];
        
        if ($action === 'resolve') {
            $stmt = $conn->prepare("UPDATE content_reports SET status = 'resolved', reviewed_by = ?, reviewed_at = NOW() WHERE report_id = ?");
            $stmt->bind_param("ii", $reviewer_id, $report_id);
            if ($stmt->execute()) {
                $success_message = "Report marked as resolved successfully!";
            } else {
                $error_message = "Failed to resolve report.";
            }
        } elseif ($action === 'dismiss') {
            $stmt = $conn->prepare("UPDATE content_reports SET status = 'dismissed', reviewed_by = ?, reviewed_at = NOW() WHERE report_id = ?");
            $stmt->bind_param("ii", $reviewer_id, $report_id);
            if ($stmt->execute()) {
                $success_message = "Report dismissed successfully!";
            } else {
                $error_message = "Failed to dismiss report.";
            }
        } elseif ($action === 'delete_content' && isset($_POST['content_type']) && isset($_POST['content_id'])) {
            $content_type = $_POST['content_type'];
            $content_id = intval($_POST['content_id']);
            
            // Delete the reported content
            if ($content_type === 'post') {
                $stmt = $conn->prepare("DELETE FROM posts WHERE post_id = ?");
                $stmt->bind_param("i", $content_id);
                if ($stmt->execute()) {
                    // Also mark report as resolved
                    $stmt2 = $conn->prepare("UPDATE content_reports SET status = 'resolved', reviewed_by = ?, reviewed_at = NOW() WHERE report_id = ?");
                    $stmt2->bind_param("ii", $reviewer_id, $report_id);
                    $stmt2->execute();
                    $success_message = "Content deleted and report resolved!";
                } else {
                    $error_message = "Failed to delete content.";
                }
            } elseif ($content_type === 'comment') {
                $stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
                $stmt->bind_param("i", $content_id);
                if ($stmt->execute()) {
                    // Also mark report as resolved
                    $stmt2 = $conn->prepare("UPDATE content_reports SET status = 'resolved', reviewed_by = ?, reviewed_at = NOW() WHERE report_id = ?");
                    $stmt2->bind_param("ii", $reviewer_id, $report_id);
                    $stmt2->execute();
                    $success_message = "Content deleted and report resolved!";
                } else {
                    $error_message = "Failed to delete content.";
                }
            }
        }
    }
}

$pageTitle = "Reported Content - Admin Dashboard";
include '../includes/header.php';

// Get all reports with reporter and reviewer information
$reports_query = "SELECT cr.*, 
                  u1.full_name as reporter_name, u1.email as reporter_email,
                  u2.full_name as reviewer_name,
                  CASE 
                    WHEN cr.content_type = 'post' THEN (SELECT LEFT(content, 100) FROM posts WHERE post_id = cr.content_id)
                    WHEN cr.content_type = 'comment' THEN (SELECT LEFT(comment_text, 100) FROM comments WHERE comment_id = cr.content_id)
                    ELSE 'N/A'
                  END as content_preview
                  FROM content_reports cr
                  JOIN users u1 ON cr.reporter_id = u1.user_id
                  LEFT JOIN users u2 ON cr.reviewed_by = u2.user_id
                  ORDER BY 
                    CASE cr.status 
                      WHEN 'pending' THEN 1 
                      WHEN 'resolved' THEN 2 
                      WHEN 'dismissed' THEN 3 
                    END,
                    cr.created_at DESC";

$reports_result = $conn->query($reports_query);

// Get statistics
$total_reports = $conn->query("SELECT COUNT(*) as count FROM content_reports")->fetch_assoc()['count'];
$pending_reports = $conn->query("SELECT COUNT(*) as count FROM content_reports WHERE status = 'pending'")->fetch_assoc()['count'];
$resolved_reports = $conn->query("SELECT COUNT(*) as count FROM content_reports WHERE status = 'resolved'")->fetch_assoc()['count'];
$dismissed_reports = $conn->query("SELECT COUNT(*) as count FROM content_reports WHERE status = 'dismissed'")->fetch_assoc()['count'];
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>🚩 Reported Content</h2>
            <p>Review and moderate reported content</p>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin: 25px 0;">
            <div class="stat-card" style="background: linear-gradient(135deg, #f5576c, #f093fb);">
                <i class="fas fa-flag"></i>
                <h3><?php echo $total_reports; ?></h3>
                <p>Total Reports</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f);">
                <i class="fas fa-clock"></i>
                <h3><?php echo $pending_reports; ?></h3>
                <p>Pending Review</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                <i class="fas fa-check-circle"></i>
                <h3><?php echo $resolved_reports; ?></h3>
                <p>Resolved</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #6c757d, #95a5a6);">
                <i class="fas fa-times-circle"></i>
                <h3><?php echo $dismissed_reports; ?></h3>
                <p>Dismissed</p>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="card">
            <div class="section-header">
                <h3>Content Reports</h3>
            </div>

            <?php if ($reports_result && $reports_result->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="spiritual-table">
                    <thead>
                        <tr>
                            <th>Content Type</th>
                            <th>Content Preview</th>
                            <th>Reporter</th>
                            <th>Reason</th>
                            <th>Description</th>
                            <th>Reported</th>
                            <th>Status</th>
                            <th>Reviewed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($report = $reports_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="status-badge" style="background: 
                                    <?php 
                                    $type_colors = [
                                        'post' => 'var(--spiritual-blue)',
                                        'comment' => 'var(--spiritual-purple)',
                                        'user' => 'var(--spiritual-gold)',
                                        'message' => '#43e97b'
                                    ];
                                    echo $type_colors[$report['content_type']] ?? '#6c757d';
                                    ?>; color: white;">
                                    <?php echo ucfirst($report['content_type']); ?>
                                </span>
                            </td>
                            <td style="max-width: 200px;">
                                <small style="color: var(--gray); font-style: italic; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($report['content_preview'] ?? 'Content not available'); ?>
                                </small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($report['reporter_name']); ?></strong>
                                <br><small style="color: var(--gray);"><?php echo htmlspecialchars($report['reporter_email']); ?></small>
                            </td>
                            <td>
                                <span class="status-badge" style="background: 
                                    <?php 
                                    $reason_colors = [
                                        'inappropriate' => '#f5576c',
                                        'spam' => '#ff9500',
                                        'harassment' => '#dc3545',
                                        'false_info' => '#6f42c1',
                                        'other' => '#6c757d'
                                    ];
                                    echo $reason_colors[$report['reason']] ?? '#6c757d';
                                    ?>; color: white; font-size: 0.75rem;">
                                    <?php 
                                    $reason_labels = [
                                        'inappropriate' => 'Inappropriate',
                                        'spam' => 'Spam',
                                        'harassment' => 'Harassment',
                                        'false_info' => 'False Info',
                                        'other' => 'Other'
                                    ];
                                    echo $reason_labels[$report['reason']] ?? ucfirst($report['reason']);
                                    ?>
                                </span>
                            </td>
                            <td style="max-width: 250px;">
                                <small style="word-wrap: break-word; display: block;">
                                    <?php echo htmlspecialchars($report['description'] ?? 'No description provided'); ?>
                                </small>
                            </td>
                            <td style="white-space: nowrap;">
                                <?php echo date('M d, Y', strtotime($report['created_at'])); ?>
                                <br><small style="color: var(--gray);"><?php echo date('H:i', strtotime($report['created_at'])); ?></small>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $report['status']; ?>">
                                    <?php echo ucfirst($report['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($report['reviewer_name']): ?>
                                    <small>
                                        <strong><?php echo htmlspecialchars($report['reviewer_name']); ?></strong>
                                        <br><span style="color: var(--gray);"><?php echo date('M d, Y', strtotime($report['reviewed_at'])); ?></span>
                                    </small>
                                <?php else: ?>
                                    <span style="color: var(--gray); font-size: 0.85rem;">Not reviewed</span>
                                <?php endif; ?>
                            </td>
                            <td style="min-width: 200px;">
                                <?php if ($report['status'] === 'pending'): ?>
                                <div style="display: flex; flex-direction: column; gap: 5px;">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                        <button type="submit" name="action" value="resolve" class="btn btn-success btn-sm" onclick="return confirm('Mark this report as resolved?')" style="width: 100%;">
                                            <i class="fas fa-check"></i> Resolve
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                        <button type="submit" name="action" value="dismiss" class="btn btn-secondary btn-sm" onclick="return confirm('Dismiss this report?')" style="width: 100%;">
                                            <i class="fas fa-times"></i> Dismiss
                                        </button>
                                    </form>
                                    <?php if (in_array($report['content_type'], ['post', 'comment'])): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                        <input type="hidden" name="content_type" value="<?php echo $report['content_type']; ?>">
                                        <input type="hidden" name="content_id" value="<?php echo $report['content_id']; ?>">
                                        <button type="submit" name="action" value="delete_content" class="btn btn-danger btn-sm" onclick="return confirm('Delete this content permanently? This action cannot be undone!')" style="width: 100%;">
                                            <i class="fas fa-trash"></i> Delete Content
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <span style="color: var(--gray); font-size: 0.9rem;">
                                    <?php echo $report['status'] === 'resolved' ? '✓ Resolved' : '✗ Dismissed'; ?>
                                </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-shield-alt"></i>
                <h3>No Reports Found</h3>
                <p>No content has been reported yet. This is a good sign!</p>
                <p style="color: var(--gray); font-size: 0.9rem; margin-top: 10px;">
                    When users report inappropriate content, it will appear here for review.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Report Guidelines -->
        <div class="card" style="margin-top: 25px;">
            <div class="section-header">
                <h3>Content Moderation Guidelines</h3>
            </div>
            <div style="padding: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div>
                        <h4 style="color: var(--spiritual-gold); margin-bottom: 15px;">🚩 Report Categories</h4>
                        <ul style="line-height: 1.8; color: var(--dark-color);">
                            <li><strong>Inappropriate Content:</strong> Content not suitable for fellowship</li>
                            <li><strong>Spam:</strong> Irrelevant or promotional content</li>
                            <li><strong>Harassment:</strong> Bullying or offensive behavior</li>
                            <li><strong>False Information:</strong> Misleading or incorrect content</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: var(--spiritual-blue); margin-bottom: 15px;">⚖️ Moderation Actions</h4>
                        <ul style="line-height: 1.8; color: var(--dark-color);">
                            <li><strong>Resolve:</strong> Take appropriate action and close report</li>
                            <li><strong>Dismiss:</strong> Report is unfounded or resolved</li>
                            <li><strong>Escalate:</strong> Forward to higher authority</li>
                            <li><strong>Contact User:</strong> Reach out for clarification</li>
                        </ul>
                    </div>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px; border-left: 4px solid var(--spiritual-purple);">
                    <h4 style="color: var(--spiritual-purple); margin-bottom: 10px;">📋 Best Practices</h4>
                    <ul style="line-height: 1.6; color: var(--dark-color); margin: 0;">
                        <li>Review reports promptly to maintain community trust</li>
                        <li>Consider context and intent when evaluating content</li>
                        <li>Communicate decisions clearly to involved parties</li>
                        <li>Document actions taken for future reference</li>
                        <li>Follow fellowship values and community guidelines</li>
                    </ul>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>