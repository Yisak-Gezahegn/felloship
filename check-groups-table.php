<?php
require_once 'config/database.php';

echo "Groups Table Structure:\n";
echo "======================\n\n";

$result = $conn->query("DESCRIBE groups");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . " - " . $row['Key'] . " - " . $row['Default'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n\nSample Groups Data:\n";
echo "==================\n\n";

$result = $conn->query("SELECT * FROM groups LIMIT 3");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);
        echo "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}
?>