<?php
// Common Functions

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Check if user has minimum role level
function hasMinRole($minRole) {
    $roleHierarchy = ['member', 'family_leader', 'section_leader', 'fellowship_leader', 'coordinator', 'developer'];
    $userRoleIndex = array_search($_SESSION['role'] ?? 'member', $roleHierarchy);
    $minRoleIndex = array_search($minRole, $roleHierarchy);
    return $userRoleIndex >= $minRoleIndex;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        $depth = substr_count($_SERVER['PHP_SELF'], '/') - 2;
        $rel = str_repeat('../', max(0, $depth));
        header('Location: ' . $rel . 'login.php');
        exit();
    }
}

// Redirect if not approved
function requireApproval() {
    if ($_SESSION['status'] !== 'approved') {
        $depth = substr_count($_SERVER['PHP_SELF'], '/') - 2;
        $rel = str_repeat('../', max(0, $depth));
        header('Location: ' . $rel . 'pending.php');
        exit();
    }
}

// Sanitize input
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Format time ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return $diff . ' seconds ago';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $timestamp);
}

// Get user info by ID
function getUserInfo($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Update user online status
function updateOnlineStatus($conn, $userId) {
    $stmt = $conn->prepare("UPDATE users SET is_online = TRUE, last_seen = NOW() WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
}

// Get unread message count
function getUnreadCount($conn, $userId) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM private_messages WHERE receiver_id = ? AND is_read = FALSE");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'];
}

// Get unread notifications count
function getUnreadNotifications($conn, $userId) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'];
}

// Create notification
function createNotification($conn, $userId, $type, $title, $message, $link = '') {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, notification_type, title, message, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $userId, $type, $title, $message, $link);
    return $stmt->execute();
}

// Upload file
function uploadFile($file, $targetDir = 'uploads/') {
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetFile = $targetDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return $fileName;
    }
    return false;
}

// Get section name
function getSectionName($conn, $sectionId) {
    $stmt = $conn->prepare("SELECT section_name FROM sections WHERE section_id = ?");
    $stmt->bind_param("i", $sectionId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['section_name'] ?? 'N/A';
}
?>
