<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
    echo '<p style="color: var(--gray);">No comments yet.</p>';
    exit();
}

$post_id = intval($_GET['post_id']);

// Get comments for the post
$stmt = $conn->prepare("
    SELECT c.*, u.full_name, u.profile_picture 
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
    LIMIT 50
");

$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<p style="color: var(--gray); font-size: 0.9rem; text-align: center; padding: 10px;">No comments yet. Be the first to comment!</p>';
    exit();
}

while ($comment = $result->fetch_assoc()) {
    $profile_pic_path = !empty($comment['profile_picture']) && $comment['profile_picture'] !== 'default.jpg' && file_exists("../uploads/profiles/{$comment['profile_picture']}")
        ? "uploads/profiles/{$comment['profile_picture']}" 
        : null;
    
    echo '<div style="display: flex; gap: 10px; margin-bottom: 12px; padding: 8px; background: #f8f9fa; border-radius: 8px;">';
    
    if ($profile_pic_path) {
        echo '<img src="' . $profile_pic_path . '" alt="' . htmlspecialchars($comment['full_name']) . '" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">';
    } else {
        echo '<div class="profile-pic-default" style="width: 30px; height: 30px; flex-shrink: 0;"><i class="fas fa-user" style="font-size: 0.9rem;"></i></div>';
    }
    
    echo '<div style="flex: 1;">';
    echo '<div style="font-weight: bold; font-size: 0.9rem; color: var(--primary-color);">' . htmlspecialchars($comment['full_name']) . '</div>';
    echo '<div style="font-size: 0.85rem; color: var(--dark-color); margin: 2px 0;">' . nl2br(htmlspecialchars($comment['comment_text'])) . '</div>';
    echo '<div style="font-size: 0.75rem; color: var(--gray);">' . timeAgo($comment['created_at']) . '</div>';
    echo '</div>';
    echo '</div>';
}
?>