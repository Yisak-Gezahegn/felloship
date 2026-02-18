<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check authentication and role
if (!isLoggedIn() || !hasRole('coordinator')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (!isset($_GET['report_id']) || !is_numeric($_GET['report_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid report ID']);
    exit();
}

$report_id = intval($_GET['report_id']);

try {
    // Get report details with section and user information
    $stmt = $conn->prepare("
        SELECT sr.*, s.section_name, u.full_name, ru.full_name as reviewer_name
        FROM section_reports sr
        JOIN sections s ON sr.section_id = s.section_id
        JOIN users u ON sr.submitted_by = u.user_id
        LEFT JOIN users ru ON sr.reviewed_by = ru.user_id
        WHERE sr.report_id = ?
    ");
    
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit();
    }
    
    $report = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'report' => $report
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>