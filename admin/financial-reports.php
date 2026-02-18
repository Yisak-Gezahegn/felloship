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

$pageTitle = "Financial Reports - Admin Dashboard";
include '../includes/header.php';

// Get financial reports (if table exists)
$financial_reports = null;
$table_exists = false;

// Check if financial_reports table exists
$table_check = $conn->query("SHOW TABLES LIKE 'financial_reports'");
if ($table_check && $table_check->num_rows > 0) {
    $table_exists = true;
    $financial_reports = $conn->query("
        SELECT fr.*, u.full_name as reporter_name, s.section_name
        FROM financial_reports fr 
        JOIN users u ON fr.user_id = u.user_id 
        LEFT JOIN sections s ON fr.section_id = s.section_id 
        ORDER BY fr.report_date DESC
    ");
}

// Simulated financial data for demonstration
$simulated_data = [
    'total_income' => 15750.00,
    'total_expenses' => 12300.00,
    'net_balance' => 3450.00,
    'monthly_income' => 2500.00,
    'monthly_expenses' => 1800.00,
    'categories' => [
        'Tithes & Offerings' => 8500.00,
        'Special Collections' => 3250.00,
        'Events & Programs' => 2000.00,
        'Donations' => 2000.00
    ],
    'expenses' => [
        'Ministry Programs' => 4500.00,
        'Facility Maintenance' => 2800.00,
        'Community Outreach' => 2200.00,
        'Administrative' => 1500.00,
        'Equipment' => 1300.00
    ]
];
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>💰 Financial Reports</h2>
            <p>Monitor fellowship finances and stewardship</p>
        </div>

        <!-- Financial Overview -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 25px 0;">
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                <i class="fas fa-arrow-up"></i>
                <h3>$<?php echo number_format($simulated_data['total_income'], 2); ?></h3>
                <p>Total Income</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f5576c, #f093fb);">
                <i class="fas fa-arrow-down"></i>
                <h3>$<?php echo number_format($simulated_data['total_expenses'], 2); ?></h3>
                <p>Total Expenses</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f);">
                <i class="fas fa-balance-scale"></i>
                <h3>$<?php echo number_format($simulated_data['net_balance'], 2); ?></h3>
                <p>Net Balance</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-blue), var(--spiritual-purple));">
                <i class="fas fa-calendar-month"></i>
                <h3>$<?php echo number_format($simulated_data['monthly_income'], 2); ?></h3>
                <p>Monthly Income</p>
            </div>
        </div>

        <!-- Income Breakdown -->
        <div class="card" style="margin-bottom: 25px;">
            <div class="section-header">
                <h3>Income Breakdown</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; padding: 20px;">
                <?php foreach ($simulated_data['categories'] as $category => $amount): ?>
                <div style="background: #f8f9fa; padding: 20px; border-radius: var(--border-radius); border-left: 4px solid var(--spiritual-gold);">
                    <h4 style="color: var(--spiritual-gold); margin-bottom: 10px;"><?php echo $category; ?></h4>
                    <p style="font-size: 1.5rem; font-weight: bold; color: var(--dark-color); margin: 0;">
                        $<?php echo number_format($amount, 2); ?>
                    </p>
                    <p style="font-size: 0.9rem; color: var(--gray); margin: 5px 0 0 0;">
                        <?php echo round(($amount / $simulated_data['total_income']) * 100, 1); ?>% of total
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Expense Breakdown -->
        <div class="card" style="margin-bottom: 25px;">
            <div class="section-header">
                <h3>Expense Breakdown</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; padding: 20px;">
                <?php foreach ($simulated_data['expenses'] as $category => $amount): ?>
                <div style="background: #f8f9fa; padding: 20px; border-radius: var(--border-radius); border-left: 4px solid #f5576c;">
                    <h4 style="color: #f5576c; margin-bottom: 10px;"><?php echo $category; ?></h4>
                    <p style="font-size: 1.5rem; font-weight: bold; color: var(--dark-color); margin: 0;">
                        $<?php echo number_format($amount, 2); ?>
                    </p>
                    <p style="font-size: 0.9rem; color: var(--gray); margin: 5px 0 0 0;">
                        <?php echo round(($amount / $simulated_data['total_expenses']) * 100, 1); ?>% of total
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Financial Reports Table -->
        <div class="card">
            <div class="section-header">
                <h3>Financial Reports</h3>
            </div>

            <?php if ($table_exists && $financial_reports && $financial_reports->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="spiritual-table">
                    <thead>
                        <tr>
                            <th>Report Date</th>
                            <th>Reporter</th>
                            <th>Section</th>
                            <th>Income</th>
                            <th>Expenses</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($report = $financial_reports->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($report['report_date'])); ?></td>
                            <td><?php echo htmlspecialchars($report['reporter_name']); ?></td>
                            <td><?php echo htmlspecialchars($report['section_name'] ?? 'General'); ?></td>
                            <td style="color: #43e97b; font-weight: bold;">
                                $<?php echo number_format($report['total_income'], 2); ?>
                            </td>
                            <td style="color: #f5576c; font-weight: bold;">
                                $<?php echo number_format($report['total_expenses'], 2); ?>
                            </td>
                            <td style="color: var(--spiritual-gold); font-weight: bold;">
                                $<?php echo number_format($report['total_income'] - $report['total_expenses'], 2); ?>
                            </td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="viewFinancialReport(<?php echo $report['report_id']; ?>)">
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
                <i class="fas fa-chart-line"></i>
                <h3>No Financial Reports</h3>
                <p>Financial reports will appear here when submitted by section leaders</p>
                <?php if (!$table_exists): ?>
                <p style="color: var(--gray); font-size: 0.9rem; margin-top: 10px;">
                    Note: Financial reports table not found. This feature requires database setup.
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Financial Guidelines -->
        <div class="card" style="margin-top: 25px;">
            <div class="section-header">
                <h3>Financial Stewardship Guidelines</h3>
            </div>
            <div style="padding: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div>
                        <h4 style="color: var(--spiritual-gold); margin-bottom: 15px;">💰 Income Management</h4>
                        <ul style="line-height: 1.8; color: var(--dark-color);">
                            <li>Record all tithes and offerings accurately</li>
                            <li>Track special collections separately</li>
                            <li>Document all donation sources</li>
                            <li>Maintain transparent records</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: var(--spiritual-blue); margin-bottom: 15px;">📊 Expense Tracking</h4>
                        <ul style="line-height: 1.8; color: var(--dark-color);">
                            <li>Categorize all expenses properly</li>
                            <li>Require receipts for all purchases</li>
                            <li>Get approval for major expenses</li>
                            <li>Review monthly spending patterns</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: var(--spiritual-purple); margin-bottom: 15px;">📋 Reporting Standards</h4>
                        <ul style="line-height: 1.8; color: var(--dark-color);">
                            <li>Submit monthly financial reports</li>
                            <li>Include supporting documentation</li>
                            <li>Reconcile bank statements</li>
                            <li>Maintain audit trail</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: #43e97b; margin-bottom: 15px;">🔒 Financial Integrity</h4>
                        <ul style="line-height: 1.8; color: var(--dark-color);">
                            <li>Follow biblical stewardship principles</li>
                            <li>Ensure multiple oversight levels</li>
                            <li>Practice financial transparency</li>
                            <li>Regular financial reviews</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<!-- View Financial Report Modal -->
<div id="financialModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: var(--border-radius); width: 90%; max-width: 700px; max-height: 80vh; overflow-y: auto;">
        <h3 style="margin-bottom: 20px;">Financial Report Details</h3>
        <div id="financialContent">
            <!-- Financial report content will be loaded here -->
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <button class="btn btn-secondary" onclick="closeFinancialModal()">Close</button>
        </div>
    </div>
</div>

<script>
function viewFinancialReport(reportId) {
    // In a real system, you would fetch detailed financial report via AJAX
    document.getElementById('financialContent').innerHTML = `
        <div style="text-align: center; padding: 40px;">
            <i class="fas fa-chart-bar" style="font-size: 3rem; color: var(--spiritual-gold); margin-bottom: 20px;"></i>
            <h4>Financial Report #${reportId}</h4>
            <p style="color: var(--gray); margin-bottom: 20px;">Detailed financial report would be displayed here.</p>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 20px 0; text-align: left;">
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                    <h5 style="color: #43e97b; margin-bottom: 10px;">Income Details</h5>
                    <p style="margin: 5px 0;">Tithes: $1,200.00</p>
                    <p style="margin: 5px 0;">Offerings: $800.00</p>
                    <p style="margin: 5px 0;">Special: $300.00</p>
                </div>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                    <h5 style="color: #f5576c; margin-bottom: 10px;">Expense Details</h5>
                    <p style="margin: 5px 0;">Programs: $600.00</p>
                    <p style="margin: 5px 0;">Maintenance: $400.00</p>
                    <p style="margin: 5px 0;">Outreach: $200.00</p>
                </div>
            </div>
        </div>
    `;
    document.getElementById('financialModal').style.display = 'block';
}

function closeFinancialModal() {
    document.getElementById('financialModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('financialModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeFinancialModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>