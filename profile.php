<?php
$pageTitle = "My Profile - Haramaya Fellowship";
include 'includes/header.php';

requireLogin();

$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    if (empty($full_name) || empty($email)) {
        $error = "Name and email are required!";
    } else {
        // Check if email is already taken by another user
        $email_check = $conn->query("SELECT user_id FROM users WHERE email = '$email' AND user_id != {$_SESSION['user_id']}");
        
        if ($email_check->num_rows > 0) {
            $error = "Email is already taken by another user!";
        } else {
            // Handle profile picture upload
            $profile_picture = $_SESSION['profile_picture'];
            
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_picture']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed)) {
                    $new_filename = time() . '_' . uniqid() . '.' . $ext;
                    $upload_path = 'uploads/profiles/' . $new_filename;
                    
                    if (!file_exists('uploads/profiles')) {
                        mkdir('uploads/profiles', 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                        // Delete old profile picture if it exists
                        if (!empty($profile_picture) && $profile_picture !== 'default.jpg' && file_exists('uploads/profiles/' . $profile_picture)) {
                            unlink('uploads/profiles/' . $profile_picture);
                        }
                        $profile_picture = $new_filename;
                    }
                }
            }
            
            // Update profile
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, bio = ?, profile_picture = ? WHERE user_id = ?");
            $stmt->bind_param("sssssi", $full_name, $email, $phone, $bio, $profile_picture, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $_SESSION['profile_picture'] = $profile_picture;
                $success = "Profile updated successfully!";
            } else {
                $error = "Failed to update profile!";
            }
        }
    }
}

// Get user data
$user = $conn->query("SELECT u.*, s.section_name 
                      FROM users u 
                      LEFT JOIN sections s ON u.section_id = s.section_id 
                      WHERE u.user_id = {$_SESSION['user_id']}")->fetch_assoc();

// Get user statistics
$total_posts = $conn->query("SELECT COUNT(*) as count FROM posts WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['count'];
$total_comments = $conn->query("SELECT COUNT(*) as count FROM comments WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['count'];
$total_groups = $conn->query("SELECT COUNT(*) as count FROM group_members WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['count'];
$member_since = date('F Y', strtotime($user['created_at']));
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>👤 My Profile</h2>
            <p>Manage your personal information and settings</p>
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

        <div style="display: grid; grid-template-columns: 300px 1fr; gap: 25px; margin-top: 25px;">
            <!-- Profile Summary Card -->
            <div class="card" style="height: fit-content;">
                <div style="text-align: center; padding: 20px;">
                    <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'default.jpg' && file_exists("uploads/profiles/{$user['profile_picture']}")): ?>
                    <img src="uploads/profiles/<?php echo $user['profile_picture']; ?>" alt="Profile" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 5px solid var(--spiritual-gold); margin-bottom: 15px;">
                    <?php else: ?>
                    <div class="profile-pic-default" style="width: 150px; height: 150px; margin: 0 auto 15px;">
                        <i class="fas fa-user" style="font-size: 4rem;"></i>
                    </div>
                    <?php endif; ?>
                    
                    <h3 style="color: var(--primary-color); margin-bottom: 5px;"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 10px;">
                        <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                    </p>
                    
                    <?php if ($user['section_name']): ?>
                    <p style="color: var(--spiritual-blue); font-size: 0.85rem; margin-bottom: 15px;">
                        <i class="fas fa-layer-group"></i> <?php echo $user['section_name']; ?>
                    </p>
                    <?php endif; ?>
                    
                    <div style="padding: 15px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 8px; margin-top: 15px;">
                        <p style="font-size: 0.85rem; color: var(--gray); margin-bottom: 8px;">
                            <i class="fas fa-calendar"></i> Member since
                        </p>
                        <strong style="color: var(--primary-color);"><?php echo $member_since; ?></strong>
                    </div>
                </div>
                
                <!-- Statistics -->
                <div style="border-top: 1px solid #e9ecef; padding: 15px;">
                    <div style="display: flex; justify-content: space-around; text-align: center;">
                        <div>
                            <strong style="display: block; font-size: 1.5rem; color: var(--spiritual-blue);"><?php echo $total_posts; ?></strong>
                            <span style="font-size: 0.75rem; color: var(--gray);">Posts</span>
                        </div>
                        <div>
                            <strong style="display: block; font-size: 1.5rem; color: var(--spiritual-gold);"><?php echo $total_comments; ?></strong>
                            <span style="font-size: 0.75rem; color: var(--gray);">Comments</span>
                        </div>
                        <div>
                            <strong style="display: block; font-size: 1.5rem; color: var(--spiritual-purple);"><?php echo $total_groups; ?></strong>
                            <span style="font-size: 0.75rem; color: var(--gray);">Groups</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Form -->
            <div class="card">
                <div class="section-header">
                    <h3>Edit Profile</h3>
                </div>

                <form method="POST" enctype="multipart/form-data" style="padding: 20px;">
                    <div class="form-group">
                        <label>Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-control" accept="image/*">
                        <small style="color: var(--gray); font-size: 0.85rem;">Accepted formats: JPG, PNG, GIF (Max 5MB)</small>
                    </div>

                    <div class="form-group">
                        <label>Full Name <span style="color: var(--danger-color);">*</span></label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email <span style="color: var(--danger-color);">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+251...">
                    </div>

                    <div class="form-group">
                        <label>Bio</label>
                        <textarea name="bio" class="form-control" rows="4" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" class="form-control" value="<?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>" disabled>
                        <small style="color: var(--gray); font-size: 0.85rem;">Contact admin to change your role</small>
                    </div>

                    <?php if ($user['section_name']): ?>
                    <div class="form-group">
                        <label>Section</label>
                        <input type="text" class="form-control" value="<?php echo $user['section_name']; ?>" disabled>
                        <small style="color: var(--gray); font-size: 0.85rem;">Contact admin to change your section</small>
                    </div>
                    <?php endif; ?>

                    <div style="display: flex; gap: 10px; margin-top: 25px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="dashboard.php" class="btn" style="background: var(--gray); color: white;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
@media (max-width: 768px) {
    main .container > div[style*="grid-template-columns: 300px 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
