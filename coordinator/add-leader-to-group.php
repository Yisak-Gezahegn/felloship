<?php
// Check authentication and role BEFORE including header
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('coordinator')) {
    header('Location: ../home.php');
    exit();
}

$pageTitle = "Add Leader to Group - Haramaya Fellowship";
include '../includes/header.php';

$success = '';
$error = '';

// Get the leaders group
$leaders_group = $conn->query("SELECT * FROM groups WHERE group_name = 'Fellowship Leaders Group' AND created_by = {$_SESSION['user_id']} LIMIT 1")->fetch_assoc();

if (!$leaders_group) {
    header('Location: my-group.php');
    exit();
}

$group_id = $leaders_group['group_id'];

// Handle adding leader to group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    
    if (isset($_POST['action']) && $_POST['action'] === 'remove') {
        // Remove user from group
        $stmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $group_id, $user_id);
        
        if ($stmt->execute()) {
            $success = "Leader removed from group successfully!";
        } else {
            $error = "Failed to remove leader from group.";
        }
    } else {
        // Add user to group
        $existing = $conn->query("SELECT 1 FROM group_members WHERE group_id = $group_id AND user_id = $user_id")->num_rows;
        
        if ($existing > 0) {
            $error = "This user is already a member of the group!";
        } else {
            $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id, joined_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ii", $group_id, $user_id);
            
            if ($stmt->execute()) {
                $success = "Leader added to group successfully!";
            } else {
                $error = "Failed to add leader to group.";
            }
        }
    }
}

