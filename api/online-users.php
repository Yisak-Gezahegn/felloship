<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireLogin();

// Get online users
$online_query = "SELECT user_id, full_name, profile_picture, role FROM users WHERE is_online = TRUE AND user_id != ? ORDER BY full_name LIMIT 20";
$stmt = $conn->prepare($online_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$online_users = $stmt->get_result();
?>

<h3 style="margin-bottom: 15px;">Online Now</h3>
<?php if ($online_users->num_rows > 0): ?>
    <ul style="list-style: none;">
        <?php while ($user = $online_users->fetch_assoc()): ?>
            <li style="display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--light-color);">
                <?php 
                $profile_pic_path = !empty($user['profile_picture']) && $user['profile_picture'] !== 'default.jpg' && file_exists("../uploads/profiles/{$user['profile_picture']}")
                    ? "uploads/profiles/{$user['profile_picture']}" 
                    : null;
                ?>
                <?php if ($profile_pic_path): ?>
                <img src="<?php echo $profile_pic_path; ?>" alt="<?php echo $user['full_name']; ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                <div class="profile-pic-default" style="width: 35px; height: 35px;">
                    <i class="fas fa-user" style="font-size: 1rem;"></i>
                </div>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                <span style="width: 10px; height: 10px; background: var(--success-color); border-radius: 50%; margin-left: auto;"></span>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p style="color: var(--gray);">No users online</p>
<?php endif; ?>