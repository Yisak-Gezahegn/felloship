<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$message_id = intval($_POST['message_id'] ?? 0);

if ($message_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid message ID']);
    exit();
}

// Check if message exists and belongs to current user
$stmt = $conn->prepare("SELECT gm.*, g.created_by as group_creator 
                        FROM group_messages gm 
                        JOIN groups g ON gm.group_id = g.group_id 
                        WHERE gm.message_id = ? AND gm.user_id = ?");
$stmt->bind_param("ii", $message_id, $_SESSION['user_id']);
$stmt->execute();
$message = $stmt->get_result()->fetch_assoc();

if (!$message) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Message not found or you do not have permission to delete it']);
    exit();
}

// Delete the message
$delete_stmt = $conn->prepare("DELETE FROM group_messages WHERE message_id = ? AND user_id = ?");
$delete_stmt->bind_param("ii", $message_id, $_SESSION['user_id']);

if ($delete_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Message deleted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete message']);
}
?>