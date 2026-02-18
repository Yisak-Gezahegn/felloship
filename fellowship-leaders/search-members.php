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

$pageTitle = "Search Members - Haramaya Fellowship";
include '../includes/header.php';

$search_results = null;
$search_query = '';

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
    $search_safe = $conn->real_escape_string($search_query);
    
    $query = "SELECT u.*, s.section_name, f.family_name 
              FROM users u 
              LEFT JOIN sections s ON u.section_id = s.section_id 
              LEFT JOIN families f ON u.family_id = f.family_id
              WHERE (u.full_name LIKE '%$search_safe%' 
                     OR u.email LIKE '%$search_safe%' 
                     OR u.phone LIKE '%$search_safe%'
                     OR u.department LIKE '%$search_safe%')
              AND u.status = 'approved'
              ORDER BY u.full_name";
    
    $search_results = $conn->query($query);
}

// Get sections for advanced search
$sections = $conn->query("SELECT * FROM sections ORDER BY section_name");
?>

<main style="padding: 25px 0;">
    <div class="container">
        <!-- Page Header -->
        <div class="dashboard-welcome">
            <h2>🔍 Search Members</h2>
            <p>Find and connect with fellowship members</p>
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
                    <li><a href="create-group.php"><i class="fas fa-plus-circle"></i> Create Group</a></li>
                    <li><a href="../messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="financial-reports.php"><i class="fas fa-dollar-sign"></i> Financial Reports</a></li>
                    <li><a href="manage-users.php"><i class="fas fa-users-cog"></i> Manage Users</a></li>
                    <li><a href="search-members.php" class="active"><i class="fas fa-search"></i> Search Members</a></li>
                    <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </aside>
            
            <!-- Main Content -->
            <div class="main-content">
                <!-- Search Form -->
                <div class="card">
                    <div class="section-header">
                        <h3>Search for Members</h3>
                    </div>

                    <form method="GET" action="">
                        <div class="form-group">
                            <label for="search">
                                <i class="fas fa-search"></i> Search by Name, Email, Phone, or Department
                            </label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" 
                                       id="search" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Enter search term..."
                                       value="<?php echo htmlspecialchars($search_query); ?>"
                                       style="flex: 1;">
                                <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="info-box" style="margin-top: 20px;">
                        <h4><i class="fas fa-info-circle"></i> Search Tips</h4>
                        <ul style="margin: 10px 0 0 20px; color: var(--gray);">
                            <li>Enter full name or partial name</li>
                            <li>Search by email address</li>
                            <li>Search by phone number</li>
                            <li>Search by department or field of study</li>
                        </ul>
                    </div>
                </div>

                <!-- Search Results -->
                <?php if ($search_results !== null): ?>
                <div class="card">
                    <div class="section-header">
                        <h3>Search Results (<?php echo $search_results->num_rows; ?>)</h3>
                    </div>

                    <?php if ($search_results->num_rows > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                        <?php while ($member = $search_results->fetch_assoc()): ?>
                        <div class="card" style="margin: 0; transition: all 0.3s ease;">
                            <div style="display: flex; gap: 15px; align-items: start;">
                                <?php if (!empty($member['profile_picture']) && $member['profile_picture'] !== 'default.jpg' && file_exists("../uploads/profiles/{$member['profile_picture']}")): ?>
                                <img src="../uploads/profiles/<?php echo $member['profile_picture']; ?>" 
                                     alt="Profile" 
                                     style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid var(--spiritual-gold);">
                                <?php else: ?>
                                <div class="profile-pic-default-large" style="width: 70px; height: 70px;">
                                    <i class="fas fa-user" style="font-size: 2rem;"></i>
                                </div>
                                <?php endif; ?>

                                <div style="flex: 1;">
                                    <h4 style="color: var(--primary-color); margin-bottom: 5px;">
                                        <?php echo htmlspecialchars($member['full_name']); ?>
                                        <?php if ($member['is_online']): ?>
                                        <span style="color: var(--success-color); font-size: 0.8rem;"> ● Online</span>
                                        <?php endif; ?>
                                    </h4>
                                    <p style="font-size: 0.85rem; color: var(--gray); margin: 3px 0;">
                                        <i class="fas fa-user-tag"></i> <?php echo ucfirst(str_replace('_', ' ', $member['role'])); ?>
                                    </p>
                                    <?php if ($member['section_name']): ?>
                                    <p style="font-size: 0.85rem; color: var(--gray); margin: 3px 0;">
                                        <i class="fas fa-layer-group"></i> <?php echo $member['section_name']; ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
                                <p style="font-size: 0.85rem; color: var(--gray); margin: 5px 0;">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($member['email']); ?>
                                </p>
                                <?php if ($member['phone']): ?>
                                <p style="font-size: 0.85rem; color: var(--gray); margin: 5px 0;">
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($member['phone']); ?>
                                </p>
                                <?php endif; ?>
                                <?php if ($member['department']): ?>
                                <p style="font-size: 0.85rem; color: var(--gray); margin: 5px 0;">
                                    <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($member['department']); ?>
                                </p>
                                <?php endif; ?>
                            </div>

                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <button onclick="viewMemberDetails(<?php echo $member['user_id']; ?>)" 
                                        class="btn btn-primary" 
                                        style="flex: 1; padding: 10px; font-size: 0.9rem;">
                                    <i class="fas fa-info-circle"></i> View Details
                                </button>
                                <a href="../messages.php?user=<?php echo $member['user_id']; ?>" 
                                   class="btn btn-gold" 
                                   style="padding: 10px 15px; font-size: 0.9rem;">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h3>No Results Found</h3>
                        <p>No members match your search criteria. Try different keywords.</p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Browse by Section -->
                <div class="card">
                    <div class="section-header">
                        <h3>Browse by Section</h3>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                        <?php 
                        $sections->data_seek(0); // Reset pointer
                        while ($section = $sections->fetch_assoc()): 
                            $section_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE section_id = {$section['section_id']} AND status = 'approved'")->fetch_assoc()['count'];
                        ?>
                        <a href="?search=&section=<?php echo $section['section_id']; ?>" 
                           style="text-decoration: none;">
                            <div class="quick-action-btn" style="height: 100%;">
                                <i class="fas fa-layer-group" style="font-size: 2.5rem; color: var(--spiritual-purple);"></i>
                                <h4 style="margin: 10px 0 5px 0;"><?php echo $section['section_name']; ?></h4>
                                <p style="font-size: 0.85rem; color: var(--gray); margin: 0;">
                                    <?php echo $section_count; ?> members
                                </p>
                            </div>
                        </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Member Details Modal -->
<div id="memberModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: var(--border-radius); max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: var(--shadow-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: var(--primary-color); margin: 0;">Member Details</h3>
            <button onclick="closeMemberModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray);">×</button>
        </div>
        <div id="memberDetails">
            <div class="spiritual-loader"></div>
        </div>
    </div>
</div>

<script>
function viewMemberDetails(userId) {
    document.getElementById('memberModal').style.display = 'flex';
    document.getElementById('memberDetails').innerHTML = '<div class="spiritual-loader"></div>';
    
    // Simulate loading - in production, use AJAX to fetch details
    setTimeout(() => {
        document.getElementById('memberDetails').innerHTML = `
            <div class="info-box">
                <h4>Full Member Information</h4>
                <p>User ID: ${userId}</p>
                <p>This would show complete member details including:</p>
                <ul>
                    <li>Full contact information</li>
                    <li>Academic details</li>
                    <li>Fellowship involvement</li>
                    <li>Group memberships</li>
                    <li>Activity history</li>
                </ul>
            </div>
        `;
    }, 500);
}

function closeMemberModal() {
    document.getElementById('memberModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('memberModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMemberModal();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
