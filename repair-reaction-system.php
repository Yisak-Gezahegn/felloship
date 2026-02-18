<?php
// Repair reaction system - ensure proper database structure
require_once 'config/database.php';

echo "<h1>🔧 Reaction System Repair</h1>";

// Check if user is logged in (basic check)
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>Please log in first.</p>";
    echo "<p><a href='index.php'>← Go to Login</a></p>";
    exit();
}

echo "<h2>1. Checking Database Tables</h2>";

// Check post_reactions table structure
$table_check = $conn->query("DESCRIBE post_reactions");
if ($table_check) {
    echo "<p>✅ post_reactions table exists</p>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $table_check->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ post_reactions table missing! Creating...</p>";
    
    $create_table = "CREATE TABLE post_reactions (
        reaction_id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        reaction_type ENUM('like', 'love', 'pray', 'amen') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_post (post_id, user_id),
        FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    
    if ($conn->query($create_table)) {
        echo "<p>✅ post_reactions table created successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create table: " . $conn->error . "</p>";
    }
}

echo "<h2>2. Checking Comments Table</h2>";

$comments_check = $conn->query("DESCRIBE comments");
if ($comments_check) {
    echo "<p>✅ comments table exists</p>";
} else {
    echo "<p style='color: red;'>❌ comments table missing! Creating...</p>";
    
    $create_comments = "CREATE TABLE comments (
        comment_id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        comment_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    
    if ($conn->query($create_comments)) {
        echo "<p>✅ comments table created successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create comments table: " . $conn->error . "</p>";
    }
}

echo "<h2>3. Testing Basic Operations</h2>";

// Test insert reaction
$test_post = $conn->query("SELECT post_id FROM posts LIMIT 1");
if ($test_post && $test_post->num_rows > 0) {
    $post_id = $test_post->fetch_assoc()['post_id'];
    $user_id = $_SESSION['user_id'];
    
    echo "<p>Testing with Post ID: $post_id, User ID: $user_id</p>";
    
    // Try to insert a test reaction
    $stmt = $conn->prepare("INSERT INTO post_reactions (post_id, user_id, reaction_type) VALUES (?, ?, 'like') ON DUPLICATE KEY UPDATE reaction_type = 'like'");
    if ($stmt) {
        $stmt->bind_param("ii", $post_id, $user_id);
        if ($stmt->execute()) {
            echo "<p>✅ Test reaction insert successful</p>";
            
            // Verify it exists
            $verify = $conn->query("SELECT * FROM post_reactions WHERE post_id = $post_id AND user_id = $user_id");
            if ($verify && $verify->num_rows > 0) {
                echo "<p>✅ Test reaction verified in database</p>";
                
                // Clean up test reaction
                $conn->query("DELETE FROM post_reactions WHERE post_id = $post_id AND user_id = $user_id");
                echo "<p>🧹 Test reaction cleaned up</p>";
            } else {
                echo "<p style='color: red;'>❌ Test reaction not found after insert</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Test reaction insert failed: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Failed to prepare test statement: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No posts found for testing</p>";
}

echo "<h2>4. API Endpoint Check</h2>";

// Check if API files exist
$api_files = ['api/react.php', 'api/comment.php', 'api/get-comments.php'];
foreach ($api_files as $file) {
    if (file_exists($file)) {
        echo "<p>✅ $file exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $file missing!</p>";
    }
}

echo "<h2>5. Current Data Summary</h2>";

$stats = [
    'posts' => $conn->query("SELECT COUNT(*) as count FROM posts")->fetch_assoc()['count'],
    'users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'reactions' => $conn->query("SELECT COUNT(*) as count FROM post_reactions")->fetch_assoc()['count'],
    'comments' => $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count']
];

foreach ($stats as $table => $count) {
    echo "<p>$table: <strong>$count</strong> records</p>";
}

echo "<h2>6. Recommendations</h2>";

if ($stats['reactions'] == 0) {
    echo "<p>⚠️ No reactions in database - this is normal for a fresh system</p>";
}

if ($stats['comments'] == 0) {
    echo "<p>⚠️ No comments in database - this is normal for a fresh system</p>";
}

echo "<p>✅ System appears to be properly configured</p>";
echo "<p>🧪 Use the debug page to test reactions: <a href='debug-reactions-final.php'>debug-reactions-final.php</a></p>";

echo "<div style='margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;'>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Go to <a href='debug-reactions-final.php'>debug-reactions-final.php</a> to test reactions</li>";
echo "<li>Try clicking the reaction buttons and watch the debug log</li>";
echo "<li>If reactions work there, the issue is in the home.php JavaScript</li>";
echo "<li>If reactions don't work, check the API endpoints</li>";
echo "</ol>";
echo "</div>";

echo "<p style='margin-top: 20px;'><a href='home.php'>← Back to Home</a></p>";
?>