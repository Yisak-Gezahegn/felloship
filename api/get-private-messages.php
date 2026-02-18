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

$user_id = intval($_GET['user_id'] ?? 0);

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

// Get messages between current user and specified user
$messages = $conn->query("SELECT * FROM private_messages 
                          WHERE (sender_id = {$_SESSION['user_id']} AND receiver_id = $user_id) 
                             OR (sender_id = $user_id AND receiver_id = {$_SESSION['user_id']})
                          ORDER BY created_at ASC");

$messages_array = [];
while ($msg = $messages->fetch_assoc()) {
    $messages_array[] = $msg;
}

// Mark messages as read
$conn->query("UPDATE private_messages 
              SET is_read = 1 
              WHERE sender_id = $user_id 
              AND receiver_id = {$_SESSION['user_id']} 
              AND is_read = 0");

echo json_encode([
    'success' => true,
    'messages' => $messages_array
]);
?>
