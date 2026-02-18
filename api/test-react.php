<?php
// Simple test version of react.php with minimal dependencies
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Basic connection test
try {
    require_once '../config/database.php';
    echo json_encode(['step' => 'database_connected', 'success' => true]);
} catch (Exception $e) {
    echo json_encode(['step' => 'database_connection', 'success' => false, 'error' => $e->getMessage()]);
    exit();
}

// Session test
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['step' => 'session_check', 'success' => false, 'error' => 'No user session']);
    exit();
}

// POST data test
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['step' => 'method_check', 'success' => false, 'error' => 'Not POST request']);
    exit();
}

if (!isset($_POST['post_id']) || !isset($_POST['reaction_type'])) {
    echo json_encode(['step' => 'post_data_check', 'success' => false, 'error' => 'Missing POST data', 'post_data' => $_POST]);
    exit();
}

$post_id = intval($_POST['post_id']);
$reaction_type = $_POST['reaction_type'];
$user_id = $_SESSION['user_id'];

// Validation test
if (!$post_id || !in_array($reaction_type, ['like', 'pray', 'love', 'amen'])) {
    echo json_encode(['step' => 'validation', 'success' => false, 'error' => 'Invalid data', 'post_id' => $post_id, 'reaction_type' => $reaction_type]);
    exit();
}

// Database operation test
try {
    // Simple insert/update
    $stmt = $conn->prepare("INSERT INTO post_reactions (post_id, user_id, reaction_type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE reaction_type = ?");
    $stmt->bind_param("iiss", $post_id, $user_id, $reaction_type, $reaction_type);
    
    if ($stmt->execute()) {
        // Get count
        $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM post_reactions WHERE post_id = ? AND reaction_type = ?");
        $count_stmt->bind_param("is", $post_id, $reaction_type);
        $count_stmt->execute();
        $count = $count_stmt->get_result()->fetch_assoc()['count'];
        
        echo json_encode([
            'step' => 'database_operation',
            'success' => true,
            'post_id' => $post_id,
            'reaction_type' => $reaction_type,
            'user_id' => $user_id,
            'count' => $count
        ]);
    } else {
        echo json_encode(['step' => 'database_operation', 'success' => false, 'error' => $stmt->error]);
    }
} catch (Exception $e) {
    echo json_encode(['step' => 'database_operation', 'success' => false, 'error' => $e->getMessage()]);
}
?>