<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

if (isLoggedIn()) {
    updateOnlineStatus($conn, $_SESSION['user_id']);
    $unread_notifications = getUnreadNotifications($conn, $_SESSION['user_id']);
    $unread_messages = getUnreadCount($conn, $_SESSION['user_id']);
}

$depth = substr_count($_SERVER['PHP_SELF'], '/') - 2;
$rel_path = str_repeat('../', max(0, $depth));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Haramaya University Kale Hiywot Family Fellowship'; ?></title>
    <link rel="stylesheet" href="<?php echo $rel_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="header-top-line"></div>
        
        <div class="container">
            <div class="header-content">
                <!-- Logo and Title -->
                <div class="logo-section">
                    <div class="logo-wrapper">
                        <img src="<?php echo $rel_path; ?>assets/images/fellowship-logo.jpg" alt="Fellowship Logo" class="logo">
                    </div>
                    <div class="header-text">
                        <h1>Kale Heywet Family Fellowship</h1>
                        <p class="fellowship-subtitle">Haramaya University</p>
                    </div>
                </div>
                
                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
                
                <!-- Navigation -->
                <?php if (isLoggedIn()): ?>
                <nav class="main-nav" id="mainNav">
                    <ul>
                        <li><a href="<?php echo $rel_path; ?>home.php" class="nav-link">
                            <i class="fas fa-home"></i><span>Home</span>
                        </a></li>
                        
                        <li><a href="<?php echo $rel_path; ?>groups.php" class="nav-link">
                            <i class="fas fa-users"></i><span>Groups</span>
                        </a></li>
                        
                        <li><a href="<?php echo $rel_path; ?>messages.php" class="nav-link">
                            <i class="fas fa-envelope"></i><span>Messages</span>
                            <?php if ($unread_messages > 0): ?>
                                <span class="notification-badge"><?php echo $unread_messages; ?></span>
                            <?php endif; ?>
                        </a></li>
                        
                        <li><a href="<?php echo $rel_path; ?>events.php" class="nav-link">
                            <i class="fas fa-calendar-alt"></i><span>Events</span>
                        </a></li>
                        
                        <?php if (hasMinRole('section_leader')): ?>
                        <li><a href="<?php echo $rel_path; ?>reports.php" class="nav-link">
                            <i class="fas fa-file-alt"></i><span>Reports</span>
                        </a></li>
                        <?php endif; ?>
                        
                        <!-- Notifications -->
                        <li class="dropdown">
                            <a href="#" class="nav-link">
                                <i class="fas fa-bell"></i>
                                <?php if ($unread_notifications > 0): ?>
                                    <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu notification-dropdown">
                                <div class="dropdown-header">
                                    <h4>Notifications</h4>
                                </div>
                                <div class="notification-list">
                                    <?php
                                    $notifications = $conn->query("SELECT * FROM notifications WHERE user_id = {$_SESSION['user_id']} ORDER BY created_at DESC LIMIT 5");
                                    if ($notifications && $notifications->num_rows > 0):
                                        while ($notif = $notifications->fetch_assoc()):
                                    ?>
                                    <a href="<?php echo $notif['link'] ? $rel_path . $notif['link'] : '#'; ?>" class="notification-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                                        <div>
                                            <strong><?php echo htmlspecialchars($notif['title']); ?></strong>
                                            <p><?php echo htmlspecialchars(substr($notif['message'], 0, 50)) . '...'; ?></p>
                                            <span class="time"><?php echo timeAgo($notif['created_at']); ?></span>
                                        </div>
                                    </a>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <div class="empty-notifications">
                                        <i class="fas fa-bell-slash"></i>
                                        <p>No notifications</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        
                        <!-- User Profile -->
                        <li class="dropdown user-dropdown">
                            <a href="#" class="profile-link">
                                <?php if (!empty($_SESSION['profile_picture']) && $_SESSION['profile_picture'] !== 'default.jpg' && file_exists($rel_path . 'uploads/profiles/' . $_SESSION['profile_picture'])): ?>
                                    <img src="<?php echo $rel_path; ?>uploads/profiles/<?php echo $_SESSION['profile_picture']; ?>" alt="Profile" class="profile-pic-small">
                                <?php else: ?>
                                    <div class="profile-pic-default">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <ul class="dropdown-menu profile-dropdown">
                                <li class="dropdown-user-info">
                                    <?php if (!empty($_SESSION['profile_picture']) && $_SESSION['profile_picture'] !== 'default.jpg' && file_exists($rel_path . 'uploads/profiles/' . $_SESSION['profile_picture'])): ?>
                                        <img src="<?php echo $rel_path; ?>uploads/profiles/<?php echo $_SESSION['profile_picture']; ?>" alt="Profile">
                                    <?php else: ?>
                                        <div class="profile-pic-default-large">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo $_SESSION['full_name']; ?></strong>
                                        <span><?php echo ucfirst(str_replace('_', ' ', $_SESSION['role'])); ?></span>
                                    </div>
                                </li>
                                <li><a href="<?php echo $rel_path; ?>profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                                <li><a href="<?php echo $rel_path; ?>dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                <li><a href="<?php echo $rel_path; ?>settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                                <li class="divider"></li>
                                <li><a href="<?php echo $rel_path; ?>logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
                <?php else: ?>
                <nav class="main-nav" id="mainNav">
                    <ul>
                        <li><a href="<?php echo $rel_path; ?>index.php" class="nav-link"><i class="fas fa-home"></i><span>Home</span></a></li>
                        <li><a href="<?php echo $rel_path; ?>sections.php" class="nav-link"><i class="fas fa-layer-group"></i><span>Sections</span></a></li>
                        <li><a href="<?php echo $rel_path; ?>about.php" class="nav-link"><i class="fas fa-info-circle"></i><span>About</span></a></li>
                        <li><a href="<?php echo $rel_path; ?>login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li><a href="<?php echo $rel_path; ?>register.php" class="btn-register"><i class="fas fa-user-plus"></i> Sign Up</a></li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <script>
    // Mobile menu toggle
    function toggleMobileMenu() {
        const nav = document.getElementById('mainNav');
        nav.classList.toggle('mobile-active');
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const nav = document.getElementById('mainNav');
        const toggle = document.querySelector('.mobile-menu-toggle');
        if (nav && toggle && !nav.contains(event.target) && !toggle.contains(event.target)) {
            nav.classList.remove('mobile-active');
        }
    });
    
    // Dropdown functionality for mobile
    document.addEventListener('DOMContentLoaded', function() {
        const dropdowns = document.querySelectorAll('.dropdown');
        
        dropdowns.forEach(dropdown => {
            const link = dropdown.querySelector('a');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (link && menu) {
                // For mobile: toggle on click
                link.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        dropdown.classList.toggle('active');
                        
                        // Close other dropdowns
                        dropdowns.forEach(other => {
                            if (other !== dropdown) {
                                other.classList.remove('active');
                            }
                        });
                    }
                });
            }
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                dropdowns.forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });
    });
    </script>
