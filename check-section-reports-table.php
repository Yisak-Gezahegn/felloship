<?php
// Check section_reports table structure
require_once 'config/database.php';

echo "<h2>Checking section_reports Table</h2>";

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'section_reports'");
if ($result->num_rows == 0) {
    echo "<p style='color: red;'>❌ Table 'section_reports' does NOT exist!</p>";
    echo "<p>The table needs to be created.</p>";
} else {
    echo "<p style='color: green;'>✅ Table 'section_reports' exists</p>";
    
    // Show table structure
    $result = $conn->query("DESCRIBE section_reports");
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Count records
    $result = $conn->query("SELECT COUNT(*) as count FROM section_reports");
    $count = $result->fetch_assoc()['count'];
    echo "<h3>Total Records: {$count}</h3>";
    
    if ($count > 0) {
        // Show sample data
        $result = $conn->query("SELECT * FROM section_reports LIMIT 3");
        echo "<h3>Sample Data:</h3>";
        echo "<table border='1' cellpadding='5'>";
        $first = true;
        while ($row = $result->fetch_assoc()) {
            if ($first) {
                echo "<tr>";
                foreach (array_keys($row) as $key) {
                    echo "<th>{$key}</th>";
                }
                echo "</tr>";
                $first = false;
            }
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}

echo "<br><br>";
echo "<a href='admin/section-reports.php'>Go to Section Reports Page</a>";
?>
