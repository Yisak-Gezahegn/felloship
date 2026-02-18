<?php
session_start();
include '../config/database.php';
include '../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../home.php');
    exit();
}

$group_id = intval($_POST['group_id']);
$message = trim($_POST['message']);

// Check if we have either message or file
if (empty($message) && !isset($_FILES['file'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=empty_message');
    exit();
}

// Check if user is member of the group
$membership_check = $conn->query("SELECT 1 FROM group_members WHERE group_id = $group_id AND user_id = {$_SESSION['user_id']}");
$is_member = $membership_check->num_rows > 0;

if (!$is_member) {
    // If not a member, add them (for coordinator or fellowship leaders)
    if (in_array($_SESSION['role'], ['coordinator', 'fellowship_leader'])) {
        $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id, joined_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $group_id, $_SESSION['user_id']);
        $stmt->execute();
    } else {
        header('Location: ../home.php?error=not_member');
        exit();
    }
}

// Handle file upload
$file_attachment = null;
if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    $upload_dir = '../uploads/group_files/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['file']['name']);
    $new_filename = time() . '_' . $safe_filename;
    $upload_path = $upload_dir . $new_filename;
    
    // Check file size (max 10MB)
    if ($_FILES['file']['size'] > 10 * 1024 * 1024) {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=file_too_large');
        exit();
    }
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
        $file_attachment = $new_filename;
    } else {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=upload_failed');
        exit();
    }
}

// If no message text, use file name as message
if (empty($message) && $file_attachment) {
    $message = 'Shared a file: ' . $_FILES['file']['name'];
}

// Insert message
$stmt = $conn->prepare("INSERT INTO group_messages (group_id, user_id, message_text, file_attachment, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiss", $group_id, $_SESSION['user_id'], $message, $file_attachment);

if ($stmt->execute()) {
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?success=message_sent');
} else {
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=send_failed');
}
exit();
?>