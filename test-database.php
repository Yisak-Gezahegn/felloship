<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

echo "<h2>Database Test</h2>";
echo "<p>Current User: " . $_SESSION['full_name'] . " (ID: " . $_SESSION['user_id'] . ")</p>";

// Test 1: Check tables exist
echo "<h3>1. Table Structure Check</h3>";
$tables = ['posts', 'post_reactions', 'comments', 'users'];
foreach ($tables as $table) {
    $result = $conn->query("DESCRIBE $table");
    if ($result) {
        echo "<p>✓ Table '$table' exists with " . $result->num_rows . " columns</p>";
    } else {
        echo "<p>✗ Table '$table' missing or error: " . $conn->error . "</p>";
    }
}

// Test 2: Check data
echo "<h3>2. Data Check</h3>";
$posts = $conn->query("SELECT COUNT(*) as count FROM posts");
$reactions = $conn->query("SELECT COUNT(*) as count FROM post_reactions");
$comments = $conn->query("SELECT COUNT(*) as count FROM comments");

if ($posts) {
    $post_count = $posts->fetch_assoc()['count'];
    echo "<p>Posts: $post_count</p>";
} else {
    echo "<p>Error counting posts: " . $conn->error . "</p>";
}

if ($reactions) {
    $reaction_count = $reactions->fetch_assoc()['count'];
    echo "<p>Reactions: $reaction_count</p>";
} else {
    echo "<p>Error counting reactions: " . $conn->error . "</p>";
}

if ($comments) {
    $comment_count = $comments->fetch_assoc()['count'];
    echo "<p>Comments: $comment_count</p>";
} else {
    echo "<p>Error counting comments: " . $conn->error . "</p>";
}

// Test 3: Try manual reaction insert
echo "<h3>3. Manual Reaction Test</h3>";
if (isset($_GET['test_insert'])) {
    $post_result = $conn->query("SELECT post_id FROM posts LIMIT 1");
    if ($post_result && $post_result->num_rows > 0) {
        $post_id = $post_result->fetch_assoc()['post_id'];
        $user_id = $_SESSION['user_id'];
        
        // Try to insert a reaction
        $stmt = $conn->prepare("INSERT INTO post_reactions (post_id, user_id, reaction_type) VALUES (?, ?, 'like') ON DUPLICATE KEY UPDATE reaction_type = 'like'");
        $stmt->bind_param("ii", $post_id, $user_id);
        
        if ($stmt->execute()) {
            echo "<p>✓ Successfully inserted/updated reaction for post $post_id</p>";
            
            // Check if it's there
            $check = $conn->query("SELECT * FROM post_reactions WHERE post_id = $post_id AND user_id = $user_id");
            if ($check && $check->num_rows > 0) {
                $reaction = $check->fetch_assoc();
                echo "<p>✓ Reaction confirmed: " . json_encode($reaction) . "</p>";
            }
        } else {
            echo "<p>✗ Failed to insert reaction: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p>No posts found for testing</p>";
    }
} else {
    echo "<p><a href='?test_insert=1'>Click to test manual reaction insert</a></p>";
}

// Test 4: Show sample data
echo "<h3>4. Sample Data</h3>";
$sample_posts = $conn->query("SELECT post_id, LEFT(content, 50) as content FROM posts LIMIT 3");
if ($sample_posts && $sample_posts->num_rows > 0) {
    echo "<table border='1'><tr><th>Post ID</th><th>Content</th></tr>";
    while ($post = $sample_posts->fetch_assoc()) {
        echo "<tr><td>{$post['post_id']}</td><td>{$post['content']}...</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No posts found</p>";
}

echo "<p><a href='home.php'>← Back to Home</a> | <a href='test-api.php'>Test API →</a></p>";
?>