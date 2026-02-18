<?php
session_start();
echo "<h2>Debug Session Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['role'])) {
    echo "<p><strong>Your Role:</strong> " . $_SESSION['role'] . "</p>";
    echo "<p><strong>Has fellowship_leader role:</strong> " . (($_SESSION['role'] === 'fellowship_leader') ? 'YES' : 'NO') . "</p>";
    echo "<p><strong>Has coordinator role:</strong> " . (($_SESSION['role'] === 'coordinator') ? 'YES' : 'NO') . "</p>";
    echo "<p><strong>Has developer role:</strong> " . (($_SESSION['role'] === 'developer') ? 'YES' : 'NO') . "</p>";
} else {
    echo "<p><strong>No role set in session!</strong></p>";
}

// Check database connection
require_once 'config/database.php';
if (isset($_SESSION['user_id'])) {
    $user = $conn->query("SELECT * FROM users WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc();
    echo "<h3>Database User Info:</h3>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";
}
?>