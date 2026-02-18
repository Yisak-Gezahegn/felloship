<?php
// Quick test to verify groups table structure and data
require_once 'config/database.php';

echo "<h2>Testing Groups Table Structure</h2>";

// Check table structure
$result = $conn->query("DESCRIBE groups");
echo "<h3>Groups Table Columns:</h3>";
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

// Check group_type values
$result = $conn->query("SELECT group_type, COUNT(*) as count FROM groups GROUP BY group_type");
echo "<h3>Group Types Distribution:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Group Type</th><th>Count</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['group_type']}</td>";
    echo "<td>{$row['count']}</td>";
    echo "</tr>";
}
echo "</table>";

// Show sample groups
$result = $conn->query("SELECT group_id, group_name, group_type, created_at FROM groups LIMIT 5");
echo "<h3>Sample Groups:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Created</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['group_id']}</td>";
    echo "<td>{$row['group_name']}</td>";
    echo "<td>{$row['group_type']}</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><br>";
echo "<a href='admin/manage-groups.php'>Go to Manage Groups Page</a>";
?>
