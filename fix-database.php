<?php
echo "<h1>Database Fix and Setup</h1>";

try {
    require_once 'config/database.php';
    echo "<p>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit();
}

// Read and execute the main database.sql file
echo "<h2>1. Setting up database from database.sql</h2>";

if (file_exists('database.sql')) {
    $sql_content = file_get_contents('database.sql');
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0 || strpos($statement, '/*') === 0) {
            continue; // Skip empty statements and comments
        }
        
        try {
            if ($conn->query($statement)) {
                echo "<p style='color: green;'>✓ " . substr($statement, 0, 50) . "...</p>";
                $success_count++;
            } else {
                echo "<p style='color: red;'>✗ " . substr($statement, 0, 50) . "... Error: " . $conn->error . "</p>";
                $error_count++;
            }
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠ " . substr($statement, 0, 50) . "... " . $e->getMessage() . "</p>";
            $error_count++;
        }
    }
    
    echo "<h3>Database Setup Summary:</h3>";
    echo "<p>Successful: $success_count</p>";
    echo "<p>Errors/Warnings: $error_count</p>";
    
} else {
    echo "<p style='color: red;'>✗ database.sql file not found!</p>";
}

// Setup demo accounts
echo "<h2>2. Setting up demo accounts</h2>";

if (file_exists('demo-accounts.sql')) {
    $demo_sql = file_get_contents('demo-accounts.sql');
    $demo_statements = array_filter(array_map('trim', explode(';', $demo_sql)));
    
    foreach ($demo_statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            if ($conn->query($statement)) {
                echo "<p style='color: green;'>✓ " . substr($statement, 0, 50) . "...</p>";
            } else {
                echo "<p style='color: orange;'>⚠ " . substr($statement, 0, 50) . "... " . $conn->error . "</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠ " . substr($statement, 0, 50) . "... " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<p style='color: orange;'>⚠ demo-accounts.sql file not found</p>";
}

// Fix passwords if needed
echo "<h2>3. Fixing passwords</h2>";

$password_updates = [
    "UPDATE users SET password = ? WHERE username = 'developer'",
    "UPDATE users SET password = ? WHERE username = 'coordinator'", 
    "UPDATE users SET password = ? WHERE username = 'fellowship_leader'",
    "UPDATE users SET password = ? WHERE username = 'section_leader'",
    "UPDATE users SET password = ? WHERE username = 'family_leader'",
    "UPDATE users SET password = ? WHERE username = 'member'"
];

$dev_password = password_hash('admin123', PASSWORD_DEFAULT);
$demo_password = password_hash('demo123', PASSWORD_DEFAULT);

// Update developer password
$stmt = $conn->prepare($password_updates[0]);
$stmt->bind_param("s", $dev_password);
if ($stmt->execute()) {
    echo "<p>✓ Developer password updated</p>";
} else {
    echo "<p style='color: red;'>✗ Failed to update developer password</p>";
}

// Update other passwords
for ($i = 1; $i < count($password_updates); $i++) {
    $stmt = $conn->prepare($password_updates[$i]);
    $stmt->bind_param("s", $demo_password);
    if ($stmt->execute()) {
        echo "<p>✓ Password updated for user " . ($i + 1) . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Could not update password for user " . ($i + 1) . "</p>";
    }
}

// Test final database state
echo "<h2>4. Final Database Test</h2>";

$tables_to_check = ['users', 'posts', 'post_reactions', 'comments'];
foreach ($tables_to_check as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p>✓ $table: $count records</p>";
    } else {
        echo "<p style='color: red;'>✗ $table: Error - " . $conn->error . "</p>";
    }
}

// Create a test post if none exist
$post_count = $conn->query("SELECT COUNT(*) as count FROM posts")->fetch_assoc()['count'];
if ($post_count == 0) {
    echo "<h3>Creating test posts...</h3>";
    
    // Get a user ID
    $user_result = $conn->query("SELECT user_id FROM users LIMIT 1");
    if ($user_result && $user_result->num_rows > 0) {
        $user_id = $user_result->fetch_assoc()['user_id'];
        
        $test_posts = [
            "Welcome to Haramaya Fellowship! This is our first post.",
            "Join us for prayer meeting every Sunday at 3 PM.",
            "Bible study group is forming. Contact us if interested!"
        ];
        
        foreach ($test_posts as $content) {
            $stmt = $conn->prepare("INSERT INTO posts (user_id, content, post_type) VALUES (?, ?, 'general')");
            $stmt->bind_param("is", $user_id, $content);
            if ($stmt->execute()) {
                echo "<p>✓ Test post created: " . substr($content, 0, 30) . "...</p>";
            }
        }
    }
}

echo "<h2>5. Database Setup Complete!</h2>";
echo "<p>✓ Database should now be properly configured.</p>";
echo "<p><strong>Login Credentials:</strong></p>";
echo "<ul>";
echo "<li>Developer: username=<code>developer</code>, password=<code>admin123</code></li>";
echo "<li>Others: username=<code>coordinator</code>, password=<code>demo123</code></li>";
echo "</ul>";

echo "<p><a href='simple-db-test.php'>→ Test Database</a></p>";
echo "<p><a href='login.php'>→ Login</a></p>";
echo "<p><a href='home.php'>→ Home</a></p>";
?>