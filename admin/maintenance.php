<?php
// Check authentication and role BEFORE including header
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('developer')) {
    header('Location: ../home.php');
    exit();
}

// Handle maintenance actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'optimize_database') {
            // Optimize all tables
            $tables = [];
            $result = $conn->query("SHOW TABLES");
            while ($row = $result->fetch_array()) {
                $table_name = $row[0];
                $conn->query("OPTIMIZE TABLE `$table_name`");
                $tables[] = $table_name;
            }
            $success_message = "Database optimized successfully! Optimized " . count($tables) . " tables.";
            
        } elseif ($action === 'clear_sessions') {
            // Clear old sessions (set all users offline)
            $conn->query("UPDATE users SET is_online = FALSE");
            $success_message = "All user sessions cleared successfully!";
            
        } elseif ($action === 'clean_uploads') {
            // Clean orphaned upload files (files not referenced in database)
            $cleaned_files = 0;
            
            // Check profile pictures
            $upload_dirs = ['../uploads/profiles', '../uploads/posts'];
            foreach ($upload_dirs as $dir) {
                if (is_dir($dir)) {
                    $files = scandir($dir);
                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..' && is_file($dir . '/' . $file)) {
                            $file_referenced = false;
                            
                            if (strpos($dir, 'profiles') !== false) {
                                // Check if profile picture is referenced
                                $check = $conn->query("SELECT COUNT(*) as count FROM users WHERE profile_picture = '$file'");
                                $file_referenced = $check->fetch_assoc()['count'] > 0;
                            } elseif (strpos($dir, 'posts') !== false) {
                                // Check if post image is referenced
                                $check = $conn->query("SELECT COUNT(*) as count FROM posts WHERE image = '$file'");
                                $file_referenced = $check->fetch_assoc()['count'] > 0;
                            }
                            
                            if (!$file_referenced) {
                                unlink($dir . '/' . $file);
                                $cleaned_files++;
                            }
                        }
                    }
                }
            }
            $success_message = "Upload cleanup completed! Removed $cleaned_files orphaned files.";
            
        } elseif ($action === 'repair_database') {
            // Repair all tables
            $tables = [];
            $result = $conn->query("SHOW TABLES");
            while ($row = $result->fetch_array()) {
                $table_name = $row[0];
                $conn->query("REPAIR TABLE `$table_name`");
                $tables[] = $table_name;
            }
            $success_message = "Database repair completed! Repaired " . count($tables) . " tables.";
        }
    }
}

$pageTitle = "System Maintenance - Admin Dashboard";
include '../includes/header.php';

