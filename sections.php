<?php
$pageTitle = "Our Sections - Haramaya Fellowship";
include 'includes/header.php';

// Get sections from database
$sections_query = "SELECT s.*, u.full_name as leader_name, 
                   (SELECT COUNT(*) FROM users WHERE section_id = s.section_id AND status = 'approved') as member_count
                   FROM sections s
                   LEFT JOIN users u ON s.leader_id = u.user_id
                   ORDER BY s.section_name";
$sections_result = $conn->query($sections_query);

// Section logos mapping
$section_logos = [
    'EVAN' => 'Evan-logo.jpg',
    'BS' => 'Bs-logo.jpg',
    'CHOIR' => '',
    'WORSHIP' => '',
    'PRAYER' => '',
    'FUND' => '',
    'CHARITY' => '',
    'LAD' => '',
    'MEDIA' => '',
    'COUNSEL' => ''
];

$section_icons = [
    'EVAN' => 'fa-bullhorn',
    'BS' => 'fa-book-open',
    'CHOIR' => 'fa-music',
    'WORSHIP' => 'fa-hands',
    'PRAYER' => 'fa-praying-hands',
    'FUND' => 'fa-hand-holding-usd',
    'CHARITY' => 'fa-heart',
    'LAD' => 'fa-user-graduate',
    'MEDIA' => 'fa-camera',
    'COUNSEL' => 'fa-comments'
];
?>

<main style="padding: 40px 0;">
    <div class="container">
        <h1 style="text-align: center; margin-bottom: 20px; font-size: 2.5rem;">Our Fellowship Sections</h1>
        <p style="text-align: center; color: var(--gray); margin-bottom: 50px; font-size: 1.1rem;">
            Discover the various ministries and sections serving our fellowship community
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <?php while ($section = $sections_result->fetch_assoc()): ?>
                <div class="card" style="text-align: center; padding: 30px; transition: transform 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <?php if (!empty($section_logos[$section['section_code']])): ?>
                        <img src="assets/images/<?php echo $section_logos[$section['section_code']]; ?>" 
                             alt="<?php echo $section['section_name']; ?>" 
                             style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; margin-bottom: 20px; border: 3px solid var(--primary-color);">
                    <?php else: ?>
                        <div style="width: 100px; height: 100px; margin: 0 auto 20px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas <?php echo $section_icons[$section['section_code']]; ?>" style="font-size: 3rem; color: white;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h3 style="margin-bottom: 10px; color: var(--primary-color);"><?php echo $section['section_name']; ?></h3>
                    <p style="color: var(--gray); margin-bottom: 20px; min-height: 60px;"><?php echo $section['description']; ?></p>
                    
                    <div style="border-top: 1px solid var(--light-color); padding-top: 15px; margin-top: 15px;">
                        <?php if ($section['leader_name']): ?>
                            <p style="margin-bottom: 10px;">
                                <i class="fas fa-user-tie" style="color: var(--secondary-color);"></i>
                                <strong>Leader:</strong> <?php echo $section['leader_name']; ?>
                            </p>
                        <?php else: ?>
                            <p style="margin-bottom: 10px; color: var(--gray);">
                                <i class="fas fa-user-tie"></i>
                                <strong>Leader:</strong> Not assigned
                            </p>
                        <?php endif; ?>
                        
                        <p>
                            <i class="fas fa-users" style="color: var(--success-color);"></i>
                            <strong>Members:</strong> <?php echo $section['member_count']; ?>
                        </p>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="section-details.php?id=<?php echo $section['section_id']; ?>" class="btn btn-primary" style="margin-top: 20px; width: 100%;">
                            View Details
                        </a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php if (!isLoggedIn()): ?>
            <div style="text-align: center; margin-top: 50px;">
                <h3 style="margin-bottom: 20px;">Want to join a section?</h3>
                <a href="register.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 15px 40px;">
                    Register Now
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
