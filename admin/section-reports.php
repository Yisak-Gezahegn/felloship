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

// Handle report actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['report_id'])) {
        $report_id = intval($_POST['report_id']);
        $action = $_POST['action'];
        
        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE section_reports SET status = 'approved' WHERE report_id = ?");
            $stmt->bind_param("i", $report_id);
            if ($stmt->execute()) {
                $success_message = "Report approved successfully!";
            } else {
                $error_message = "Failed to approve report.";
            }
        }
    }
}

$pageTitle = "Section Reports - Admin Dashboard";
include '../includes/header.php';

// Get all section reports with section and user info
$reports = $conn->query("
    SELECT sr.*, s.section_name, u.full_name as reporter_name
    FROM section_reports sr 
    JOIN sections s ON sr.section_id = s.section_id 
    JOIN users u ON sr.submitted_by = u.user_id 
    ORDER BY sr.created_at DESC
");

// Get report statistics
$total_reports = $conn->query("SELECT COUNT(*) as count FROM section_reports")->fetch_assoc()['count'];
$pending_reports = $conn->query("SELECT COUNT(*) as count FROM section_reports WHERE status = 'pending'")->fetch_assoc()['count'];
$approved_reports = $conn->query("SELECT COUNT(*) as count FROM section_reports WHERE status = 'approved'")->fetch_assoc()['count'];
$reviewed_reports = $conn->query("SELECT COUNT(*) as count FROM section_reports WHERE status = 'reviewed'")->fetch_assoc()['count'];
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>📊 Section Reports</h2>
            <p>Review and approve section activity reports</p>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin: 25px 0;">
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-blue), var(--spiritual-purple));">
                <i class="fas fa-file-alt"></i>
                <h3><?php echo $total_reports; ?></h3>
                <p>Total Reports</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f);">
                <i class="fas fa-clock"></i>
                <h3><?php echo $pending_reports; ?></h3>
                <p>Pending Review</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                <i class="fas fa-check"></i>
                <h3><?php echo $approved_reports; ?></h3>
                <p>Approved</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f5576c, #f093fb);">
                <i class="fas fa-eye"></i>
                <h3><?php echo $reviewed_reports; ?></h3>
                <p>Reviewed</p>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="card">
            <div class="section-header">
                <h3>All Section Reports</h3>
            </div>

            <?php if ($reports && $reports->num_rows > 0): ?>
            <!-- Desktop Table View -->
            <div style="overflow-x: auto;">
                <table class="spiritual-table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Report Title</th>
                            <th>Submitted By</th>
                            <th>Report Date</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $reports_data = [];
                        while ($report = $reports->fetch_assoc()) {
                            $reports_data[] = $report;
                        }
                        foreach ($reports_data as $report): 
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($report['section_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($report['report_title']); ?></td>
                            <td><?php echo htmlspecialchars($report['reporter_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($report['report_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $report['status']; ?>">
                                    <?php echo ucfirst($report['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="viewReport(<?php echo $report['report_id']; ?>, '<?php echo htmlspecialchars(addslashes($report['report_title'])); ?>', '<?php echo htmlspecialchars(addslashes($report['report_content'])); ?>')">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <?php if ($report['status'] === 'pending'): ?>
                                <form method="POST" style="display: inline; margin-left: 5px;">
                                    <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-sm" onclick="return confirm('Approve this report?')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if ($report['file_attachment']): ?>
                                <a href="../uploads/<?php echo htmlspecialchars($report['file_attachment']); ?>" class="btn btn-secondary btn-sm" target="_blank" style="margin-left: 5px;">
                                    <i class="fas fa-download"></i> File
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <?php foreach ($reports_data as $report): ?>
            <div class="report-mobile-card">
                <h4><?php echo htmlspecialchars($report['report_title']); ?></h4>
                
                <div class="report-mobile-info">
                    <div class="report-mobile-info-item">
                        <label>Section</label>
                        <strong><?php echo htmlspecialchars($report['section_name']); ?></strong>
                    </div>
                    <div class="report-mobile-info-item">
                        <label>Submitted By</label>
                        <strong><?php echo htmlspecialchars($report['reporter_name']); ?></strong>
                    </div>
                    <div class="report-mobile-info-item">
                        <label>Report Date</label>
                        <strong><?php echo date('M d, Y', strtotime($report['report_date'])); ?></strong>
                    </div>
                    <div class="report-mobile-info-item">
                        <label>Status</label>
                        <span class="status-badge status-<?php echo $report['status']; ?>">
                            <?php echo ucfirst($report['status']); ?>
                        </span>
                    </div>
                </div>

                <div style="margin-bottom: 10px;">
                    <label style="display: block; font-size: 0.75rem; color: var(--gray); margin-bottom: 4px;">Submitted At</label>
                    <strong style="font-size: 0.9rem;"><?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?></strong>
                </div>

                <div class="report-mobile-actions">
                    <button class="btn btn-info" onclick="viewReport(<?php echo $report['report_id']; ?>, '<?php echo htmlspecialchars(addslashes($report['report_title'])); ?>', '<?php echo htmlspecialchars(addslashes($report['report_content'])); ?>')">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <?php if ($report['status'] === 'pending'): ?>
                    <form method="POST">
                        <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                        <button type="submit" name="action" value="approve" class="btn btn-success" onclick="return confirm('Approve this report?')">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php if ($report['file_attachment']): ?>
                    <a href="../uploads/<?php echo htmlspecialchars($report['file_attachment']); ?>" class="btn btn-secondary" target="_blank">
                        <i class="fas fa-download"></i> File
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-file-alt"></i>
                <h3>No Reports Found</h3>
                <p>No section reports have been submitted yet</p>
            </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<!-- View Report Modal -->
<div id="viewModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: var(--border-radius); width: 90%; max-width: 700px; max-height: 80vh; overflow-y: auto;">
        <h3 style="margin-bottom: 20px; color: var(--primary-color);">
            <i class="fas fa-file-alt"></i> <span id="reportTitle"></span>
        </h3>
        <div id="reportContent" style="line-height: 1.8; color: var(--dark-color); padding: 20px; background: #f8f9fa; border-radius: 8px; white-space: pre-wrap;">
            <!-- Report content will be loaded here -->
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <button class="btn btn-secondary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<script>
function viewReport(reportId, title, content) {
    document.getElementById('reportTitle').textContent = title;
    document.getElementById('reportContent').textContent = content;
    document.getElementById('viewModal').style.display = 'block';
}

function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('viewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeViewModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>