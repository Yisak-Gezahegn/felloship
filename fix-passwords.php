<?php
// Run this file once to fix all passwords
// Visit: http://localhost/haramaya-fellowship/fix-passwords.php

require_once 'config/database.php';

echo "<h2>Fixing Passwords...</h2>";

// Update developer password to: admin123
$password = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password = '$password' WHERE username = 'developer'");
echo "✓ Developer password set to: admin123<br>";

// Update all demo accounts to: demo123
$password = password_hash('demo123', PASSWORD_DEFAULT);

$accounts = ['coordinator', 'fellow_leader', 'section_leader', 'family_leader', 'member'];

foreach ($accounts as $username) {
    $result = $conn->query("UPDATE users SET password = '$password' WHERE username = '$username'");
    if ($result) {
        echo "✓ $username password set to: demo123<br>";
    }
}

echo "<br><h3>All passwords have been fixed!</h3>";
echo "<p>You can now login with:</p>";
echo "<ul>";
echo "<li><strong>developer</strong> / admin123</li>";
echo "<li><strong>coordinator</strong> / demo123</li>";
echo "<li><strong>fellow_leader</strong> / demo123</li>";
echo "<li><strong>section_leader</strong> / demo123</li>";
echo "<li><strong>family_leader</strong> / demo123</li>";
echo "<li><strong>member</strong> / demo123</li>";
echo "</ul>";
echo "<br><a href='login.php'>Go to Login Page</a>";
echo "<br><br><strong>IMPORTANT: Delete this file (fix-passwords.php) after use for security!</strong>";
?>
