<?php
// Check authentication and role BEFORE including header
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();
if (!hasRole('fellowship_leader') && !hasRole('coordinator') && !hasRole('developer')) {
    header('Location: ../home.php');
    exit();
}

$success = '';
$error = '';

// Handle form submission BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = trim($_POST['group_name']);
    $description = trim($_POST['description']);
    $group_type = $_POST['group_type'];
    
    if (empty($group_name) || empty($description)) {
        $error = "Group name and description are required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO groups (group_name, description, group_type, created_by, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssi", $group_name, $description, $group_type, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $group_id = $conn->insert_id;
            
            // Add creator as member
            $stmt2 = $conn->prepare("INSERT INTO group_members (group_id, user_id, joined_at) VALUES (?, ?, NOW())");
            $stmt2->bind_param("ii", $group_id, $_SESSION['user_id']);
            $stmt2->execute();
            
            header("Location: my-groups.php?success=created");
            exit();
        } else {
            $error = "Failed to create group. Please try again.";
        }
    }
}

$pageTitle = "Create Group - Haramaya Fellowship";
include '../includes/header.php';
?>

<main style="padding: 25px 0;">
    <div class="container">
        <!-- Page Header -->
        <div class="dashboard-welcome">
            <h2>➕ Create New Group</h2>
            <p>Start a new group to connect with fellowship members</p>
        </div>

        <div class="dashboard">
            <!-- Sidebar -->
            <aside class="sidebar">
                <h3>📊 Leader Panel</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../home.php"><i class="fas fa-home"></i> Home Feed</a></li>
                    <li><a href="create-post.php"><i class="fas fa-plus"></i> Create Post</a></li>
                    <li><a href="my-groups.php"><i class="fas fa-users"></i> My Groups</a></li>
                    <li><a href="create-group.php" class="active"><i class="fas fa-plus-circle"></i> Create Group</a></li>
                    <li><a href="../messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="financial-reports.php"><i class="fas fa-dollar-sign"></i> Financial Reports</a></li>
                    <li><a href="manage-users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </aside>
            
            <!-- Main Content -->
            <div class="main-content">
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="section-header">
                        <h3>Group Information</h3>
                    </div>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="group_name">
                                <i class="fas fa-users"></i> Group Name <span style="color: var(--danger-color);">*</span>
                            </label>
                            <input type="text" 
                                   id="group_name" 
                                   name="group_name" 
                                   class="form-control" 
                                   placeholder="Enter group name (e.g., Prayer Warriors, Bible Study Team)"
                                   required
                                   maxlength="100">
                        </div>

                        <div class="form-group">
                            <label for="group_type">
                                <i class="fas fa-tag"></i> Group Type <span style="color: var(--danger-color);">*</span>
                            </label>
                            <select id="group_type" name="group_type" class="form-control" required>
                                <option value="">-- Select Group Type --</option>
                                <option value="public">Public - Anyone can join</option>
                                <option value="private">Private - Invitation only</option>
                                <option value="section">Section Group - For specific section</option>
                                <option value="leadership">Leadership - For leaders only</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description">
                                <i class="fas fa-align-left"></i> Description <span style="color: var(--danger-color);">*</span>
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      class="form-control" 
                                      rows="6" 
                                      placeholder="Describe the purpose and goals of this group..."
                                      required
                                      maxlength="500"></textarea>
                            <small style="color: var(--gray); display: block; margin-top: 5px;">
                                <i class="fas fa-info-circle"></i> Maximum 500 characters
                            </small>
                        </div>

                        <div class="info-box">
                            <h4 style="margin-bottom: 10px;"><i class="fas fa-lightbulb"></i> Group Guidelines</h4>
                            <ul style="margin: 0; padding-left: 20px; color: var(--gray);">
                                <li>Choose a clear, descriptive name for your group</li>
                                <li>Public groups are visible to all members</li>
                                <li>Private groups require approval to join</li>
                                <li>You will be the group owner and can manage members</li>
                                <li>Keep discussions respectful and on-topic</li>
                            </ul>
                        </div>

                        <div style="display: flex; gap: 15px; margin-top: 25px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-plus-circle"></i> Create Group
                            </button>
                            <a href="my-groups.php" class="btn" style="flex: 1; background: var(--gray); color: white; text-align: center; padding: 12px;">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Tips Card -->
                <div class="card" style="margin-top: 20px;">
                    <div class="section-header">
                        <h3>💡 Tips for Successful Groups</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        <div class="success-box">
                            <h4><i class="fas fa-bullseye"></i> Clear Purpose</h4>
                            <p style="margin: 8px 0 0 0; font-size: 0.9rem;">Define specific goals and objectives for your group</p>
                        </div>
                        <div class="info-box">
                            <h4><i class="fas fa-users"></i> Active Engagement</h4>
                            <p style="margin: 8px 0 0 0; font-size: 0.9rem;">Post regularly and encourage member participation</p>
                        </div>
                        <div class="warning-box">
                            <h4><i class="fas fa-shield-alt"></i> Safe Environment</h4>
                            <p style="margin: 8px 0 0 0; font-size: 0.9rem;">Create a welcoming space for all members</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Character counter for description
document.getElementById('description').addEventListener('input', function() {
    const maxLength = 500;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    // You can add a character counter display here if needed
});
</script>

<?php include '../includes/footer.php'; ?>
