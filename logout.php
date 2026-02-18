<?php
session_start();
require_once 'config/database.php';

// Update user offline status
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("UPDATE users SET is_online = FALSE, last_seen = NOW() WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
}

// Destroy session
session_destroy();

// Redirect to home
header('Location: index.php');
exit();
?>
