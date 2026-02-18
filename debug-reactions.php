<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

echo "<h2>Debug Reactions System</h2>";

// Test 1: Check if post_reactions table exists and has data
echo "<h3>1. Post Reactions Table Check</h3>";
$result = $conn->query("SELECT * FROM post_reactions LIMIT 5");
if ($result) {
    echo "<p>✓ post_reactions table exists</p>";
    echo "<p>Rows found: " . $result->num_rows . "</p>";
    if ($result->num_rows > 0) {
        echo "<table border='1'><tr><th>ID</th><th>Post ID</th><th>User ID</th><th>Reaction</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['reaction_id']}</td><td>{$row['post_id']}</td><td>{$row['user_id']}</td><td>{$row['reaction_type']}</td></tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p>✗ Error: " . $conn->error . "</p>";
}

// Test 2: Check if posts table has data
echo "<h3>2. Posts Table Check</h3>";
$result = $conn->query("SELECT post_id, content FROM posts LIMIT 3");
if ($result) {
    echo "<p>✓ posts table exists</p>";
    echo "<p>Posts found: " . $result->num_rows . "</p>";
    if ($result->num_rows > 0) {
        echo "<table border='1'><tr><th>Post ID</th><th>Content (first 50 chars)</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['post_id']}</td><td>" . substr($row['content'], 0, 50) . "...</td></tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p>✗ Error: " . $conn->error . "</p>";
}

// Test 3: Test reaction insertion
echo "<h3>3. Test Reaction Insertion</h3>";
if (isset($_GET['test_react']) && isset($_GET['post_id'])) {
    $post_id = intval($_GET['post_id']);
    $user_id = $_SESSION['user_id'];
    
    // Try to insert a test reaction
    $stmt = $conn->prepare("INSERT INTO post_reactions (post_id, user_id, reaction_type) VALUES (?, ?, 'like') ON DUPLICATE KEY UPDATE reaction_type = 'like'");
    $stmt->bind_param("ii", $post_id, $user_id);
    
    if ($stmt->execute()) {
        echo "<p>✓ Test reaction inserted successfully</p>";
    } else {
        echo "<p>✗ Error inserting reaction: " . $stmt->error . "</p>";
    }
}

// Test 4: Show available posts for testing
echo "<h3>4. Test Reaction (Click to test)</h3>";
$posts = $conn->query("SELECT post_id, LEFT(content, 30) as content FROM posts LIMIT 3");
if ($posts && $posts->num_rows > 0) {
    while ($post = $posts->fetch_assoc()) {
        echo "<p><a href='?test_react=1&post_id={$post['post_id']}'>Test React to Post {$post['post_id']}: {$post['content']}...</a></p>";
    }
} else {
    echo "<p>No posts found for testing</p>";
}

// Test 5: Current user info
echo "<h3>5. Current User Info</h3>";
echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
echo "<p>Username: " . $_SESSION['username'] . "</p>";
echo "<p>Role: " . $_SESSION['role'] . "</p>";

echo "<p><a href='home.php'>← Back to Home</a></p>";
?>