// Get system information
$db_size_result = $conn->query("
    SELECT 
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'db_size_mb'
    FROM information_schema.tables 
    WHERE table_schema = '" . DB_NAME . "'
");
$db_size = $db_size_result ? $db_size_result->fetch_assoc()['db_size_mb'] : 0;

// Get table information
$table_info = $conn->query("
    SELECT 
        table_name,
        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb',
        table_rows
    FROM information_schema.TABLES 
    WHERE table_schema = '" . DB_NAME . "'
    ORDER BY (data_length + index_length) DESC
");

// Count upload files
$upload_counts = [
    'profiles' => 0,
    'posts' => 0
];

$upload_dirs = [
    'profiles' => '../uploads/profiles',
    'posts' => '../uploads/posts'
];

foreach ($upload_dirs as $type => $dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        $upload_counts[$type] = count($files) - 2; // Subtract . and ..
    }
}
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>🔧 System Maintenance</h2>
            <p>Perform system maintenance and optimization tasks</p>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- System Overview -->
        <div class="card" style="margin-bottom: 25px;">
            <div class="section-header">
                <h3>System Overview</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; padding: 20px;">
                <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-blue), var(--spiritual-purple));">
                    <i class="fas fa-database"></i>
                    <h3><?php echo $db_size; ?> MB</h3>
                    <p>Database Size</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f);">
                    <i class="fas fa-images"></i>
                    <h3><?php echo $upload_counts['profiles']; ?></h3>
                    <p>Profile Pictures</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                    <i class="fas fa-file-image"></i>
                    <h3><?php echo $upload_counts['posts']; ?></h3>
                    <p>Post Images</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                    <i class="fas fa-server"></i>
                    <h3>Online</h3>
                    <p>System Status</p>
                </div>
            </div>
        </div>

        <!-- Maintenance Actions -->
        <div class="card" style="margin-bottom: 25px;">
            <div class="section-header">
                <h3>Maintenance Actions</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; padding: 20px;">
                
                <!-- Database Optimization -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: var(--border-radius); border-left: 4px solid var(--spiritual-blue);">
                    <h4 style="color: var(--spiritual-blue); margin-bottom: 15px;">
                        <i class="fas fa-database"></i> Database Optimization
                    </h4>
                    <p style="color: var(--gray); margin-bottom: 15px;">
                        Optimize database tables to improve performance and reduce storage space.
                    </p>
                    <form method="POST">
                        <input type="hidden" name="action" value="optimize_database">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Optimize database? This may take a few minutes.')">
                            <i class="fas fa-cogs"></i> Optimize Database
                        </button>
                    </form>
                </div>

                <!-- Session Management -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: var(--border-radius); border-left: 4px solid var(--spiritual-gold);">
                    <h4 style="color: var(--spiritual-gold); margin-bottom: 15px;">
                        <i class="fas fa-users"></i> Session Management
                    </h4>
                    <p style="color: var(--gray); margin-bottom: 15px;">
                        Clear all user sessions and force users to log in again.
                    </p>
                    <form method="POST">
                        <input type="hidden" name="action" value="clear_sessions">
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Clear all user sessions? All users will need to log in again.')">
                            <i class="fas fa-sign-out-alt"></i> Clear Sessions
                        </button>
                    </form>
                </div>

                <!-- File Cleanup -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: var(--border-radius); border-left: 4px solid var(--spiritual-purple);">
                    <h4 style="color: var(--spiritual-purple); margin-bottom: 15px;">
                        <i class="fas fa-broom"></i> File Cleanup
                    </h4>
                    <p style="color: var(--gray); margin-bottom: 15px;">
                        Remove orphaned upload files that are no longer referenced in the database.
                    </p>
                    <form method="POST">
                        <input type="hidden" name="action" value="clean_uploads">
                        <button type="submit" class="btn btn-info" onclick="return confirm('Clean orphaned files? This will permanently delete unreferenced uploads.')">
                            <i class="fas fa-trash-alt"></i> Clean Files
                        </button>
                    </form>
                </div>

                <!-- Database Repair -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: var(--border-radius); border-left: 4px solid #f5576c;">
                    <h4 style="color: #f5576c; margin-bottom: 15px;">
                        <i class="fas fa-wrench"></i> Database Repair
                    </h4>
                    <p style="color: var(--gray); margin-bottom: 15px;">
                        Repair database tables to fix any corruption or integrity issues.
                    </p>
                    <form method="POST">
                        <input type="hidden" name="action" value="repair_database">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Repair database tables? This should only be done if you suspect database corruption.')">
                            <i class="fas fa-tools"></i> Repair Database
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Database Tables Information -->
        <div class="card">
            <div class="section-header">
                <h3>Database Tables</h3>
            </div>

            <?php if ($table_info && $table_info->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table class="spiritual-table">
                    <thead>
                        <tr>
                            <th>Table Name</th>
                            <th>Rows</th>
                            <th>Size (MB)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($table = $table_info->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($table['table_name']); ?></strong></td>
                            <td><?php echo number_format($table['table_rows']); ?></td>
                            <td><?php echo $table['size_mb']; ?> MB</td>
                            <td>
                                <span class="status-badge status-approved">
                                    ✅ Healthy
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-database"></i>
                <h3>No Table Information</h3>
                <p>Unable to retrieve table information</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Maintenance Schedule -->
        <div class="card" style="margin-top: 25px;">
            <div class="section-header">
                <h3>Recommended Maintenance Schedule</h3>
            </div>
            <div style="padding: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <h4 style="color: var(--spiritual-gold); margin-bottom: 10px;">📅 Daily</h4>
                        <ul style="line-height: 1.6; color: var(--dark-color);">
                            <li>Monitor system performance</li>
                            <li>Check error logs</li>
                            <li>Verify backup completion</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: var(--spiritual-blue); margin-bottom: 10px;">📅 Weekly</h4>
                        <ul style="line-height: 1.6; color: var(--dark-color);">
                            <li>Database optimization</li>
                            <li>File cleanup</li>
                            <li>Security updates</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="color: var(--spiritual-purple); margin-bottom: 10px;">📅 Monthly</h4>
                        <ul style="line-height: 1.6; color: var(--dark-color);">
                            <li>Full system backup</li>
                            <li>Performance analysis</li>
                            <li>User activity review</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>