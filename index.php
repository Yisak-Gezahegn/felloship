<?php
$pageTitle = "Welcome - Haramaya Family Fellowship";
include 'includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero" style="background: linear-gradient(rgba(44, 90, 160, 0.8), rgba(30, 58, 111, 0.8)), linear-gradient(135deg, var(--spiritual-gold), var(--spiritual-blue)) center/cover; color: white; padding: 100px 0; text-align: center;">
        <div class="container">
            <h1 style="font-size: 3rem; margin-bottom: 20px;">Welcome to Kale Hiywot Family Fellowship</h1>
            <p style="font-size: 1.3rem; margin-bottom: 30px;">Building a Christ-centered community at Haramaya University</p>
            <div style="display: flex; gap: 20px; justify-content: center;">
                <a href="register.php" class="btn btn-primary" style="font-size: 1.2rem; padding: 15px 40px;">Join Us</a>
                <a href="login.php" class="btn" style="background: white; color: var(--primary-color); font-size: 1.2rem; padding: 15px 40px;">Login</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section style="padding: 60px 0;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 40px; font-size: 2.5rem;">About Our Fellowship</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                <div class="card" style="text-align: center;">
                    <i class="fas fa-bible" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 15px;"></i>
                    <h3>Bible Study</h3>
                    <p>Deep dive into God's Word through structured Bible study sessions and discussions.</p>
                </div>
                <div class="card" style="text-align: center;">
                    <i class="fas fa-praying-hands" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 15px;"></i>
                    <h3>Prayer & Worship</h3>
                    <p>Join us in powerful prayer sessions and uplifting worship experiences.</p>
                </div>
                <div class="card" style="text-align: center;">
                    <i class="fas fa-hands-helping" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 15px;"></i>
                    <h3>Community Service</h3>
                    <p>Serving our community through charity work and outreach programs.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Sections Overview -->
    <section style="background: var(--light-color); padding: 60px 0;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 40px; font-size: 2.5rem;">Our Sections</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <?php
                $sections = [
                    ['name' => 'Evangelism', 'icon' => 'fa-bullhorn', 'desc' => 'Spreading the Gospel', 'logo' => 'Evan-logo.jpg'],
                    ['name' => 'Bible Study', 'icon' => 'fa-book-open', 'desc' => 'Studying God\'s Word', 'logo' => 'Bs-logo.jpg'],
                    ['name' => 'Choirs', 'icon' => 'fa-music', 'desc' => 'Worship through Music', 'logo' => ''],
                    ['name' => 'Worship', 'icon' => 'fa-hands', 'desc' => 'Leading Praise', 'logo' => ''],
                    ['name' => 'Prayers', 'icon' => 'fa-praying-hands', 'desc' => 'Intercession Ministry', 'logo' => ''],
                    ['name' => 'Fundraising', 'icon' => 'fa-hand-holding-usd', 'desc' => 'Financial Support', 'logo' => ''],
                    ['name' => 'Charity', 'icon' => 'fa-heart', 'desc' => 'Helping the Needy', 'logo' => ''],
                    ['name' => 'LAD', 'icon' => 'fa-art', 'desc' => 'Leadership Training', 'logo' => ''],
                    ['name' => 'Media', 'icon' => 'fa-camera', 'desc' => 'Documentation', 'logo' => ''],
                    ['name' => 'Counseling', 'icon' => 'fa-comments', 'desc' => 'Spiritual Guidance', 'logo' => '']
                ];
                
                foreach ($sections as $section) {
                    if (!empty($section['logo'])) {
                        echo "
                        <div class='card' style='text-align: center; padding: 30px;'>
                            <img src='assets/images/{$section['logo']}' alt='{$section['name']}' style='width: 80px; height: 80px; object-fit: cover; border-radius: 50%; margin-bottom: 15px;'>
                            <h3>{$section['name']}</h3>
                            <p style='color: var(--gray);'>{$section['desc']}</p>
                        </div>
                        ";
                    } else {
                        echo "
                        <div class='card' style='text-align: center; padding: 30px;'>
                            <i class='fas {$section['icon']}' style='font-size: 2.5rem; color: var(--secondary-color); margin-bottom: 10px;'></i>
                            <h3>{$section['name']}</h3>
                            <p style='color: var(--gray);'>{$section['desc']}</p>
                        </div>
                        ";
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section style="padding: 60px 0; text-align: center;">
        <div class="container">
            <h2 style="font-size: 2.5rem; margin-bottom: 20px;">Ready to Join Our Community?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 30px; color: var(--gray);">Be part of a vibrant Christian community at Haramaya University</p>
            <a href="register.php" class="btn btn-primary" style="font-size: 1.2rem; padding: 15px 40px;">Sign Up Now</a>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
