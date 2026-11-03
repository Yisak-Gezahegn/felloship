<?php
$pageTitle = "Section Details - Haramaya Fellowship";
include 'includes/header.php';

requireLogin();
requireApproval();

$section_id = intval($_GET['id'] ?? 0);
if (!$section_id) {
    header('Location: sections.php');
    exit();
}

// Get section info
$stmt = $conn->prepare("SELECT s.*, u.full_name as leader_name, u.email as leader_email
                        FROM sections s
                        LEFT JOIN users u ON s.leader_id = u.user_id
                        WHERE s.section_id = ?");
$stmt->bind_param("i", $section_id);
$stmt->execute();
$section = $stmt->get_result()->fetch_assoc();

if (!$section) {
    header('Location: sections.php');
    exit();
}

// Get section members
$members_stmt = $conn->prepare("SELECT user_id, full_name, profile_picture, role, is_online, department, current_year
                                FROM users
                                WHERE section_id = ? AND status = 'approved'
                                ORDER BY full_name");
$members_stmt->bind_param("i", $section_id);
$members_stmt->execute();
$members = $members_stmt->get_result();
$member_count = $members->num_rows;

// Get recent posts from this section's members
$posts_stmt = $conn->prepare("SELECT p.*, u.full_name, u.profile_picture
                               FROM posts p
                               JOIN users u ON p.user_id = u.user_id
                               WHERE u.section_id = ?
                               ORDER BY p.created_at DESC
                               LIMIT 10");
$posts_stmt->bind_param("i", $section_id);
$posts_stmt->execute();
$posts = $posts_stmt->get_result();
?>

<main style="padding: 40px 0;">
    <div class="container">
        <a href="sections.php" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary-color); text-decoration: none; margin-bottom: 25px; font-weight: 500;">
            <i class="fas fa-arrow-left"></i> Back to Sections
        </a>

        <!-- Section Header -->
        <div class="dashboard-welcome" style="margin-bottom: 30px;">
            <h2><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($section['section_name']); ?></h2>
            <p><?php echo htmlspecialchars($section['description'] ?? ''); ?></p>
            <div class="spiritual-divider" style="background: rgba(255,255,255,0.3); margin: 15px 0;"></div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                <i class="fas fa-tag"></i> Code: <?php echo htmlspecialchars($section['section_code']); ?> &nbsp;|&nbsp;
                <i class="fas fa-users"></i> <?php echo $member_count; ?> Members
                <?php if ($section['leader_name']): ?>
                &nbsp;|&nbsp; <i class="fas fa-user-tie"></i> Leader: <?php echo htmlspecialchars($section['leader_name']); ?>
                <?php endif; ?>
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 320px; gap: 25px;">

            <!-- Members List -->
            <div>
                <div class="card">
                    <div class="section-header">
                        <h3><i class="fas fa-users"></i> Section Members (<?php echo $member_count; ?>)</h3>
                    </div>
                    <?php if ($member_count > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; padding: 20px 0;">
                        <?php
                        $members->data_seek(0);
                        while ($member = $members->fetch_assoc()):
                            $pic = !empty($member['profile_picture']) && $member['profile_picture'] !== 'default.jpg'
                                ? 'uploads/profiles/' . htmlspecialchars($member['profile_picture'])
                                : null;
                        ?>
                        <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: var(--border-radius);">
                            <?php if ($pic && file_exists($pic)): ?>
                                <img src="<?php echo $pic; ?>" alt="<?php echo htmlspecialchars($member['full_name']); ?>"
                                     style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid var(--spiritual-gold); margin-bottom: 10px;">
                            <?php else: ?>
                                <div class="profile-pic-default" style="width: 60px; height: 60px; margin: 0 auto 10px;">
                                    <i class="fas fa-user" style="font-size: 1.5rem;"></i>
                                </div>
                            <?php endif; ?>
                            <p style="font-weight: 600; font-size: 0.9rem; margin-bottom: 4px;"><?php echo htmlspecialchars($member['full_name']); ?></p>
                            <p style="font-size: 0.78rem; color: var(--gray); margin-bottom: 4px;"><?php echo htmlspecialchars($member['department'] ?? ''); ?></p>
                            <span style="color: <?php echo $member['is_online'] ? 'var(--success-color)' : 'var(--gray)'; ?>; font-size: 0.78rem;">
                                <?php echo $member['is_online'] ? '● Online' : '○ Offline'; ?>
                            </span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Members Yet</h3>
                        <p>Be the first to join this section!</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Posts from this Section -->
                <?php if ($posts->num_rows > 0): ?>
                <div class="card" style="margin-top: 20px;">
                    <div class="section-header">
                        <h3><i class="fas fa-file-alt"></i> Recent Posts</h3>
                    </div>
                    <div style="display: grid; gap: 15px; padding-top: 10px;">
                        <?php while ($post = $posts->fetch_assoc()): ?>
                        <div style="padding: 15px; background: #f8f9fa; border-radius: var(--border-radius); border-left: 4px solid var(--spiritual-purple);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <strong style="color: var(--primary-color);"><?php echo htmlspecialchars($post['full_name']); ?></strong>
                                <span style="font-size: 0.82rem; color: var(--gray);"><?php echo timeAgo($post['created_at']); ?></span>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))) . (strlen($post['content']) > 200 ? '...' : ''); ?></p>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Sidebar -->
            <aside>
                <div class="card">
                    <div class="section-header">
                        <h3>Section Info</h3>
                    </div>
                    <div style="padding: 10px 0;">
                        <p style="margin-bottom: 12px;"><i class="fas fa-layer-group" style="color: var(--spiritual-purple); width: 20px;"></i> <strong>Name:</strong> <?php echo htmlspecialchars($section['section_name']); ?></p>
                        <p style="margin-bottom: 12px;"><i class="fas fa-tag" style="color: var(--spiritual-gold); width: 20px;"></i> <strong>Code:</strong> <?php echo htmlspecialchars($section['section_code']); ?></p>
                        <p style="margin-bottom: 12px;"><i class="fas fa-users" style="color: var(--success-color); width: 20px;"></i> <strong>Members:</strong> <?php echo $member_count; ?></p>
                        <?php if ($section['leader_name']): ?>
                        <p style="margin-bottom: 12px;"><i class="fas fa-user-tie" style="color: var(--spiritual-blue); width: 20px;"></i> <strong>Leader:</strong> <?php echo htmlspecialchars($section['leader_name']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card" style="margin-top: 15px; text-align: center;">
                    <i class="fas fa-door-open" style="font-size: 3rem; color: var(--spiritual-gold); margin-bottom: 15px; display: block;"></i>
                    <h4 style="margin-bottom: 10px;">Want to join?</h4>
                    <p style="font-size: 0.9rem; color: var(--gray); margin-bottom: 15px;">Contact the section leader or update your profile section preference.</p>
                    <a href="settings.php" class="btn btn-primary" style="width: 100%;">Update My Section</a>
                </div>
            </aside>
        </div>
    </div>
</main>

<style>
@media (max-width: 768px) {
    main .container > div[style*="grid-template-columns: 1fr 320px"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
