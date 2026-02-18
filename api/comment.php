<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    requireLogin();

    // Handle both JSON and form data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['post_id'])) {
            // Form data
            $post_id = intval($_POST['post_id']);
            $comment_text = clean($_POST['comment']);
        } else {
            // JSON data
            $data = json_decode(file_get_contents('php://input'), true);
            $post_id = intval($data['post_id']);
            $comment_text = clean($data['comment_text']);
        }
        
        $user_id = $_SESSION['user_id'];

        // Validate inputs
        if (!$post_id || empty(trim($comment_text))) {
            echo json_encode(['success' => false, 'error' => 'Invalid input', 'debug' => ['post_id' => $post_id, 'comment_text' => $comment_text]]);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $post_id, $user_id, $comment_text);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'comment_id' => $stmt->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to comment', 'sql_error' => $stmt->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
}
?>
