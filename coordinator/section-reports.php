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

$success = '';
$error = '';

// Handle report approval/rejection BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['report_id'])) {
    $report_id = intval($_POST['report_id']);
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE section_reports SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE report_id = ?");
        $stmt->bind_param("ii", $_SESSION['user_id'], $report_id);
        
        if ($stmt->execute()) {
            $success = "Report approved successfully!";
        } else {
            $error = "Failed to approve report.";
        }
    } elseif ($action === 'reject') {
        $comment = trim($_POST['comment'] ?? '');
        $stmt = $conn->prepare("UPDATE section_reports SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW(), review_comment = ? WHERE report_id = ?");
        $stmt->bind_param("isi", $_SESSION['user_id'], $comment, $report_id);
        
        if ($stmt->execute()) {
            $success = "Report rejected and feedback sent.";
        } else {
            $error = "Failed to reject report.";
        }
    }
}

$pageTitle = "Section Reports - Haramaya Fellowship";
include '../includes/header.php';

// Get all section reports
$reports = $conn->query("SELECT sr.*, s.section_name, u.full_name 
                         FROM section_reports sr
                         JOIN sections s ON sr.section_id = s.section_id
                         JOIN users u ON sr.submitted_by = u.user_id
                         ORDER BY sr.created_at DESC");

$total_reports = $reports->num_rows;
$pending_reports = $conn->query("SELECT COUNT(*) as count FROM section_reports WHERE status = 'pending'")->fetch_assoc()['count'];
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>📋 Section Reports</h2>
            <p>Review and manage section activity reports</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-file-alt"></i> <?php echo $total_reports; ?> Total Reports | 
                <i class="fas fa-clock"></i> <?php echo $pending_reports; ?> Pending Review
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
                    <li><a href="approve-leaders.php"><i class="fas fa-user-check"></i> Approve Leaders</a></li>
                    <li><a href="section-reports.php" class="active"><i class="fas fa-file-alt"></i> Section Reports</a></li>
                    <li><a href="create-post.php"><i class="fas fa-plus"></i> Create Post/Event</a></li>
                    <li><a href="search-members.php"><i class="fas fa-search"></i> Search Members</a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                </ul>
            </aside>
            
            <div class="main-content">
                <!-- Statistics -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px;">
                        <i class="fas fa-file-alt" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $total_reports; ?></h3>
                        <p>Total Reports</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 20px;">
                        <i class="fas fa-clock" style="font-size: 2rem;"></i>
                        <h3 style="font-size: 1.8rem;"><?php echo $pending_reports; ?></h3>
                        <p>Pending Review</p>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: var(--border-radius);">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 20px; padding: 15px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: var(--border-radius);">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="section-header">
                        <h3>All Section Reports</h3>
                    </div>

                    <?php if ($total_reports > 0): ?>
                    <div style="overflow-x: auto;">
                        <table class="spiritual-table">
                            <thead>
                                <tr>
                                    <th>Section</th>
                                    <th>Report Title</th>
                                    <th>Report Date</th>
                                    <th>Submitted By</th>
                                    <th>Status</th>
                                    <th>Submitted On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($report = $reports->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($report['section_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($report['report_title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($report['report_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($report['full_name']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $report['status']; ?>">
                                            <?php echo ucfirst($report['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($report['created_at'])); ?></td>
                                    <td>
                                        <button onclick="viewReport(<?php echo $report['report_id']; ?>)" 
                                                class="btn btn-primary" 
                                                style="padding: 6px 12px; font-size: 0.85rem;">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <h3>No Reports Yet</h3>
                        <p>Section reports will appear here when submitted</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Report Details Modal -->
<div id="reportModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: var(--border-radius); max-width: 700px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: var(--shadow-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: var(--primary-color); margin: 0;">Report Details</h3>
            <button onclick="closeReportModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray);">×</button>
        </div>
        <div id="reportDetails">
            <div class="spiritual-loader"></div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: var(--border-radius); max-width: 500px; width: 90%; box-shadow: var(--shadow-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: var(--danger-color); margin: 0;">Reject Report</h3>
            <button onclick="closeRejectionModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray);">×</button>
        </div>
        <form method="POST" id="rejectionForm">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="report_id" id="rejectReportId">
            <div class="form-group">
                <label for="comment">Reason for Rejection (Optional):</label>
                <textarea name="comment" id="comment" rows="4" class="form-control" 
                          placeholder="Provide feedback to help improve the report..."></textarea>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeRejectionModal()" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                <button type="submit" class="btn btn-danger" style="flex: 1;">
                    <i class="fas fa-times"></i> Reject Report
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentReportId = null;

function viewReport(reportId) {
    currentReportId = reportId;
    document.getElementById('reportModal').style.display = 'flex';
    document.getElementById('reportDetails').innerHTML = '<div class="spiritual-loader"></div>';
    
    // Load report details via AJAX
    fetch(`../api/get-report-details.php?report_id=${reportId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const report = data.report;
                document.getElementById('reportDetails').innerHTML = `
                    <div class="info-box">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                            <div>
                                <strong>Section:</strong> ${report.section_name}<br>
                                <strong>Report Title:</strong> ${report.report_title}<br>
                                <strong>Report Date:</strong> ${new Date(report.report_date).toLocaleDateString()}
                            </div>
                            <div>
                                <strong>Submitted By:</strong> ${report.full_name}<br>
                                <strong>Status:</strong> <span class="status-badge status-${report.status}">${report.status.charAt(0).toUpperCase() + report.status.slice(1)}</span><br>
                                <strong>Submitted:</strong> ${new Date(report.created_at).toLocaleDateString()}
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <strong>Report Content:</strong>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 10px; white-space: pre-wrap;">${report.report_content || 'No content provided'}</div>
                        </div>
                        
                        ${report.activities ? `
                        <div style="margin-bottom: 20px;">
                            <strong>Activities:</strong>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 10px; white-space: pre-wrap;">${report.activities}</div>
                        </div>
                        ` : ''}
                        
                        ${report.attendance_count ? `
                        <div style="margin-bottom: 20px;">
                            <strong>Attendance:</strong> ${report.attendance_count} members
                        </div>
                        ` : ''}
                        
                        ${report.financial_summary ? `
                        <div style="margin-bottom: 20px;">
                            <strong>Financial Summary:</strong>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 10px; white-space: pre-wrap;">${report.financial_summary}</div>
                        </div>
                        ` : ''}
                        
                        ${report.challenges ? `
                        <div style="margin-bottom: 20px;">
                            <strong>Challenges:</strong>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 10px; white-space: pre-wrap;">${report.challenges}</div>
                        </div>
                        ` : ''}
                        
                        ${report.future_plans ? `
                        <div style="margin-bottom: 20px;">
                            <strong>Future Plans:</strong>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 10px; white-space: pre-wrap;">${report.future_plans}</div>
                        </div>
                        ` : ''}
                        
                        ${report.status === 'pending' ? `
                        <div style="margin-top: 25px; display: flex; gap: 10px;">
                            <form method="POST" style="flex: 1;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="report_id" value="${reportId}">
                                <button type="submit" class="btn btn-success" style="width: 100%;">
                                    <i class="fas fa-check"></i> Approve Report
                                </button>
                            </form>
                            <button onclick="showRejectionModal(${reportId})" class="btn btn-danger" style="flex: 1;">
                                <i class="fas fa-times"></i> Request Revision
                            </button>
                        </div>
                        ` : ''}
                        
                        ${report.review_comment ? `
                        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px;">
                            <strong>Review Comment:</strong>
                            <div style="margin-top: 10px;">${report.review_comment}</div>
                        </div>
                        ` : ''}
                    </div>
                `;
            } else {
                document.getElementById('reportDetails').innerHTML = `
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> Failed to load report details.
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('reportDetails').innerHTML = `
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> Error loading report details.
                </div>
            `;
        });
}

function closeReportModal() {
    document.getElementById('reportModal').style.display = 'none';
    currentReportId = null;
}

function showRejectionModal(reportId) {
    document.getElementById('rejectReportId').value = reportId;
    document.getElementById('rejectionModal').style.display = 'flex';
}

function closeRejectionModal() {
    document.getElementById('rejectionModal').style.display = 'none';
    document.getElementById('comment').value = '';
}

// Close modals when clicking outside
document.getElementById('reportModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReportModal();
    }
});

document.getElementById('rejectionModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectionModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
