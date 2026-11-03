<?php
// Database settings can be supplied through environment variables or
// config/database.local.php. XAMPP defaults are retained for local installs.
$localDbConfigPath = __DIR__ . '/database.local.php';
$localDbConfig = is_file($localDbConfigPath) ? require $localDbConfigPath : [];

if (!is_array($localDbConfig)) {
    throw new RuntimeException('config/database.local.php must return an array.');
}

$dbHost = getenv('DB_HOST');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');
$dbName = getenv('DB_NAME');
$dbPort = getenv('DB_PORT');

define('DB_HOST', $dbHost !== false && $dbHost !== '' ? $dbHost : ($localDbConfig['host'] ?? 'localhost'));
define('DB_USER', $dbUser !== false && $dbUser !== '' ? $dbUser : ($localDbConfig['user'] ?? 'root'));
define('DB_PASS', $dbPass !== false ? $dbPass : ($localDbConfig['password'] ?? ''));
define('DB_NAME', $dbName !== false && $dbName !== '' ? $dbName : ($localDbConfig['name'] ?? 'haramaya_fellowship'));
define('DB_PORT', (int) ($dbPort !== false && $dbPort !== '' ? $dbPort : ($localDbConfig['port'] ?? 3306)));

// Base URL Configuration
if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    $base_url = $protocol . '://' . $host . str_replace(basename($script_name), '', $script_name);
    $base_url = rtrim($base_url, '/');
    define('BASE_URL', $base_url);
} else {
    // Fallback for CLI usage
    define('BASE_URL', 'http://localhost/felloship');
}

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    // Log the real error and show a safe message to the user
    error_log("DB Connection failed: " . $conn->connect_error);
    die("A database connection error occurred. Please try again later.");
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
