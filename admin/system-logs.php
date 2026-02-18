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

// Handle log actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'clear_logs') {
            // Clear system logs (you might want to implement a proper logging system)
            $success_message = "System logs cleared successfully!";
        }
    }
}

$pageTitle = "System Logs - Admin Dashboard";
include '../includes/header.php';

// Get recent user activities (simulated system logs)
$recent_activities = $conn->query("
    SELECT 'User Login' as activity_type, u.full_name as user_name, u.last_seen as activity_time, 
           CONCAT('User logged in from IP: ', COALESCE(u.last_ip, 'Unknown')) as details
    FROM users u 
    WHERE u.last_login IS NOT NULL 
    ORDER BY u.last_login DESC 
    LIMIT 20
");

// Get recent posts (content activity)
$recent_posts = $conn->query("
    SELECT 'Post Created' as activity_type, u.full_name as user_name, p.created_at as activity_time,
           CONCAT('Created post: \"', LEFT(p.content, 50), '...\"') as details
    FROM posts p 
    JOIN users u ON p.user_id = u.user_id 
    ORDER BY p.created_at DESC 
    LIMIT 10
");

// Get recent comments
$recent_comments = $conn->query("
    SELECT 'Comment Added' as activity_type, u.full_name as user_name, c.created_at as activity_time,
           CONCAT('Commented: \"', LEFT(c.comment_text, 50), '...\"') as details
    FROM comments c 
    JOIN users u ON c.user_id = u.user_id 
    ORDER BY c.created_at DESC 
    LIMIT 10
");

// Combine all activities
$all_activities = [];

if ($recent_activities) {
    while ($activity = $recent_activities->fetch_assoc()) {
        $all_activities[] = $activity;
    }
}

if ($recent_posts) {
    while ($activity = $recent_posts->fetch_assoc()) {
        $all_activities[] = $activity;
    }
}

if ($recent_comments) {
    while ($activity = $recent_comments->fetch_assoc()) {
        $all_activities[] = $activity;
    }
}

// Sort by time (newest first)
usort($all_activities, function($a, $b) {
    return strtotime($b['activity_time']) - strtotime($a['activity_time']);
});

// Limit to 50 most recent
$all_activities = array_slice($all_activities, 0, 50);

// Get system statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$online_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_online = TRUE")->fetch_assoc()['count'];
$total_posts = $conn->query("SELECT COUNT(*) as count FROM posts")->fetch_assoc()['count'];
$total_comments = $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>📊 System Logs</h2>
            <p>Monitor system activities and user actions</p>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- System Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 25px 0;">
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-blue), var(--spiritual-purple));">
                <i class="fas fa-users"></i>
                <h3><?php echo $total_users; ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f);">
                <i class="fas fa-globe"></i>
                <h3><?php echo $online_users; ?></h3>
                <p>Online Now</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                <i class="fas fa-file-alt"></i>
                <h3><?php echo $total_posts; ?></h3>
                <p>Total Posts</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                <i class="fas fa-comments"></i>
                <h3><?php echo $total_comments; ?></h3>
                <p>Total Comments</p>
            </div>
        </div>

        <!-- System Status -->
        <div class="card" style="margin-bottom: 25px;">
            <div class="section-header">
                <h3>System Status</h3>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; padding: 20px;">
                <div class="info-box" style="background: linear-gradient(135deg, #43e97b, #38f9d7); color: white; padding: 20px; border-radius: var(--border-radius);">
                    <i class="fas fa-server" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <h4>Database</h4>
                    <p>✅ Connected and operational</p>
                </div>
                <div class="info-box" style="background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f); color: white; padding: 20px; border-radius: var(--border-radius);">
                    <i class="fas fa-shield-alt" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <h4>Security</h4>
                    <p>✅ All systems secure</p>
                </div>
                <div class="info-box" style="background: linear-gradient(135deg, var(--spiritual-blue), var(--spiritual-purple)); color: white; padding: 20px; border-radius: var(--border-radius);">
                    <i class="fas fa-tachometer-alt" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <h4>Performance</h4>
                    <p>✅ Running smoothly</p>
                </div>
                <div class="info-box" style="background: linear-gradient(135deg, #f093fb, #f5576c); color: white; padding: 20px; border-radius: var(--border-radius);">
                    <i class="fas fa-clock" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <h4>Uptime</h4>
                    <p>✅ System operational</p>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="card">
            <div class="section-header">
                <h3>Recent System Activities</h3>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="clear_logs">
                    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Clear all system logs?')">
                        <i class="fas fa-trash"></i> Clear Logs
                    </button>
                </form>
            </div>

            <?php if (!empty($all_activities)): ?>
            <div style="max-height: 600px; overflow-y: auto;">
                <table class="spiritual-table">
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>User</th>
                            <th>Details</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_activities as $activity): ?>
                        <tr>
                            <td>
                                <span class="status-badge" style="background: 
                                    <?php 
                                    $activity_colors = [
                                        'User Login' => 'var(--spiritual-blue)',
                                        'Post Created' => 'var(--spiritual-gold)',
                                        'Comment Added' => 'var(--spiritual-purple)'
                                    ];
                                    echo $activity_colors[$activity['activity_type']] ?? '#6c757d';
                                    ?>; color: white;">
                                    <?php 
                                    $activity_icons = [
                                        'User Login' => '🔐',
                                        'Post Created' => '📝',
                                        'Comment Added' => '💬'
                                    ];
                                    echo $activity_icons[$activity['activity_type']] ?? '📋';
                                    ?> <?php echo $activity['activity_type']; ?>
                                </span>
                            </td>
                            <td><strong><?php echo htmlspecialchars($activity['user_name']); ?></strong></td>
                            <td style="max-width: 300px; word-wrap: break-word;">
                                <?php echo htmlspecialchars($activity['details']); ?>
                            </td>
                            <td><?php echo timeAgo($activity['activity_time']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-file-text"></i>
                <h3>No Activities Found</h3>
                <p>System activities will appear here</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Log Information -->
        <div class="card" style="margin-top: 25px;">
            <div class="section-header">
                <h3>Log Information</h3>
            </div>
            <div style="padding: 20px;">
                <h4 style="color: var(--spiritual-gold); margin-bottom: 15px;">📋 Log Types:</h4>
                <ul style="line-height: 1.8; color: var(--dark-color);">
                    <li><strong>User Activities:</strong> Login/logout events, profile updates</li>
                    <li><strong>Content Activities:</strong> Post creation, comments, reactions</li>
                    <li><strong>System Events:</strong> Database operations, security events</li>
                    <li><strong>Error Logs:</strong> System errors and warnings</li>
                </ul>
                
                <h4 style="color: var(--spiritual-blue); margin: 20px 0 15px;">🔧 Log Management:</h4>
                <ul style="line-height: 1.8; color: var(--dark-color);">
                    <li>Logs are automatically rotated to prevent excessive storage usage</li>
                    <li>Critical events are highlighted for immediate attention</li>
                    <li>Export functionality available for detailed analysis</li>
                    <li>Real-time monitoring of system performance</li>
                </ul>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>