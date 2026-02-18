<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
requireApproval();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = clean($_POST['content']);
    $post_type = clean($_POST['post_type']);
    $user_id = $_SESSION['user_id'];
    $image = null;
    
    // Check if user can post
    if (!hasMinRole('family_leader')) {
        header('Location: /home.php?error=no_permission');
        exit();
    }
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image = uploadFile($_FILES['image'], '../uploads/posts/');
    }
    
    // Insert post
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image, post_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $content, $image, $post_type);
    
    if ($stmt->execute()) {
        header('Location: /home.php?success=post_created');
    } else {
        header('Location: /home.php?error=post_failed');
    }
} else {
    header('Location: /home.php');
}
exit();
?>
