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

// Check if user is a member
$is_member = $conn->query("SELECT 1 FROM group_members WHERE group_id = $group_id AND user_id = {$_SESSION['user_id']}")->num_rows;

if ($is_member == 0) {
    header('Location: ../groups.php?error=not_member');
    exit();
}

// Check if user is the group creator
$group = $conn->query("SELECT * FROM groups WHERE group_id = $group_id")->fetch_assoc();

if ($group['created_by'] == $_SESSION['user_id']) {
    header('Location: ../groups.php?error=creator_cannot_leave');
    exit();
}

// Remove user from group
$stmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
$stmt->bind_param("ii", $group_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    header('Location: ../groups.php?success=left');
} else {
    header('Location: ../groups.php?error=leave_failed');
}
exit();
?>
