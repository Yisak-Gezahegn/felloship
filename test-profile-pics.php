<?php
$pageTitle = "Profile Picture Test - Haramaya Fellowship";
include 'includes/header.php';

requireLogin();
?>

<main style="padding: 25px 0;">
    <div class="container">
        <h2>🖼️ Profile Picture Placeholder Test</h2>
        
        <div class="card" style="margin-bottom: 20px;">
            <h3>Current User Profile Picture</h3>
            <div style="display: flex; align-items: center; gap: 15px; padding: 20px;">
                <?php if (!empty($_SESSION['profile_picture']) && $_SESSION['profile_picture'] !== 'default.jpg' && file_exists("uploads/profiles/{$_SESSION['profile_picture']}")): ?>
                <img src="uploads/profiles/<?php echo $_SESSION['profile_picture']; ?>" alt="Profile" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--spiritual-gold); box-shadow: var(--shadow-md);">
                <div>
                    <p><strong>Status:</strong> ✅ Has profile picture</p>
                    <p><strong>File:</strong> <?php echo $_SESSION['profile_picture']; ?></p>
                </div>
                <?php else: ?>
                <div class="profile-pic-default-large" style="width: 100px; height: 100px; border: 3px solid var(--spiritual-gold); box-shadow: var(--shadow-md);">
                    <i class="fas fa-user" style="font-size: 3rem;"></i>
                </div>
                <div>
                    <p><strong>Status:</strong> 📷 Using placeholder</p>
                    <p><strong>Reason:</strong> No profile picture uploaded</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card" style="margin-bottom: 20px;">
            <h3>Different Placeholder Sizes</h3>
            <div style="display: flex; align-items: center; gap: 20px; padding: 20px;">
                <div style="text-align: center;">
                    <div class="profile-pic-default" style="width: 30px; height: 30px; margin: 0 auto 10px;">
                        <i class="fas fa-user" style="font-size: 0.9rem;"></i>
                    </div>
                    <p style="font-size: 0.8rem;">Small (30px)</p>
                </div>
                
                <div style="text-align: center;">
                    <div class="profile-pic-default" style="width: 35px; height: 35px; margin: 0 auto 10px;">
                        <i class="fas fa-user" style="font-size: 1rem;"></i>
                    </div>
                    <p style="font-size: 0.8rem;">Medium (35px)</p>
                </div>
                
                <div style="text-align: center;">
                    <div class="profile-pic-default" style="width: 50px; height: 50px; margin: 0 auto 10px;">
                        <i class="fas fa-user" style="font-size: 1.5rem;"></i>
                    </div>
                    <p style="font-size: 0.8rem;">Large (50px)</p>
                </div>
                
                <div style="text-align: center;">
                    <div class="profile-pic-default-large" style="width: 100px; height: 100px; margin: 0 auto 10px;">
                        <i class="fas fa-user" style="font-size: 3rem;"></i>
                    </div>
                    <p style="font-size: 0.8rem;">Extra Large (100px)</p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h3>Sample Users with Placeholders</h3>
            <?php
            $sample_users = $conn->query("SELECT user_id, full_name, profile_picture, role FROM users LIMIT 5");
            if ($sample_users && $sample_users->num_rows > 0):
            ?>
            <div style="padding: 20px;">
                <?php while ($user = $sample_users->fetch_assoc()): ?>
                <div style="display: flex; align-items: center; gap: 15px; padding: 10px 0; border-bottom: 1px solid #e9ecef;">
                    <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'default.jpg' && file_exists("uploads/profiles/{$user['profile_picture']}")): ?>
                    <img src="uploads/profiles/<?php echo $user['profile_picture']; ?>" alt="Profile" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                        <p style="margin: 0; font-size: 0.9rem; color: var(--gray);">✅ Has profile picture</p>
                    </div>
                    <?php else: ?>
                    <div class="profile-pic-default" style="width: 50px; height: 50px;">
                        <i class="fas fa-user" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                        <p style="margin: 0; font-size: 0.9rem; color: var(--gray);">📷 Using placeholder</p>
                    </div>
                    <?php endif; ?>
                    <span style="margin-left: auto; padding: 5px 10px; background: var(--spiritual-blue); color: white; border-radius: 15px; font-size: 0.8rem;">
                        <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                    </span>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No Users Found</h3>
            </div>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="home.php" class="btn btn-primary">← Back to Home</a>
            <a href="profile.php" class="btn btn-secondary" style="margin-left: 10px;">Upload Profile Picture</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>