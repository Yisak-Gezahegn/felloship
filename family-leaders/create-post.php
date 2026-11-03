<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
requireApproval();
if (!hasMinRole('family_leader')) {
    header('Location: ../home.php');
    exit();
}

$pageTitle = "Create Post - Haramaya Fellowship";

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content   = clean($_POST['content']);
    $post_type = clean($_POST['post_type']);
    $image     = null;

    if (empty(trim($content))) {
        $error = "Post content cannot be empty.";
    } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image = uploadFile($_FILES['image'], '../uploads/posts/');
        }

        $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image, post_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $_SESSION['user_id'], $content, $image, $post_type);

        if ($stmt->execute()) {
            $success = "Post published successfully!";
        } else {
            $error = "Failed to publish post. Please try again.";
        }
    }
}

include '../includes/header.php';
?>

<main style="padding: 25px 0;">
    <div class="container" style="max-width: 700px;">
        <div class="dashboard-welcome">
            <h2>✏️ Create Post</h2>
            <p>Share something with the fellowship community</p>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success" style="margin: 20px 0;">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <a href="../home.php" style="margin-left: 15px;">View in feed →</a>
        </div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger" style="margin: 20px 0;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="card" style="margin-top: 20px;">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Post Type</label>
                    <select name="post_type" class="form-control">
                        <option value="general">General Post</option>
                        <option value="announcement">Announcement</option>
                        <option value="event">Event</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Content <span style="color:var(--danger-color)">*</span></label>
                    <textarea name="content" class="form-control" rows="6" required
                              placeholder="What's on your heart to share with the fellowship?"></textarea>
                </div>

                <div class="form-group">
                    <label>Add Image (optional)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Publish Post
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
