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

$receiver_id = intval($_POST['receiver_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($receiver_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid receiver']);
    exit();
}

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit();
}

// Check if receiver exists
$receiver = $conn->query("SELECT user_id FROM users WHERE user_id = $receiver_id AND status = 'approved'")->fetch_assoc();

if (!$receiver) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Receiver not found']);
    exit();
}

// Insert message
$stmt = $conn->prepare("INSERT INTO private_messages (sender_id, receiver_id, message_text, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $_SESSION['user_id'], $receiver_id, $message);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Message sent successfully',
        'message_id' => $conn->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
?>