// Get all leaders who are not in the group yet
$available_leaders = $conn->query("SELECT u.*, s.section_name 
                                   FROM users u 
                                   LEFT JOIN sections s ON u.section_id = s.section_id
                                   WHERE u.role IN ('fellowship_leader', 'section_leader', 'family_leader') 
                                   AND u.status = 'approved'
                                   AND u.user_id NOT IN (
                                       SELECT user_id FROM group_members WHERE group_id = $group_id
                                   )
                                   ORDER BY u.full_name");

// Get current group members
$current_members_result = $conn->query("SELECT u.*, s.section_name 
                                        FROM group_members gm
                                        JOIN users u ON gm.user_id = u.user_id
                                        LEFT JOIN sections s ON u.section_id = s.section_id
                                        WHERE gm.group_id = $group_id
                                        ORDER BY u.full_name");

// Fetch all members into an array
$current_members = [];
while ($row = $current_members_result->fetch_assoc()) {
    $current_members[] = $row;
}
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>➕ Add Leader to Group</h2>
            <p>Add fellowship leaders to the Leaders Group</p>
        </div>

        <div class="dashboard">
            <aside class="sidebar">
                <h3>📋 Coordinator Panel</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="my-group.php"><i class="fas fa-users"></i> Leaders Group</a></li>
                    <li><a href="../messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="manage-users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="approve-leaders.php"><i class="fas fa-user-check"></i> Approve Leaders</a></li>
                    <li><a href="section-reports.php"><i class="fas fa-file-alt"></i> Section Reports</a></li>
                    <li><a href="create-post.php"><i class="fas fa-plus"></i> Create Post/Event</a></li>
                    <li><a href="search-members.php"><i class="fas fa-search"></i> Search Members</a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                </ul>
            </aside>
            
            <div class="main-content">
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <a href="my-group.php" style="margin-left: 15px;">Back to Group</a>
                </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                    <!-- Available Leaders -->
                    <div class="card">
                        <div class="section-header">
                            <h3>Available Leaders (<?php echo $available_leaders->num_rows; ?>)</h3>
                        </div>

                        <?php if ($available_leaders->num_rows > 0): ?>
                        <div style="max-height: 600px; overflow-y: auto;">
                            <?php while ($leader = $available_leaders->fetch_assoc()): ?>
                            <div style="display: flex; align-items: center; gap: 15px; padding: 15px; border-bottom: 1px solid #e9ecef; transition: all 0.3s ease;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                                <?php if (!empty($leader['profile_picture']) && $leader['profile_picture'] !== 'default.jpg' && file_exists("../uploads/profiles/{$leader['profile_picture']}")): ?>
                                <img src="../uploads/profiles/<?php echo $leader['profile_picture']; ?>" alt="Profile" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--spiritual-gold);">
                                <?php else: ?>
                                <div class="profile-pic-default" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <?php endif; ?>
                                
                                <div style="flex: 1;">
                                    <h4 style="margin-bottom: 5px; color: var(--primary-color);"><?php echo htmlspecialchars($leader['full_name']); ?></h4>
                                    <p style="font-size: 0.85rem; color: var(--gray); margin: 3px 0;">
                                        <i class="fas fa-user-tag"></i> <?php echo ucfirst(str_replace('_', ' ', $leader['role'])); ?>
                                    </p>
                                    <?php if ($leader['section_name']): ?>
                                    <p style="font-size: 0.85rem; color: var(--gray); margin: 3px 0;">
                                        <i class="fas fa-layer-group"></i> <?php echo $leader['section_name']; ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                                
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="user_id" value="<?php echo $leader['user_id']; ?>">
                                    <button type="submit" class="btn btn-primary" style="padding: 8px 15px;" onclick="return confirm('Add <?php echo htmlspecialchars($leader['full_name']); ?> to the Leaders Group?')">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </form>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h3>No Available Leaders</h3>
                            <p>All leaders are already in the group or there are no approved leaders yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Current Group Members -->
                    <div class="card">
                        <div class="section-header">
                            <h3>Current Members (<?php echo count($current_members); ?>)</h3>
                        </div>

                        <?php if (count($current_members) > 0): ?>
                        <div style="max-height: 600px; overflow-y: auto;">
                            <?php foreach ($current_members as $member): ?>
                            <div style="display: flex; align-items: center; gap: 15px; padding: 15px; border-bottom: 1px solid #e9ecef;">
                                <?php if (!empty($member['profile_picture']) && $member['profile_picture'] !== 'default.jpg' && file_exists("../uploads/profiles/{$member['profile_picture']}")): ?>
                                <img src="../uploads/profiles/<?php echo $member['profile_picture']; ?>" alt="Profile" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--spiritual-gold);">
                                <?php else: ?>
                                <div class="profile-pic-default" style="width: 50px; height: 50px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <?php endif; ?>
                                
                                <div style="flex: 1;">
                                    <h4 style="margin-bottom: 5px; color: var(--primary-color);"><?php echo htmlspecialchars($member['full_name']); ?></h4>
                                    <p style="font-size: 0.85rem; color: var(--gray); margin: 3px 0;">
                                        <i class="fas fa-user-tag"></i> <?php echo ucfirst(str_replace('_', ' ', $member['role'])); ?>
                                    </p>
                                    <?php if ($member['section_name']): ?>
                                    <p style="font-size: 0.85rem; color: var(--gray); margin: 3px 0;">
                                        <i class="fas fa-layer-group"></i> <?php echo $member['section_name']; ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php if ($member['is_online']): ?>
                                    <span style="width: 12px; height: 12px; background: var(--success-color); border-radius: 50%; box-shadow: 0 0 8px var(--success-color);"></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($member['user_id'] == $_SESSION['user_id']): ?>
                                    <span class="status-badge" style="background: var(--spiritual-gold); color: var(--dark-color); font-size: 0.75rem;">
                                        <i class="fas fa-crown"></i> You
                                    </span>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="user_id" value="<?php echo $member['user_id']; ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;" onclick="return confirm('Remove <?php echo htmlspecialchars($member['full_name']); ?> from the Leaders Group?')">
                                            <i class="fas fa-times"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h3>No Members Yet</h3>
                            <p>Add leaders to start building your group.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin-top: 20px; text-align: center;">
                    <a href="my-group.php" class="btn btn-gold">
                        <i class="fas fa-arrow-left"></i> Back to Leaders Group
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>