<?php
require_once 'config/database.php';

echo "<h2>Updating Database Schema for Section Reports</h2>\n";

// Read the SQL update file
$sql_content = file_get_contents('update-section-reports.sql');

// Split into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql_content)));

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue; // Skip empty statements and comments
    }
    
    try {
        if ($conn->query($statement)) {
            echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 50) . "...</p>\n";
            $success_count++;
        } else {
            echo "<p style='color: red;'>✗ Failed: " . substr($statement, 0, 50) . "... Error: " . $conn->error . "</p>\n";
            $error_count++;
        }
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠ Warning: " . substr($statement, 0, 50) . "... " . $e->getMessage() . "</p>\n";
        $error_count++;
    }
}

echo "<h3>Update Summary:</h3>\n";
echo "<p>Successful: $success_count</p>\n";
echo "<p>Errors/Warnings: $error_count</p>\n";

if ($error_count === 0) {
    echo "<p style='color: green; font-weight: bold;'>✓ Database update completed successfully!</p>\n";
    echo "<p><a href='coordinator/section-reports.php'>Test Section Reports Page</a></p>\n";
} else {
    echo "<p style='color: orange;'>⚠ Database update completed with some warnings. The system should still work.</p>\n";
}

$conn->close();
?>