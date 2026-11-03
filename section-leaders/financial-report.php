<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('section_leader')) {
    header('Location: ../home.php');
    exit();
}

$pageTitle = "Section Financial Report - Haramaya Fellowship";

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_type = clean($_POST['report_type']);
    $category    = clean($_POST['category']);
    $description = clean($_POST['description']);
    $amount      = floatval($_POST['amount']);
    $section_id  = intval($_SESSION['section_id']);
    $submitted_by = intval($_SESSION['user_id']);

    if (empty($report_type) || empty($category) || empty($description) || $amount <= 0) {
        $error = "Please fill all required fields with valid values.";
    } else {
        $stmt = $conn->prepare("INSERT INTO financial_reports (report_type, category, description, amount, section_id, submitted_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssdii", $report_type, $category, $description, $amount, $section_id, $submitted_by);
        if ($stmt->execute()) {
            $success = "Financial report submitted successfully!";
        } else {
            $error = "Failed to submit report. Please try again.";
        }
    }
}

// Get section's own financial records
$section_id = intval($_SESSION['section_id']);
$total_income  = $conn->query("SELECT COALESCE(SUM(amount),0) as t FROM financial_reports WHERE section_id=$section_id AND report_type='income'")->fetch_assoc()['t'];
$total_expense = $conn->query("SELECT COALESCE(SUM(amount),0) as t FROM financial_reports WHERE section_id=$section_id AND report_type='expense'")->fetch_assoc()['t'];
$balance = $total_income - $total_expense;

$reports = $conn->query("SELECT * FROM financial_reports WHERE section_id=$section_id ORDER BY created_at DESC LIMIT 20");
$section_info = $conn->query("SELECT section_name FROM sections WHERE section_id=$section_id")->fetch_assoc();

include '../includes/header.php';
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>💰 Section Financial Report</h2>
            <p><?php echo htmlspecialchars($section_info['section_name'] ?? ''); ?> — Track income and expenses</p>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success" style="margin: 20px 0;"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger" style="margin: 20px 0;"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Summary -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin: 25px 0;">
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                <i class="fas fa-arrow-up"></i>
                <h3><?php echo number_format($total_income, 2); ?></h3>
                <p>Total Income (Birr)</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #ff6b6b, #ee5a6f);">
                <i class="fas fa-arrow-down"></i>
                <h3><?php echo number_format($total_expense, 2); ?></h3>
                <p>Total Expenses (Birr)</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="fas fa-wallet"></i>
                <h3><?php echo number_format($balance, 2); ?></h3>
                <p>Balance (Birr)</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 25px;">
            <!-- Records Table -->
            <div class="card">
                <div class="section-header"><h3>Financial Records</h3></div>
                <?php if ($reports->num_rows > 0): ?>
                <table class="spiritual-table">
                    <thead>
                        <tr><th>Date</th><th>Type</th><th>Category</th><th>Description</th><th>Amount</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($r = $reports->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
                            <td><span class="status-badge" style="background:<?php echo $r['report_type']==='income'?'var(--success-color)':'var(--danger-color)'; ?>">
                                <?php echo ucfirst($r['report_type']); ?></span></td>
                            <td><?php echo ucfirst($r['category']); ?></td>
                            <td><?php echo htmlspecialchars(substr($r['description'],0,50)); ?></td>
                            <td style="font-weight:600; color:<?php echo $r['report_type']==='income'?'var(--success-color)':'var(--danger-color)'; ?>">
                                <?php echo ($r['report_type']==='income'?'+':'-') . number_format($r['amount'],2); ?> Birr
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <h3>No Records Yet</h3>
                    <p>Add your first financial record using the form</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Add Record Form -->
            <div class="card">
                <div class="section-header"><h3>Add Record</h3></div>
                <form method="POST" style="padding: 10px 0;">
                    <div class="form-group">
                        <label>Type <span style="color:var(--danger-color)">*</span></label>
                        <select name="report_type" class="form-control" required>
                            <option value="">Select</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Category <span style="color:var(--danger-color)">*</span></label>
                        <select name="category" class="form-control" required>
                            <option value="">Select</option>
                            <option value="tithe">Tithe / Offering</option>
                            <option value="donation">Donation</option>
                            <option value="fundraising">Fundraising</option>
                            <option value="event">Event</option>
                            <option value="supplies">Supplies</option>
                            <option value="charity">Charity</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount (Birr) <span style="color:var(--danger-color)">*</span></label>
                        <input type="number" name="amount" class="form-control" min="0.01" step="0.01" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Description <span style="color:var(--danger-color)">*</span></label>
                        <textarea name="description" class="form-control" rows="3" required placeholder="Describe this transaction..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        <i class="fas fa-save"></i> Save Record
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
@media (max-width: 768px) {
    main .container > div[style*="grid-template-columns: 1fr 380px"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
