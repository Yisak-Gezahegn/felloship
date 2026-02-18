<?php
// Test events page variables
require_once 'config/database.php';

echo "<h1>Testing Events Page Variables</h1>";

// Test each query individually
echo "<h2>1. Total Events Query</h2>";
$total_events_query = "SELECT COUNT(*) as count FROM posts WHERE post_type = 'event'";
$result = $conn->query($total_events_query);
if ($result) {
    $total_events = $result->fetch_assoc()['count'];
    echo "<p>✓ Total events: $total_events</p>";
} else {
    echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
}

echo "<h2>2. Recent Count Query</h2>";
$recent_count_query = "SELECT COUNT(*) as count FROM posts WHERE post_type = 'event' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result = $conn->query($recent_count_query);
if ($result) {
    $recent_count = $result->fetch_assoc()['count'];
    echo "<p>✓ Recent count: $recent_count</p>";
} else {
    echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
}

echo "<h2>3. Upcoming Count Query</h2>";
$upcoming_count_query = "SELECT COUNT(*) as count FROM posts WHERE post_type = 'event' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = $conn->query($upcoming_count_query);
if ($result) {
    $upcoming_count = $result->fetch_assoc()['count'];
    echo "<p>✓ Upcoming count: $upcoming_count</p>";
} else {
    echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
}

echo "<h2>4. Check Event Posts</h2>";
$events_check = $conn->query("SELECT post_id, content, post_type, created_at FROM posts WHERE post_type = 'event'");
if ($events_check && $events_check->num_rows > 0) {
    echo "<p>✓ Found {$events_check->num_rows} event posts:</p>";
    while ($event = $events_check->fetch_assoc()) {
        echo "<p>- Post {$event['post_id']}: {$event['post_type']} - " . substr($event['content'], 0, 50) . "...</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ No event posts found. This explains why counts are 0.</p>";
}

echo "<h2>5. Check All Posts</h2>";
$all_posts = $conn->query("SELECT post_id, post_type, created_at FROM posts");
if ($all_posts && $all_posts->num_rows > 0) {
    echo "<p>✓ Found {$all_posts->num_rows} total posts:</p>";
    $type_counts = [];
    while ($post = $all_posts->fetch_assoc()) {
        $type_counts[$post['post_type']] = ($type_counts[$post['post_type']] ?? 0) + 1;
    }
    foreach ($type_counts as $type => $count) {
        echo "<p>- $type: $count posts</p>";
    }
} else {
    echo "<p style='color: red;'>✗ No posts found at all</p>";
}

echo "<p><a href='events.php'>← Back to Events</a></p>";
?>