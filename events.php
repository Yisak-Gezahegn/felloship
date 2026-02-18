<?php
$pageTitle = "Events - Haramaya Fellowship";
include 'includes/header.php';

requireLogin();

// Get event statistics
$total_events = $conn->query("SELECT COUNT(*) as count FROM posts WHERE post_type = 'event'")->fetch_assoc()['count'];
$recent_count = $conn->query("SELECT COUNT(*) as count FROM posts WHERE post_type = 'event' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
$total_reactions = $conn->query("SELECT COUNT(*) as count FROM post_reactions pr JOIN posts p ON pr.post_id = p.post_id WHERE p.post_type = 'event'")->fetch_assoc()['count'];

// For now, we'll use created_at to simulate upcoming/past events since we don't have actual event dates
// In a real system, you'd have an events table with proper event_date fields
$upcoming_count = $conn->query("SELECT COUNT(*) as count FROM posts WHERE post_type = 'event' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];
$past_count = $total_events - $upcoming_count;

// Get upcoming events (recent posts marked as events)
$upcoming_events = $conn->query("SELECT p.*, u.full_name as author_name, u.profile_picture, s.section_name,
                                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id) as reaction_count,
                                (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count
                                FROM posts p 
                                JOIN users u ON p.user_id = u.user_id 
                                LEFT JOIN sections s ON u.section_id = s.section_id 
                                WHERE p.post_type = 'event' 
                                AND p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                ORDER BY p.created_at DESC");

// Get past events (older posts marked as events)
$past_events = $conn->query("SELECT p.*, u.full_name as author_name, u.profile_picture, s.section_name,
                            (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id) as reaction_count,
                            (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count
                            FROM posts p 
                            JOIN users u ON p.user_id = u.user_id 
                            LEFT JOIN sections s ON u.section_id = s.section_id 
                            WHERE p.post_type = 'event' 
                            AND p.created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
                            ORDER BY p.created_at DESC");

// Get all events for reference
$all_events = $conn->query("SELECT p.*, u.full_name as author_name, u.profile_picture, s.section_name,
                            (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id) as reaction_count,
                            (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count
                            FROM posts p 
                            JOIN users u ON p.user_id = u.user_id 
                            LEFT JOIN sections s ON u.section_id = s.section_id 
                            WHERE p.post_type = 'event'
                            ORDER BY p.created_at DESC");
?>

<main style="padding: 25px 0;">
    <div class="container">
        <div class="dashboard-welcome">
            <h2>📅 Events</h2>
            <p>Stay updated with fellowship events and activities</p>
        </div>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 25px 0;">
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-gold), #f4d03f);">
                <i class="fas fa-calendar-alt"></i>
                <h3><?php echo $total_events; ?></h3>
                <p>Total Events</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-blue), #667eea);">
                <i class="fas fa-calendar-check"></i>
                <h3><?php echo $upcoming_count; ?></h3>
                <p>Upcoming Events</p>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, var(--spiritual-purple), #764ba2);">
                <i class="fas fa-calendar-day"></i>
                <h3><?php echo $past_count; ?></h3>
                <p>Past Events</p>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="card" style="margin-bottom: 25px;">
            <div class="section-header">
                <h3>🔔 Upcoming Events</h3>
            </div>

            <?php if ($upcoming_events->num_rows > 0): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; padding: 20px;">
                <?php while ($event = $upcoming_events->fetch_assoc()): ?>
                <div style="background: linear-gradient(135deg, #fff, #f8f9fa); border-radius: var(--border-radius); overflow: hidden; border: 1px solid #e9ecef; transition: all 0.3s ease; box-shadow: var(--shadow-sm);" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-sm)'">
                    <?php if (!empty($event['image'])): ?>
                    <div style="height: 200px; overflow: hidden; background: linear-gradient(135deg, var(--spiritual-gold), var(--spiritual-blue));">
                        <img src="uploads/posts/<?php echo $event['image']; ?>" alt="Event" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <?php else: ?>
                    <div style="height: 200px; background: linear-gradient(135deg, var(--spiritual-gold), var(--spiritual-blue)); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-calendar-alt" style="font-size: 4rem; color: white; opacity: 0.5;"></i>
                    </div>
                    <?php endif; ?>
                    
                    <div style="padding: 20px;">
                        <!-- Event Date Badge -->
                        <div style="display: inline-block; background: var(--spiritual-gold); color: var(--dark-color); padding: 8px 15px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; margin-bottom: 15px;">
                            <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($event['created_at'])); ?>
                            <i class="fas fa-clock" style="margin-left: 10px;"></i> <?php echo date('h:i A', strtotime($event['created_at'])); ?>
                        </div>
                        
                        <h3 style="color: var(--primary-color); margin-bottom: 10px; font-size: 1.2rem;">
                            Event by <?php echo htmlspecialchars($event['author_name']); ?>
                        </h3>
                        
                        <p style="color: var(--gray); font-size: 0.9rem; line-height: 1.6; margin-bottom: 15px;">
                            <?php echo nl2br(htmlspecialchars(substr($event['content'], 0, 150))); ?>
                            <?php if (strlen($event['content']) > 150) echo '...'; ?>
                        </p>
                        
                        <div style="display: flex; align-items: center; gap: 10px; padding-top: 15px; border-top: 1px solid #e9ecef; margin-top: 15px;">
                            <?php if (!empty($event['profile_picture']) && $event['profile_picture'] !== 'default.jpg' && file_exists("uploads/profiles/{$event['profile_picture']}")): ?>
                            <img src="uploads/profiles/<?php echo $event['profile_picture']; ?>" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                            <div class="profile-pic-default" style="width: 30px; height: 30px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <?php endif; ?>
                            <div style="flex: 1;">
                                <strong style="font-size: 0.85rem; color: var(--primary-color);"><?php echo htmlspecialchars($event['author_name']); ?></strong>
                                <?php if ($event['section_name']): ?>
                                <p style="font-size: 0.75rem; color: var(--gray); margin: 0;"><?php echo $event['section_name']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 15px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
                            <span style="font-size: 0.85rem; color: var(--gray);">
                                <i class="fas fa-heart" style="color: var(--danger-color);"></i> <?php echo $event['reaction_count']; ?>
                            </span>
                            <span style="font-size: 0.85rem; color: var(--gray);">
                                <i class="fas fa-comment" style="color: var(--spiritual-blue);"></i> <?php echo $event['comment_count']; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No Upcoming Events</h3>
                <p>Check back later for new events and activities</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Past Events -->
        <div class="card">
            <div class="section-header">
                <h3>📜 Past Events</h3>
            </div>

            <?php if ($past_events->num_rows > 0): ?>
            <div style="padding: 20px;">
                <?php while ($event = $past_events->fetch_assoc()): ?>
                <div style="display: flex; gap: 15px; padding: 15px; margin-bottom: 15px; background: #f8f9fa; border-radius: var(--border-radius); border-left: 4px solid var(--gray); transition: all 0.3s;" onmouseover="this.style.background='#e9ecef'" onmouseout="this.style.background='#f8f9fa'">
                    <?php if (!empty($event['image'])): ?>
                    <div style="width: 120px; height: 120px; border-radius: 8px; overflow: hidden; flex-shrink: 0;">
                        <img src="uploads/posts/<?php echo $event['image']; ?>" alt="Event" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <?php else: ?>
                    <div style="width: 120px; height: 120px; border-radius: 8px; background: linear-gradient(135deg, var(--gray), #6c757d); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-calendar-alt" style="font-size: 2rem; color: white; opacity: 0.5;"></i>
                    </div>
                    <?php endif; ?>
                    
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                            <h4 style="color: var(--primary-color); margin: 0; font-size: 1.1rem;">
                                Event by <?php echo htmlspecialchars($event['author_name']); ?>
                            </h4>
                            <span style="background: var(--gray); color: white; padding: 5px 12px; border-radius: 15px; font-size: 0.75rem; white-space: nowrap;">
                                <?php echo date('M d, Y', strtotime($event['created_at'])); ?>
                            </span>
                        </div>
                        
                        <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 10px; line-height: 1.5;">
                            <?php echo nl2br(htmlspecialchars(substr($event['content'], 0, 120))); ?>
                            <?php if (strlen($event['content']) > 120) echo '...'; ?>
                        </p>
                        
                        <div style="display: flex; gap: 20px; align-items: center;">
                            <span style="font-size: 0.85rem; color: var(--gray);">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($event['author_name']); ?>
                            </span>
                            <span style="font-size: 0.85rem; color: var(--gray);">
                                <i class="fas fa-heart" style="color: var(--danger-color);"></i> <?php echo $event['reaction_count']; ?>
                            </span>
                            <span style="font-size: 0.85rem; color: var(--gray);">
                                <i class="fas fa-comment" style="color: var(--spiritual-blue);"></i> <?php echo $event['comment_count']; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <h3>No Past Events</h3>
                <p>Past events will appear here</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
@media (max-width: 768px) {
    main .container > div[style*="grid-template-columns: repeat(auto-fill"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
