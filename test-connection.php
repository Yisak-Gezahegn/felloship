<?php
echo "<h1>Database Connection Test</h1>";

// Test 1: Basic PHP info
echo "<h2>1. PHP Configuration</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>MySQL Extension: " . (extension_loaded('mysqli') ? 'Loaded' : 'NOT LOADED') . "</p>";

// Test 2: Database connection
echo "<h2>2. Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    echo "<p>✓ Database connection successful</p>";
    echo "<p>Connection info: " . $conn->host_info . "</p>";
    
    // Test basic query
    $result = $conn->query("SELECT 1 as test");
    if ($result) {
        echo "<p>✓ Basic query successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Basic query failed: " . $conn->error . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test 3: Session test
echo "<h2>3. Session Test</h2>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<p>✓ Session active</p>";
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Username: " . ($_SESSION['username'] ?? 'Not set') . "</p>";
    echo "<p>Full Name: " . ($_SESSION['full_name'] ?? 'Not set') . "</p>";
} else {
    echo "<p style='color: red;'>✗ No active session</p>";
    echo "<p><a href='login.php'>Login first</a></p>";
}

// Test 4: Check if we can create tables (permissions test)
if (isset($conn)) {
    echo "<h2>4. Database Permissions Test</h2>";
    
    // Try to create a test table
    $test_table_query = "CREATE TABLE IF NOT EXISTS test_permissions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        test_data VARCHAR(50)
    )";
    
    if ($conn->query($test_table_query)) {
        echo "<p>✓ CREATE permission works</p>";
        
        // Try to insert
        if ($conn->query("INSERT INTO test_permissions (test_data) VALUES ('test')")) {
            echo "<p>✓ INSERT permission works</p>";
            
            // Try to select
            $select_result = $conn->query("SELECT * FROM test_permissions");
            if ($select_result) {
                echo "<p>✓ SELECT permission works (" . $select_result->num_rows . " rows)</p>";
            } else {
                echo "<p style='color: red;'>✗ SELECT permission failed</p>";
            }
            
            // Clean up
            $conn->query("DROP TABLE test_permissions");
            echo "<p>✓ Test table cleaned up</p>";
        } else {
            echo "<p style='color: red;'>✗ INSERT permission failed: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ CREATE permission failed: " . $conn->error . "</p>";
    }
}

// Test 5: Check database configuration
echo "<h2>5. Database Configuration</h2>";
if (file_exists('config/database.php')) {
    echo "<p>✓ Database config file exists</p>";
    
    // Read the config file content (safely)
    $config_content = file_get_contents('config/database.php');
    if (strpos($config_content, 'mysqli') !== false) {
        echo "<p>✓ Using mysqli connection</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Not using mysqli (might be using PDO or other)</p>";
    }
    
    // Check for common issues
    if (strpos($config_content, 'localhost') !== false || strpos($config_content, '127.0.0.1') !== false) {
        echo "<p>✓ Using local database</p>";
    } else {
        echo "<p>ℹ Using remote database</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Database config file missing!</p>";
}

echo "<p><a href='check-database.php'>→ Check Database Structure</a></p>";
echo "<p><a href='home.php'>← Back to Home</a></p>";
?>