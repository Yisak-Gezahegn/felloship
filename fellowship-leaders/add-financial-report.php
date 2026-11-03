<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('fellowship_leader') && !hasRole('coordinator') && !hasRole('developer')) {
    header('Location: ../home.php');
    exit();
}

$pageTitle = "Add Financial Report - Haramaya Fellowship";

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_type = clean($_POST['report_type']);
    $category    = clean($_POST['category']);
    $description = clean($_POST['description']);
    $amount      = floatval($_POST['amount']);
    $section_id  = intval($_POST['section_id']) ?: null;

    if (empty($report_type) || empty($category) || empty($description) || $amount <= 0) {
        $error = "Please fill all required fields with valid values.";
    } else {
        $stmt = $conn->prepare("INSERT INTO financial_reports (report_type, category, description, amount, section_id, submitted_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssdi i", $report_type, $category, $description, $amount, $section_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $success = "Financial report added successfully!";
        } else {
            $error = "Failed to save report. Please try again.";
        }
    }
}

$sections = $conn->query("SELECT section_id, section_name FROM sections ORDER BY section_name");

include '../includes/header.php';
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>💰 Add Financial Report</h2>
            <p>Record income or expense for the fellowship</p>
        </div>

        <div style="max-width: 650px; margin: 30px auto;">
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <a href="financial-reports.php" style="margin-left: 15px;">View all reports →</a>
            </div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label>Report Type <span style="color:var(--danger-color)">*</span></label>
                        <select name="report_type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Category <span style="color:var(--danger-color)">*</span></label>
                        <select name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="tithe">Tithe / Offering</option>
                            <option value="donation">Donation</option>
                            <option value="fundraising">Fundraising</option>
                            <option value="event">Event</option>
                            <option value="supplies">Supplies</option>
                            <option value="charity">Charity / Outreach</option>
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

                    <div class="form-group">
                        <label>Section (optional)</label>
                        <select name="section_id" class="form-control">
                            <option value="">General / All Sections</option>
                            <?php while ($s = $sections->fetch_assoc()): ?>
                            <option value="<?php echo $s['section_id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 25px;">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Report</button>
                        <a href="financial-reports.php" class="btn" style="background: var(--gray); color: white;"><i class="fas fa-times"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
