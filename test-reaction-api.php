<?php
// Test the reaction API directly
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Simulate a logged-in user (use the developer account)
$_SESSION['user_id'] = 1; // Developer user
$_SESSION['full_name'] = 'Developer';
$_SESSION['role'] = 'developer';

echo "<h1>Testing Reaction API</h1>";

// Get a post to test with
$post_result = $conn->query("SELECT post_id FROM posts LIMIT 1");
if ($post_result && $post_result->num_rows > 0) {
    $post_id = $post_result->fetch_assoc()['post_id'];
    echo "<p>Testing with Post ID: $post_id</p>";
    
    // Test 1: Direct database insert
    echo "<h2>Test 1: Direct Database Insert</h2>";
    $stmt = $conn->prepare("INSERT INTO post_reactions (post_id, user_id, reaction_type) VALUES (?, ?, 'like') ON DUPLICATE KEY UPDATE reaction_type = 'like'");
    if ($stmt) {
        $stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            echo "<p>✓ Direct insert successful</p>";
        } else {
            echo "<p style='color: red;'>✗ Direct insert failed: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Failed to prepare statement: " . $conn->error . "</p>";
    }
    
    // Test 2: Check if reaction exists
    echo "<h2>Test 2: Check Reaction Exists</h2>";
    $check = $conn->query("SELECT * FROM post_reactions WHERE post_id = $post_id AND user_id = {$_SESSION['user_id']}");
    if ($check && $check->num_rows > 0) {
        $reaction = $check->fetch_assoc();
        echo "<p>✓ Reaction found: " . json_encode($reaction) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ No reaction found</p>";
    }
    
    // Test 3: Simulate API call
    echo "<h2>Test 3: Simulate API Call</h2>";
    $_POST['post_id'] = $post_id;
    $_POST['reaction_type'] = 'pray';
    
    // Include the API file content directly
    ob_start();
    try {
        // Validate inputs
        $post_id_api = intval($_POST['post_id']);
        $reaction_type = $_POST['reaction_type'];
        $user_id = $_SESSION['user_id'];

        if (!$post_id_api || !in_array($reaction_type, ['like', 'love', 'pray', 'amen'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit();
        }

        // Check if already reacted
        $stmt = $conn->prepare("SELECT reaction_id, reaction_type FROM post_reactions WHERE post_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $post_id_api, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $existing = $result->fetch_assoc();
            if ($existing['reaction_type'] === $reaction_type) {
                // Same reaction - remove it
                $stmt = $conn->prepare("DELETE FROM post_reactions WHERE post_id = ? AND user_id = ?");
                $stmt->bind_param("ii", $post_id_api, $user_id);
                $action = 'removed';
            } else {
                // Different reaction - update it
                $stmt = $conn->prepare("UPDATE post_reactions SET reaction_type = ? WHERE post_id = ? AND user_id = ?");
                $stmt->bind_param("sii", $reaction_type, $post_id_api, $user_id);
                $action = 'updated';
            }
        } else {
            // Insert new reaction
            $stmt = $conn->prepare("INSERT INTO post_reactions (post_id, user_id, reaction_type) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $post_id_api, $user_id, $reaction_type);
            $action = 'added';
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'action' => $action, 'reaction_type' => $reaction_type]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to react', 'sql_error' => $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
    }
    $api_output = ob_get_clean();
    echo "<p>API Response: $api_output</p>";
    
    // Test 4: Check final state
    echo "<h2>Test 4: Final State Check</h2>";
    $final_check = $conn->query("SELECT * FROM post_reactions WHERE post_id = $post_id");
    if ($final_check && $final_check->num_rows > 0) {
        echo "<p>✓ Final reactions for post $post_id:</p>";
        while ($reaction = $final_check->fetch_assoc()) {
            echo "<p>- User {$reaction['user_id']}: {$reaction['reaction_type']}</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ No reactions found after API test</p>";
    }
    
} else {
    echo "<p style='color: red;'>No posts found for testing</p>";
}

echo "<p><a href='home.php'>← Back to Home</a></p>";
?>