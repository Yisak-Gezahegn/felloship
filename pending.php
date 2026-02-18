<?php
$pageTitle = "Pending Approval - Haramaya Fellowship";
include 'includes/header.php';

requireLogin();

if ($_SESSION['status'] === 'approved') {
    header('Location: home.php');
    exit();
}
?>

<main style="padding: 60px 0; min-height: 80vh;">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto; text-align: center;">
            <div class="card">
                <i class="fas fa-clock" style="font-size: 5rem; color: var(--secondary-color); margin-bottom: 20px;"></i>
                <h2 style="margin-bottom: 20px;">Account Pending Approval</h2>
                <p style="font-size: 1.1rem; color: var(--gray); margin-bottom: 30px;">
                    Thank you for registering, <?php echo $_SESSION['full_name']; ?>!
                </p>
                <p style="margin-bottom: 20px;">
                    Your account is currently pending approval from the fellowship administrators. 
                    You will receive a notification once your account has been approved.
                </p>
                <p style="color: var(--gray);">
                    This usually takes 24-48 hours. Please check back later.
                </p>
                <div style="margin-top: 30px;">
                    <a href="logout.php" class="btn btn-primary">Logout</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
