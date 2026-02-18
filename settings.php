<?php
$pageTitle = "Settings - Haramaya Fellowship";
include 'includes/header.php';

requireLogin();

$success = '';
$error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required!";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        // Verify current password
        $user = $conn->query("SELECT password FROM users WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to change password!";
            }
        } else {
            $error = "Current password is incorrect!";
        }
    }
}

// Handle notification settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notifications'])) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $post_notifications = isset($_POST['post_notifications']) ? 1 : 0;
    $comment_notifications = isset($_POST['comment_notifications']) ? 1 : 0;
    $group_notifications = isset($_POST['group_notifications']) ? 1 : 0;
    
    // For now, just show success (you can add a notifications table later)
    $success = "Notification settings updated successfully!";
}

// Get user data
$user = $conn->query("SELECT * FROM users WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc();
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>⚙️ Settings</h2>
            <p>Manage your account settings and preferences</p>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success" style="margin: 20px 0;">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger" style="margin: 20px 0;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 25px;">
            <!-- Change Password -->
            <div class="card">
                <div class="section-header">
                    <h3>🔒 Change Password</h3>
                </div>

                <form method="POST" style="padding: 20px;">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                        <small style="color: var(--gray); font-size: 0.85rem;">Minimum 6 characters</small>
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>

            <!-- Notification Settings -->
            <div class="card">
                <div class="section-header">
                    <h3>🔔 Notification Settings</h3>
                </div>

                <form method="POST" style="padding: 20px;">
                    <input type="hidden" name="update_notifications" value="1">
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s;" onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                            <input type="checkbox" name="email_notifications" checked style="width: 18px; height: 18px;">
                            <div>
                                <strong style="display: block; color: var(--primary-color);">Email Notifications</strong>
                                <small style="color: var(--gray);">Receive email updates about activities</small>
                            </div>
                        </label>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s;" onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                            <input type="checkbox" name="post_notifications" checked style="width: 18px; height: 18px;">
                            <div>
                                <strong style="display: block; color: var(--primary-color);">New Posts</strong>
                                <small style="color: var(--gray);">Get notified about new posts</small>
                            </div>
                        </label>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s;" onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                            <input type="checkbox" name="comment_notifications" checked style="width: 18px; height: 18px;">
                            <div>
                                <strong style="display: block; color: var(--primary-color);">Comments</strong>
                                <small style="color: var(--gray);">Get notified when someone comments on your posts</small>
                            </div>
                        </label>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s;" onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                            <input type="checkbox" name="group_notifications" checked style="width: 18px; height: 18px;">
                            <div>
                                <strong style="display: block; color: var(--primary-color);">Group Messages</strong>
                                <small style="color: var(--gray);">Get notified about group messages</small>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Preferences
                    </button>
                </form>
            </div>

            <!-- Account Information -->
            <div class="card">
                <div class="section-header">
                    <h3>📋 Account Information</h3>
                </div>

                <div style="padding: 20px;">
                    <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <strong style="display: block; color: var(--gray); font-size: 0.85rem; margin-bottom: 5px;">Email</strong>
                        <p style="color: var(--primary-color); margin: 0;"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>

                    <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <strong style="display: block; color: var(--gray); font-size: 0.85rem; margin-bottom: 5px;">Role</strong>
                        <p style="color: var(--primary-color); margin: 0;"><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></p>
                    </div>

                    <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <strong style="display: block; color: var(--gray); font-size: 0.85rem; margin-bottom: 5px;">Account Status</strong>
                        <p style="color: var(--success-color); margin: 0;">
                            <i class="fas fa-check-circle"></i> <?php echo ucfirst($user['status']); ?>
                        </p>
                    </div>

                    <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <strong style="display: block; color: var(--gray); font-size: 0.85rem; margin-bottom: 5px;">Member Since</strong>
                        <p style="color: var(--primary-color); margin: 0;"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                    </div>

                    <a href="profile.php" class="btn btn-gold" style="width: 100%;">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </a>
                </div>
            </div>

            <!-- Privacy & Security -->
            <div class="card">
                <div class="section-header">
                    <h3>🛡️ Privacy & Security</h3>
                </div>

                <div style="padding: 20px;">
                    <div style="margin-bottom: 20px;">
                        <h4 style="color: var(--primary-color); font-size: 1rem; margin-bottom: 10px;">Account Visibility</h4>
                        <p style="color: var(--gray); font-size: 0.9rem; line-height: 1.6;">
                            Your profile is visible to all fellowship members. Posts and comments you make are visible according to their privacy settings.
                        </p>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <h4 style="color: var(--primary-color); font-size: 1rem; margin-bottom: 10px;">Data Privacy</h4>
                        <p style="color: var(--gray); font-size: 0.9rem; line-height: 1.6;">
                            Your personal information is kept secure and is only shared with fellowship administrators and leaders as needed.
                        </p>
                    </div>

                    <div style="padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">
                        <strong style="display: block; color: #856404; margin-bottom: 5px;">
                            <i class="fas fa-exclamation-triangle"></i> Need Help?
                        </strong>
                        <p style="color: #856404; font-size: 0.85rem; margin: 0;">
                            Contact the fellowship coordinator if you have privacy concerns or need to delete your account.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="dashboard.php" class="btn" style="background: var(--gray); color: white;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</main>

<style>
@media (max-width: 768px) {
    main .container > div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
