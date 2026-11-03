<?php
// Production: suppress error display, log instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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
            $reaction_type = $_POST['reaction_type'];
        } else {
            // JSON data
            $data = json_decode(file_get_contents('php://input'), true);
            $post_id = intval($data['post_id']);
            $reaction_type = $data['reaction_type'];
        }
        
        $user_id = $_SESSION['user_id'];

        // Validate inputs
        if (!$post_id || !in_array($reaction_type, ['like', 'love', 'pray', 'amen'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid input', 'debug' => ['post_id' => $post_id, 'reaction_type' => $reaction_type]]);
            exit();
        }

        // Check if already reacted
        $stmt = $conn->prepare("SELECT reaction_id, reaction_type FROM post_reactions WHERE post_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $already_reacted = false;
        $previous_reaction = null;
        $action = 'added';
        
        if ($result->num_rows > 0) {
            $existing = $result->fetch_assoc();
            $previous_reaction = $existing['reaction_type'];
            $already_reacted = true;
            
            if ($previous_reaction === $reaction_type) {
                // Same reaction - remove it
                $stmt = $conn->prepare("DELETE FROM post_reactions WHERE post_id = ? AND user_id = ?");
                $stmt->bind_param("ii", $post_id, $user_id);
                $action = 'removed';
            } else {
                // Different reaction - update it
                $stmt = $conn->prepare("UPDATE post_reactions SET reaction_type = ? WHERE post_id = ? AND user_id = ?");
                $stmt->bind_param("sii", $reaction_type, $post_id, $user_id);
                $action = 'updated';
            }
        } else {
            // Insert new reaction
            $stmt = $conn->prepare("INSERT INTO post_reactions (post_id, user_id, reaction_type) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $post_id, $user_id, $reaction_type);
            $action = 'added';
        }

        if ($stmt->execute()) {
            // Get updated reaction counts
            $counts_stmt = $conn->prepare("
                SELECT reaction_type, COUNT(*) as count 
                FROM post_reactions 
                WHERE post_id = ? 
                GROUP BY reaction_type
            ");
            $counts_stmt->bind_param("i", $post_id);
            $counts_stmt->execute();
            $counts_result = $counts_stmt->get_result();
            
            $reaction_counts = [];
            while ($count_row = $counts_result->fetch_assoc()) {
                $reaction_counts[$count_row['reaction_type']] = intval($count_row['count']);
            }
            
            // Check current user's reaction
            $user_reaction_stmt = $conn->prepare("SELECT reaction_type FROM post_reactions WHERE post_id = ? AND user_id = ?");
            $user_reaction_stmt->bind_param("ii", $post_id, $user_id);
            $user_reaction_stmt->execute();
            $user_reaction_result = $user_reaction_stmt->get_result();
            $user_current_reaction = $user_reaction_result->num_rows > 0 ? $user_reaction_result->fetch_assoc()['reaction_type'] : null;
            
            echo json_encode([
                'success' => true,
                'action' => $action,
                'reaction_type' => $reaction_type,
                'previous_reaction' => $previous_reaction,
                'user_current_reaction' => $user_current_reaction,
                'reaction_counts' => $reaction_counts,
                'debug' => [
                    'post_id' => $post_id,
                    'user_id' => $user_id,
                    'already_reacted' => $already_reacted
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to react', 'sql_error' => $stmt->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
}
?>
