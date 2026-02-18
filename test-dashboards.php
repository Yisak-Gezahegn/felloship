<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

echo "<h1>Dashboard Links Test</h1>";
echo "<p>Current User: " . $_SESSION['full_name'] . " (Role: " . $_SESSION['role'] . ")</p>";

// Test dashboard access based on role
$dashboards = [
    'developer' => 'admin/dashboard.php',
    'coordinator' => 'coordinator/dashboard.php', 
    'fellowship_leader' => 'fellowship-leaders/dashboard.php',
    'section_leader' => 'section-leaders/dashboard.php',
    'family_leader' => 'family-leaders/dashboard.php',
    'member' => 'members/dashboard.php'
];

echo "<h2>Available Dashboards:</h2>";
foreach ($dashboards as $role => $path) {
    $accessible = hasRole($role) ? "✓ Accessible" : "✗ Not accessible";
    $color = hasRole($role) ? "green" : "red";
    echo "<p style='color: $color;'><a href='$path'>$role Dashboard</a> - $accessible</p>";
}

echo "<h2>Common Pages Test:</h2>";
$common_pages = [
    'home.php' => 'Home Feed',
    'profile.php' => 'My Profile', 
    'settings.php' => 'Settings',
    'messages.php' => 'Messages',
    'groups.php' => 'Groups',
    'events.php' => 'Events',
    'sections.php' => 'Sections'
];

foreach ($common_pages as $page => $title) {
    $exists = file_exists($page) ? "✓ Exists" : "✗ Missing";
    $color = file_exists($page) ? "green" : "red";
    echo "<p style='color: $color;'><a href='$page'>$title</a> - $exists</p>";
}

echo "<h2>Role-Specific Pages Test:</h2>";

// Test coordinator pages
if (hasRole('coordinator')) {
    $coordinator_pages = [
        'coordinator/my-group.php' => 'Leaders Group',
        'coordinator/manage-users.php' => 'Manage Users',
        'coordinator/approve-leaders.php' => 'Approve Leaders',
        'coordinator/section-reports.php' => 'Section Reports',
        'coordinator/create-post.php' => 'Create Post',
        'coordinator/search-members.php' => 'Search Members'
    ];
    
    echo "<h3>Coordinator Pages:</h3>";
    foreach ($coordinator_pages as $page => $title) {
        $exists = file_exists($page) ? "✓ Exists" : "✗ Missing";
        $color = file_exists($page) ? "green" : "red";
        echo "<p style='color: $color;'><a href='$page'>$title</a> - $exists</p>";
    }
}

// Test fellowship leader pages
if (hasRole('fellowship_leader')) {
    $fellowship_pages = [
        'fellowship-leaders/my-groups.php' => 'My Groups',
        'fellowship-leaders/create-group.php' => 'Create Group',
        'fellowship-leaders/manage-users.php' => 'Manage Users',
        'fellowship-leaders/create-post.php' => 'Create Post',
        'fellowship-leaders/search-members.php' => 'Search Members',
        'fellowship-leaders/financial-reports.php' => 'Financial Reports'
    ];
    
    echo "<h3>Fellowship Leader Pages:</h3>";
    foreach ($fellowship_pages as $page => $title) {
        $exists = file_exists($page) ? "✓ Exists" : "✗ Missing";
        $color = file_exists($page) ? "green" : "red";
        echo "<p style='color: $color;'><a href='$page'>$title</a> - $exists</p>";
    }
}

echo "<p><a href='dashboard.php'>← Back to Dashboard</a></p>";
?>