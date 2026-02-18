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

// Handle backup actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'backup') {
            // Create backup directory if it doesn't exist
            $backup_dir = '../backups';
            if (!is_dir($backup_dir)) {
                mkdir($backup_dir, 0755, true);
            }
            
            $backup_file = $backup_dir . '/backup_' . date('Y-m-d_H-i-s') . '.sql';
            
            // Use mysqldump command (requires mysqldump to be available)
            $command = "mysqldump --host=" . DB_HOST . " --user=" . DB_USER . " --password=" . DB_PASS . " " . DB_NAME . " > " . $backup_file;
            
            // Execute the backup command
            $output = [];
            $return_var = 0;
            exec($command . " 2>&1", $output, $return_var);
            
            if ($return_var === 0 && file_exists($backup_file)) {
                $success_message = "Database backup created successfully: " . basename($backup_file);
            } else {
                $error_message = "Failed to create backup. Error: " . implode("\n", $output);
            }
        } elseif ($action === 'delete_backup' && isset($_POST['backup_file'])) {
            $backup_file = '../backups/' . basename($_POST['backup_file']); // Sanitize filename
            if (file_exists($backup_file) && unlink($backup_file)) {
                $success_message = "Backup file deleted successfully!";
            } else {
                $error_message = "Failed to delete backup file.";
            }
        }
    }
}

$pageTitle = "Database Backup - Admin Dashboard";
include '../includes/header.php';

// Get existing backup files
$backup_files = [];
$backup_dir = '../backups';
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backup_files[] = [
                'name' => $file,
                'size' => filesize($backup_dir . '/' . $file),
                'date' => filemtime($backup_dir . '/' . $file)
            ];
        }
    }
    // Sort by date (newest first)
    usort($backup_files, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

// Get database statistics
$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $table_name = $row[0];
    $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table_name`");
    $count = $count_result->fetch_assoc()['count'];
    $tables[] = ['name' => $table_name, 'count' => $count];
}
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>💾 Database Backup</h2>
            <p>Create and manage database backups</p>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Database Statistics -->
        <div class="card" style="margin-bottom: 25px;">
            <div class="section-header">
                <h3>Database Overview</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; padding: 20px;">
                <?php foreach ($tables as $table): ?>
                <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-blue), var(--spiritual-purple)); color: white;">
                    <i class="fas fa-table"></i>
                    <h3><?php echo $table['count']; ?></h3>
                    <p><?php echo ucfirst($table['name']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Create Backup -->
        <div class="card" style="margin-bottom: 25px;">
            <div class="section-header">
                <h3>Create New Backup</h3>
            </div>
            <div style="padding: 20px; text-align: center;">
                <p style="margin-bottom: 20px; color: var(--gray);">
                    Create a complete backup of the database including all tables and data.
                </p>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="backup">
                    <button type="submit" class="btn btn-primary" style="padding: 15px 30px; font-size: 1.1rem;">
                        <i class="fas fa-download"></i> Create Backup Now
                    </button>
                </form>
            </div>
        </div>

        <!-- Existing Backups -->
        <div class="card">
            <div class="section-header">
                <h3>Existing Backups</h3>
            </div>

            <?php if (!empty($backup_files)): ?>
            <div style="overflow-x: auto;">
                <table class="spiritual-table">
                    <thead>
                        <tr>
                            <th>Backup File</th>
                            <th>Size</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backup_files as $backup): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($backup['name']); ?></strong></td>
                            <td><?php echo formatBytes($backup['size']); ?></td>
                            <td><?php echo date('M d, Y H:i:s', $backup['date']); ?></td>
                            <td>
                                <a href="../backups/<?php echo urlencode($backup['name']); ?>" class="btn btn-success btn-sm" download>
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <form method="POST" style="display: inline; margin-left: 5px;">
                                    <input type="hidden" name="action" value="delete_backup">
                                    <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this backup file?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-database"></i>
                <h3>No Backups Found</h3>
                <p>Create your first backup using the button above</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Backup Instructions -->
        <div class="card" style="margin-top: 25px;">
            <div class="section-header">
                <h3>Backup Instructions</h3>
            </div>
            <div style="padding: 20px;">
                <h4 style="color: var(--spiritual-gold); margin-bottom: 15px;">📋 Important Notes:</h4>
                <ul style="line-height: 1.8; color: var(--dark-color);">
                    <li><strong>Regular Backups:</strong> Create backups regularly, especially before major updates</li>
                    <li><strong>Storage:</strong> Store backup files in a secure location outside the web directory</li>
                    <li><strong>Testing:</strong> Periodically test backup restoration to ensure data integrity</li>
                    <li><strong>Retention:</strong> Keep multiple backup versions and delete old ones to save space</li>
                </ul>
                
                <h4 style="color: var(--spiritual-blue); margin: 20px 0 15px; ">🔧 Manual Backup (Alternative):</h4>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.9rem;">
                    mysqldump -u <?php echo DB_USER; ?> -p <?php echo DB_NAME; ?> > backup.sql
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<?php 
// Helper function to format file sizes
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

include '../includes/footer.php'; 
?>