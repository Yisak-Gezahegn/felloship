<?php
session_start();
include 'config/database.php';

echo "<h2>Database Debug Information</h2>";

// Check if tables exist
echo "<h3>1. Checking if tables exist:</h3>";
$tables = ['groups', 'group_members', 'group_messages'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ Table '$table' exists<br>";
    } else {
        echo "❌ Table '$table' does NOT exist<br>";
    }
}

// Check groups table
echo "<h3>2. Groups in database:</h3>";
$groups = $conn->query("SELECT * FROM groups");
if ($groups->num_rows > 0) {
    while ($group = $groups->fetch_assoc()) {
        echo "Group ID: {$group['group_id']}, Name: {$group['group_name']}, Type: {$group['group_type']}, Created by: {$group['created_by']}<br>";
    }
} else {
    echo "No groups found<br>";
}

// Check group_members table
echo "<h3>3. Group members:</h3>";
$members = $conn->query("SELECT gm.*, u.full_name FROM group_members gm JOIN users u ON gm.user_id = u.user_id");
if ($members->num_rows > 0) {
    while ($member = $members->fetch_assoc()) {
        echo "Group ID: {$member['group_id']}, User: {$member['full_name']} (ID: {$member['user_id']})<br>";
    }
} else {
    echo "No group members found<br>";
}

// Check group_messages table
echo "<h3>4. Group messages:</h3>";
$messages = $conn->query("SELECT gm.*, u.full_name FROM group_messages gm JOIN users u ON gm.user_id = u.user_id");
if ($messages->num_rows > 0) {
    while ($message = $messages->fetch_assoc()) {
        echo "Group ID: {$message['group_id']}, User: {$message['full_name']}, Message: " . substr($message['message_text'], 0, 50) . "..., Time: {$message['created_at']}<br>";
    }
} else {
    echo "No messages found<br>";
}

// Check current user session
echo "<h3>5. Current user session:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "User ID: {$_SESSION['user_id']}<br>";
    echo "Role: {$_SESSION['role']}<br>";
    echo "Name: {$_SESSION['full_name']}<br>";
} else {
    echo "No user session found<br>";
}

// Test creating a group and message
echo "<h3>6. Test operations:</h3>";
if (isset($_SESSION['user_id'])) {
    // Try to create a test group
    $test_result = $conn->query("INSERT INTO groups (group_name, description, group_type, created_by, created_at) VALUES ('Test Group', 'Test Description', 'leadership', {$_SESSION['user_id']}, NOW())");
    if ($test_result) {
        $test_group_id = $conn->insert_id;
        echo "✅ Test group created with ID: $test_group_id<br>";
        
        // Add user as member
        $member_result = $conn->query("INSERT INTO group_members (group_id, user_id, joined_at) VALUES ($test_group_id, {$_SESSION['user_id']}, NOW())");
        if ($member_result) {
            echo "✅ User added as member<br>";
            
            // Try to send a test message
            $message_result = $conn->query("INSERT INTO group_messages (group_id, user_id, message_text, created_at) VALUES ($test_group_id, {$_SESSION['user_id']}, 'Test message', NOW())");
            if ($message_result) {
                echo "✅ Test message created<br>";
            } else {
                echo "❌ Failed to create test message: " . $conn->error . "<br>";
            }
        } else {
            echo "❌ Failed to add user as member: " . $conn->error . "<br>";
        }
        
        // Clean up test data
        $conn->query("DELETE FROM group_messages WHERE group_id = $test_group_id");
        $conn->query("DELETE FROM group_members WHERE group_id = $test_group_id");
        $conn->query("DELETE FROM groups WHERE group_id = $test_group_id");
        echo "🧹 Test data cleaned up<br>";
    } else {
        echo "❌ Failed to create test group: " . $conn->error . "<br>";
    }
}
?>