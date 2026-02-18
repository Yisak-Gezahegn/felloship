<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

echo "<h1>Create Test Events</h1>";
echo "<p>Current User: " . $_SESSION['full_name'] . " (ID: " . $_SESSION['user_id'] . ")</p>";

// Check if there are any event posts
$event_count = $conn->query("SELECT COUNT(*) as count FROM posts WHERE post_type = 'event'")->fetch_assoc()['count'];
echo "<p>Current event posts: $event_count</p>";

if (isset($_GET['create'])) {
    // Create some test event posts
    $test_events = [
        [
            'content' => 'Join us for our weekly prayer meeting this Sunday at 3 PM. We will be praying for our community and sharing testimonies.',
            'days_ago' => 2
        ],
        [
            'content' => 'Bible study group meeting - Topic: "Faith in Action". Bring your Bibles and notebooks. Light refreshments will be provided.',
            'days_ago' => 5
        ],
        [
            'content' => 'Fellowship picnic at the university park. Games, food, and fellowship for all members and their families. Don\'t miss it!',
            'days_ago' => 10
        ],
        [
            'content' => 'Youth outreach program at the main campus. We need volunteers to help with setup and registration.',
            'days_ago' => 15
        ],
        [
            'content' => 'Special guest speaker Dr. John Smith will be sharing about "Living a Purpose-Driven Life". All are welcome!',
            'days_ago' => 1
        ]
    ];
    
    $created_count = 0;
    foreach ($test_events as $event) {
        $created_at = date('Y-m-d H:i:s', strtotime("-{$event['days_ago']} days"));
        
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content, post_type, created_at) VALUES (?, ?, 'event', ?)");
        $stmt->bind_param("iss", $_SESSION['user_id'], $event['content'], $created_at);
        
        if ($stmt->execute()) {
            $created_count++;
            echo "<p>✓ Created event: " . substr($event['content'], 0, 50) . "...</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create event: " . $stmt->error . "</p>";
        }
    }
    
    echo "<h3>Summary:</h3>";
    echo "<p>Created $created_count test events successfully!</p>";
    echo "<p><a href='events.php'>→ View Events Page</a></p>";
    
} else {
    if ($event_count == 0) {
        echo "<p style='color: orange;'>⚠ No event posts found. Click the button below to create some test events.</p>";
        echo "<p><a href='?create=1' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Create Test Events</a></p>";
    } else {
        echo "<p>✓ Event posts exist. You can view them on the events page.</p>";
        echo "<p><a href='events.php'>→ View Events Page</a></p>";
        echo "<p><a href='?create=1' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Create More Test Events</a></p>";
    }
}

echo "<p><a href='home.php'>← Back to Home</a></p>";
?>