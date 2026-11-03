<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('section_leader')) {
    header('Location: ../home.php');
    exit();
}

$pageTitle = "Submit Section Report - Haramaya Fellowship";

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_title   = clean($_POST['report_title']);
    $report_content = clean($_POST['report_content']);
    $report_date    = clean($_POST['report_date']);
    $report_type    = clean($_POST['report_type']);
    $section_id     = intval($_SESSION['section_id']);
    $submitted_by   = intval($_SESSION['user_id']);

    if (empty($report_title) || empty($report_content) || empty($report_date)) {
        $error = "Please fill all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO section_reports (section_id, submitted_by, report_title, report_content, report_date, report_type, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("iissss", $section_id, $submitted_by, $report_title, $report_content, $report_date, $report_type);

        if ($stmt->execute()) {
            $success = "Report submitted successfully and is pending review.";
        } else {
            $error = "Failed to submit report: " . $conn->error;
        }
    }
}

$section_info = $conn->query("SELECT * FROM sections WHERE section_id = {$_SESSION['section_id']}")->fetch_assoc();

include '../includes/header.php';
?>

<main style="padding: 25px 0;">
    <div class="container" style="max-width: 750px;">
        <div class="dashboard-welcome">
            <h2>📋 Submit Section Report</h2>
            <p>Submit a report for <?php echo htmlspecialchars($section_info['section_name'] ?? 'your section'); ?></p>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success" style="margin: 20px 0;">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <a href="dashboard.php" style="margin-left: 15px;">Back to Dashboard →</a>
        </div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger" style="margin: 20px 0;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="card" style="margin-top: 20px;">
            <form method="POST">
                <div class="form-group">
                    <label>Report Type <span style="color:var(--danger-color)">*</span></label>
                    <select name="report_type" class="form-control" required>
                        <option value="weekly">Weekly Report</option>
                        <option value="monthly">Monthly Report</option>
                        <option value="quarterly">Quarterly Report</option>
                        <option value="special">Special Event Report</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Report Title <span style="color:var(--danger-color)">*</span></label>
                    <input type="text" name="report_title" class="form-control" required
                           placeholder="e.g. Weekly Bible Study Report - Week 5">
                </div>

                <div class="form-group">
                    <label>Report Date <span style="color:var(--danger-color)">*</span></label>
                    <input type="date" name="report_date" class="form-control" required
                           value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Report Content <span style="color:var(--danger-color)">*</span></label>
                    <textarea name="report_content" class="form-control" rows="8" required
                              placeholder="Describe section activities, attendance, highlights, challenges, and prayer requests..."></textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Report
                    </button>
                    <a href="dashboard.php" class="btn" style="background: var(--gray); color: white;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
