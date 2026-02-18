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

$pageTitle = "Financial Reports - Haramaya Fellowship";
include '../includes/header.php';

// Get financial summary
$total_income = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM financial_reports WHERE report_type = 'income'")->fetch_assoc()['total'];
$total_expense = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM financial_reports WHERE report_type = 'expense'")->fetch_assoc()['total'];
$balance = $total_income - $total_expense;

// Get recent reports
$reports = $conn->query("SELECT fr.*, u.full_name, s.section_name 
                         FROM financial_reports fr
                         LEFT JOIN users u ON fr.submitted_by = u.user_id
                         LEFT JOIN sections s ON fr.section_id = s.section_id
                         ORDER BY fr.created_at DESC
                         LIMIT 20");
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>💰 Financial Reports</h2>
            <p>Track fellowship finances and manage budgets</p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-chart-line"></i> Current Balance: <strong><?php echo number_format($balance, 2); ?> Birr</strong>
            </p>
        </div>

        <div class="dashboard">
            <aside class="sidebar">
                <h3>📊 Leader Panel</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                    <li><a href="create-post.php"><i class="fas fa-plus"></i> Create Post</a></li>
                    <li><a href="my-groups.php"><i class="fas fa-users"></i> My Groups</a></li>
                    <li><a href="create-group.php"><i class="fas fa-plus-circle"></i> Create Group</a></li>
                    <li><a href="../messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="financial-reports.php" class="active"><i class="fas fa-dollar-sign"></i> Financial Reports</a></li>
                    <li><a href="manage-users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </aside>
            
            <div class="main-content">
                <!-- Financial Summary -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px;">
                    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-arrow-up"></i>
                        <h3><?php echo number_format($total_income, 2); ?></h3>
                        <p>Total Income (Birr)</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);">
                        <i class="fas fa-arrow-down"></i>
                        <h3><?php echo number_format($total_expense, 2); ?></h3>
                        <p>Total Expenses (Birr)</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-wallet"></i>
                        <h3><?php echo number_format($balance, 2); ?></h3>
                        <p>Current Balance (Birr)</p>
                    </div>
                </div>

                <!-- Add New Report Button -->
                <div style="margin-bottom: 20px;">
                    <a href="add-financial-report.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Add New Report
                    </a>
                </div>

                <!-- Reports Table -->
                <div class="card">
                    <div class="section-header">
                        <h3>Recent Financial Reports</h3>
                    </div>

                    <?php if ($reports->num_rows > 0): ?>
                    <div style="overflow-x: auto;">
                        <table class="spiritual-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Section</th>
                                    <th>Amount</th>
                                    <th>Submitted By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($report = $reports->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($report['created_at'])); ?></td>
                                    <td>
                                        <span class="status-badge" style="background: <?php echo $report['report_type'] === 'income' ? 'var(--success-color)' : 'var(--danger-color)'; ?>">
                                            <?php echo ucfirst($report['report_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo ucfirst($report['category']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($report['description'], 0, 50)) . '...'; ?></td>
                                    <td><?php echo $report['section_name'] ?? 'General'; ?></td>
                                    <td style="font-weight: 600; color: <?php echo $report['report_type'] === 'income' ? 'var(--success-color)' : 'var(--danger-color)'; ?>">
                                        <?php echo $report['report_type'] === 'income' ? '+' : '-'; ?><?php echo number_format($report['amount'], 2); ?> Birr
                                    </td>
                                    <td><?php echo htmlspecialchars($report['full_name']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <h3>No Financial Reports</h3>
                        <p>Start tracking finances by adding your first report</p>
                        <a href="add-financial-report.php" class="btn btn-primary" style="margin-top: 15px;">
                            <i class="fas fa-plus-circle"></i> Add Report
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
