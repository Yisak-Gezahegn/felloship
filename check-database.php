<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

echo "<h1>Complete Database Check</h1>";
echo "<p>Current User: " . $_SESSION['full_name'] . " (ID: " . $_SESSION['user_id'] . ")</p>";

// Test 1: Check if all required tables exist
echo "<h2>1. Table Existence Check</h2>";
$required_tables = ['posts', 'post_reactions', 'comments', 'users'];
foreach ($required_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p>✓ Table '$table' exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Table '$table' MISSING!</p>";
    }
}

// Test 2: Check table structures
echo "<h2>2. Table Structure Check</h2>";

echo "<h3>Posts Table:</h3>";
$posts_structure = $conn->query("DESCRIBE posts");
if ($posts_structure) {
    echo "<table border='1'><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $posts_structure->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error describing posts table: " . $conn->error . "</p>";
}

echo "<h3>Post_reactions Table:</h3>";
$reactions_structure = $conn->query("DESCRIBE post_reactions");
if ($reactions_structure) {
    echo "<table border='1'><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $reactions_structure->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error describing post_reactions table: " . $conn->error . "</p>";
}

echo "<h3>Comments Table:</h3>";
$comments_structure = $conn->query("DESCRIBE comments");
if ($comments_structure) {
    echo "<table border='1'><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $comments_structure->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error describing comments table: " . $conn->error . "</p>";
}

// Test 3: Check data counts
echo "<h2>3. Data Count Check</h2>";
$tables_data = [
    'posts' => 'SELECT COUNT(*) as count FROM posts',
    'post_reactions' => 'SELECT COUNT(*) as count FROM post_reactions',
    'comments' => 'SELECT COUNT(*) as count FROM comments',
    'users' => 'SELECT COUNT(*) as count FROM users'
];

foreach ($tables_data as $table => $query) {
    $result = $conn->query($query);
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p>$table: $count records</p>";
    } else {
        echo "<p style='color: red;'>Error counting $table: " . $conn->error . "</p>";
    }
}

// Test 4: Check sample posts data
echo "<h2>4. Sample Posts Data</h2>";
$posts_query = "SELECT post_id, user_id, LEFT(content, 50) as content, post_type, created_at FROM posts LIMIT 5";
$posts_result = $conn->query($posts_query);
if ($posts_result && $posts_result->num_rows > 0) {
    echo "<table border='1'><tr><th>Post ID</th><th>User ID</th><th>Content</th><th>Type</th><th>Created</th></tr>";
    while ($post = $posts_result->fetch_assoc()) {
        echo "<tr><td>{$post['post_id']}</td><td>{$post['user_id']}</td><td>{$post['content']}...</td><td>{$post['post_type']}</td><td>{$post['created_at']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No posts found or error: " . $conn->error . "</p>";
}

// Test 5: Test the exact query from home.php
echo "<h2>5. Home.php Query Test</h2>";
$home_query = "SELECT p.*, u.full_name, u.profile_picture, u.role,
                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id) as reaction_count,
                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id AND reaction_type = 'like') as like_count,
                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id AND reaction_type = 'pray') as pray_count,
                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id AND reaction_type = 'love') as love_count,
                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id AND reaction_type = 'amen') as amen_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count,
                (SELECT reaction_type FROM post_reactions WHERE post_id = p.post_id AND user_id = {$_SESSION['user_id']}) as user_reaction
                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                ORDER BY p.created_at DESC
                LIMIT 3";

$home_result = $conn->query($home_query);
if ($home_result) {
    echo "<p>✓ Home query executed successfully. Found {$home_result->num_rows} posts.</p>";
    if ($home_result->num_rows > 0) {
        echo "<table border='1'><tr><th>Post ID</th><th>Author</th><th>Like Count</th><th>Pray Count</th><th>Comment Count</th><th>User Reaction</th></tr>";
        while ($post = $home_result->fetch_assoc()) {
            echo "<tr><td>{$post['post_id']}</td><td>{$post['full_name']}</td><td>{$post['like_count']}</td><td>{$post['pray_count']}</td><td>{$post['comment_count']}</td><td>" . ($post['user_reaction'] ?: 'None') . "</td></tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>✗ Home query failed: " . $conn->error . "</p>";
}

// Test 6: Manual reaction insert test
echo "<h2>6. Manual Reaction Insert Test</h2>";
if (isset($_GET['test_insert'])) {
    $post_result = $conn->query("SELECT post_id FROM posts LIMIT 1");
    if ($post_result && $post_result->num_rows > 0) {
        $post_id = $post_result->fetch_assoc()['post_id'];
        $user_id = $_SESSION['user_id'];
        
        echo "<p>Testing with Post ID: $post_id, User ID: $user_id</p>";
        
        // Try to insert a reaction
        $stmt = $conn->prepare("INSERT INTO post_reactions (post_id, user_id, reaction_type) VALUES (?, ?, 'like') ON DUPLICATE KEY UPDATE reaction_type = 'like'");
        if ($stmt) {
            $stmt->bind_param("ii", $post_id, $user_id);
            
            if ($stmt->execute()) {
                echo "<p>✓ Manual reaction insert successful</p>";
                
                // Check if it's there
                $check = $conn->query("SELECT * FROM post_reactions WHERE post_id = $post_id AND user_id = $user_id");
                if ($check && $check->num_rows > 0) {
                    $reaction = $check->fetch_assoc();
                    echo "<p>✓ Reaction confirmed in database: " . json_encode($reaction) . "</p>";
                } else {
                    echo "<p style='color: red;'>✗ Reaction not found after insert</p>";
                }
            } else {
                echo "<p style='color: red;'>✗ Manual reaction insert failed: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Failed to prepare statement: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>No posts found for testing</p>";
    }
} else {
    echo "<p><a href='?test_insert=1'>Click to test manual reaction insert</a></p>";
}

// Test 7: Check if database was properly created
echo "<h2>7. Database Creation Check</h2>";
$db_name = $conn->query("SELECT DATABASE() as db_name")->fetch_assoc()['db_name'];
echo "<p>Current database: $db_name</p>";

// Check if the database has the expected structure
$expected_tables = ['users', 'sections', 'posts', 'post_reactions', 'comments', 'groups', 'group_members', 'group_messages', 'private_messages', 'events', 'section_reports', 'financial_reports'];
$existing_tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $existing_tables[] = $row[0];
}

echo "<h3>Expected vs Existing Tables:</h3>";
echo "<table border='1'><tr><th>Table</th><th>Status</th></tr>";
foreach ($expected_tables as $table) {
    $status = in_array($table, $existing_tables) ? "✓ Exists" : "✗ Missing";
    $color = in_array($table, $existing_tables) ? "green" : "red";
    echo "<tr><td>$table</td><td style='color: $color;'>$status</td></tr>";
}
echo "</table>";

echo "<h3>Extra Tables (not expected):</h3>";
$extra_tables = array_diff($existing_tables, $expected_tables);
if (empty($extra_tables)) {
    echo "<p>No extra tables found.</p>";
} else {
    foreach ($extra_tables as $table) {
        echo "<p>Extra table: $table</p>";
    }
}

echo "<p><a href='home.php'>← Back to Home</a></p>";
?>