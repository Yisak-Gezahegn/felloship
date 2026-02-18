<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../groups.php');
    exit();
}

$group_id = intval($_POST['group_id'] ?? 0);

if ($group_id <= 0) {
    header('Location: ../groups.php?error=invalid_group');
    exit();
}

// Check if group exists and is public
$group = $conn->query("SELECT * FROM groups WHERE group_id = $group_id AND group_type = 'public'")->fetch_assoc();

if (!$group) {
    header('Location: ../groups.php?error=group_not_found');
    exit();
}

// Check if already a member
$is_member = $conn->query("SELECT 1 FROM group_members WHERE group_id = $group_id AND user_id = {$_SESSION['user_id']}")->num_rows;

if ($is_member > 0) {
    header('Location: ../groups.php?error=already_member');
    exit();
}

// Add user to group
$stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id, joined_at) VALUES (?, ?, NOW())");
$stmt->bind_param("ii", $group_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    header('Location: ../groups.php?success=joined');
} else {
    header('Location: ../groups.php?error=join_failed');
}
exit();
?>
