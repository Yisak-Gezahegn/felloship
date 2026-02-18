<?php
// Simple database test without session requirements
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'haramaya_fellowship');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connection successful!\n";

// Check if required tables exist
$required_tables = ['posts', 'post_reactions', 'comments', 'users'];
foreach ($required_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✓ Table '$table' exists\n";
    } else {
        echo "✗ Table '$table' MISSING!\n";
    }
}

// Check data counts
echo "\nData counts:\n";
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
        echo "$table: $count records\n";
    } else {
        echo "Error counting $table: " . $conn->error . "\n";
    }
}

// Check if posts exist
echo "\nSample posts:\n";
$posts_result = $conn->query("SELECT post_id, user_id, LEFT(content, 50) as content FROM posts LIMIT 3");
if ($posts_result && $posts_result->num_rows > 0) {
    while ($post = $posts_result->fetch_assoc()) {
        echo "Post {$post['post_id']}: {$post['content']}...\n";
    }
} else {
    echo "No posts found or error: " . $conn->error . "\n";
}

$conn->close();
?>