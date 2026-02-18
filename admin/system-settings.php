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

$pageTitle = "System Settings - Admin Dashboard";
include '../includes/header.php';
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>⚙️ System Settings</h2>
            <p>Configure system-wide settings and preferences</p>
        </div>

        <!-- System Information -->
        <div class="card" style="margin-bottom: 25px;">
            <div class="section-header">
                <h3>System Information</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; padding: 20px;">
                <div class="info-box">
                    <i class="fas fa-server"></i>
                    <strong>PHP Version:</strong> <?php echo phpversion(); ?>
                </div>
                <div class="info-box">
                    <i class="fas fa-database"></i>
                    <strong>MySQL Version:</strong> <?php echo $conn->server_info; ?>
                </div>
                <div class="info-box">
                    <i class="fas fa-hdd"></i>
                    <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
                </div>
                <div class="info-box">
                    <i class="fas fa-table"></i>
                    <strong>Database:</strong> <?php echo DB_NAME; ?>
                </div>
            </div>
        </div>

        <!-- Database Statistics -->
        <div class="card" style="margin-bottom: 25px;">
            <div class="section-header">
                <h3>Database Statistics</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; padding: 20px;">
                <?php
                $tables = ['users', 'posts', 'post_reactions', 'comments', 'groups', 'group_members', 'sections', 'events'];
                foreach ($tables as $table) {
                    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
                    $count = $result ? $result->fetch_assoc()['count'] : 0;
                    echo "<div class='stat-card' style='background: linear-gradient(135deg, var(--spiritual-blue), var(--spiritual-purple)); color: white;'>";
                    echo "<i class='fas fa-table'></i>";
                    echo "<h3>$count</h3>";
                    echo "<p>" . ucfirst($table) . "</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>

        <!-- System Actions -->
        <div class="card">
            <div class="section-header">
                <h3>System Actions</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; padding: 20px;">
                <a href="database-backup.php" class="quick-action-btn" style="background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f); color: white; text-decoration: none; padding: 20px; border-radius: var(--border-radius); text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fas fa-database" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    <strong>Database Backup</strong>
                    <p style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.9;">Backup database</p>
                </a>
                
                <a href="system-logs.php" class="quick-action-btn" style="background: linear-gradient(135deg, var(--spiritual-blue), var(--spiritual-purple)); color: white; text-decoration: none; padding: 20px; border-radius: var(--border-radius); text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fas fa-file-text" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    <strong>System Logs</strong>
                    <p style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.9;">View activity logs</p>
                </a>
                
                <a href="maintenance.php" class="quick-action-btn" style="background: linear-gradient(135deg, #fa709a, #fee140); color: white; text-decoration: none; padding: 20px; border-radius: var(--border-radius); text-align: center; transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fas fa-tools" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    <strong>Maintenance</strong>
                    <p style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.9;">System maintenance</p>
                </a>
                
                <div class="quick-action-btn" style="background: linear-gradient(135deg, #43e97b, #38f9d7); color: white; padding: 20px; border-radius: var(--border-radius); text-align: center;">
                    <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    <strong>System Status</strong>
                    <p style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.9;">All systems operational</p>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>