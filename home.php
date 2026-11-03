<?php
$pageTitle = "Home - Haramaya Fellowship";
include 'includes/header.php';

requireLogin();
requireApproval();

// Get all posts with user info and reactions
$posts_query = "SELECT p.*, u.full_name, u.profile_picture, u.role,
                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id) as reaction_count,
                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id AND reaction_type = 'like') as like_count,
                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id AND reaction_type = 'pray') as pray_count,
                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id AND reaction_type = 'love') as love_count,
                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id AND reaction_type = 'amen') as amen_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count,
                (SELECT reaction_type FROM post_reactions WHERE post_id = p.post_id AND user_id = {$_SESSION['user_id']}) as user_reaction
                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                ORDER BY p.created_at DESC
                LIMIT 50";
$posts_result = $conn->query($posts_query);

// Get online users
$online_query = "SELECT user_id, full_name, profile_picture, role FROM users WHERE is_online = TRUE AND user_id != ? ORDER BY full_name LIMIT 20";
$stmt = $conn->prepare($online_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$online_users = $stmt->get_result();

// Get user's section info
$user_section = null;
if ($_SESSION['section_id']) {
    $user_section = $conn->query("SELECT * FROM sections WHERE section_id = {$_SESSION['section_id']}")->fetch_assoc();
}

// Get user's groups
$user_groups = $conn->query("SELECT g.*, (SELECT COUNT(*) FROM group_members WHERE group_id = g.group_id) as member_count
                             FROM groups g
                             JOIN group_members gm ON g.group_id = gm.group_id
                             WHERE gm.user_id = {$_SESSION['user_id']}
                             ORDER BY g.group_name
                             LIMIT 5");

// Get upcoming events
$upcoming_events = $conn->query("SELECT * FROM events WHERE event_date >= NOW() ORDER BY event_date LIMIT 5");
?>


<main style="padding: 25px 0; background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%); min-height: 100vh;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 280px 1fr 320px; gap: 25px;">
            
            <!-- Left Sidebar - User Profile -->
            <aside style="height: fit-content; position: sticky; top: 100px;">
                <div class="card" style="text-align: center;">
                    <?php if (!empty($_SESSION['profile_picture']) && $_SESSION['profile_picture'] !== 'default.jpg' && file_exists("uploads/profiles/{$_SESSION['profile_picture']}")): ?>
                    <img src="uploads/profiles/<?php echo $_SESSION['profile_picture']; ?>" alt="Profile" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--spiritual-gold); margin: 0 auto; box-shadow: var(--shadow-md);">
                    <?php else: ?>
                    <div class="profile-pic-default-large" style="width: 100px; height: 100px; margin: 0 auto; border: 3px solid var(--spiritual-gold); box-shadow: var(--shadow-md);">
                        <i class="fas fa-user" style="font-size: 3rem;"></i>
                    </div>
                    <?php endif; ?>
                    <h3 style="margin: 15px 0 5px; color: var(--primary-color);"><?php echo $_SESSION['full_name']; ?></h3>
                    <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 15px;">
                        <i class="fas fa-user-tag"></i> <?php echo ucfirst(str_replace('_', ' ', $_SESSION['role'])); ?>
                    </p>
                    <?php if ($user_section): ?>
                    <p style="color: var(--gray); font-size: 0.85rem; margin-bottom: 15px;">
                        <i class="fas fa-layer-group"></i> <?php echo $user_section['section_name']; ?>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Quick Links -->
                <div class="card" style="margin-top: 15px;">
                    <h4 style="color: var(--primary-color); margin-bottom: 15px; font-size: 1rem;">
                        <i class="fas fa-link"></i> Quick Links
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <a href="profile.php" style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: var(--dark-color); border-radius: 8px; transition: all 0.3s ease;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                            <i class="fas fa-user" style="color: var(--spiritual-purple); width: 20px;"></i> My Profile
                        </a>
                        <a href="dashboard.php" style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: var(--dark-color); border-radius: 8px; transition: all 0.3s ease;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                            <i class="fas fa-tachometer-alt" style="color: var(--spiritual-blue); width: 20px;"></i> Dashboard
                        </a>
                        <a href="groups.php" style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: var(--dark-color); border-radius: 8px; transition: all 0.3s ease;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                            <i class="fas fa-users" style="color: var(--spiritual-gold); width: 20px;"></i> My Groups
                        </a>
                        <a href="messages.php" style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: var(--dark-color); border-radius: 8px; transition: all 0.3s ease;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                            <i class="fas fa-envelope" style="color: var(--success-color); width: 20px;"></i> Messages
                        </a>
                        <a href="sections.php" style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: var(--dark-color); border-radius: 8px; transition: all 0.3s ease;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                            <i class="fas fa-layer-group" style="color: var(--danger-color); width: 20px;"></i> Sections
                        </a>
                    </div>
                </div>

                <!-- My Groups Preview -->
                <?php if ($user_groups->num_rows > 0): ?>
                <div class="card" style="margin-top: 15px;">
                    <h4 style="color: var(--primary-color); margin-bottom: 15px; font-size: 1rem;">
                        <i class="fas fa-users"></i> My Groups
                    </h4>
                    <?php while ($group = $user_groups->fetch_assoc()): ?>
                    <div style="padding: 8px 0; border-bottom: 1px solid #e9ecef;">
                        <a href="group-chat.php?id=<?php echo $group['group_id']; ?>" style="text-decoration: none; color: var(--dark-color);">
                            <strong style="font-size: 0.9rem;"><?php echo htmlspecialchars($group['group_name']); ?></strong>
                            <p style="font-size: 0.75rem; color: var(--gray); margin: 3px 0 0 0;">
                                <i class="fas fa-users"></i> <?php echo $group['member_count']; ?> members
                            </p>
                        </a>
                    </div>
                    <?php endwhile; ?>
                    <a href="groups.php" style="display: block; text-align: center; margin-top: 10px; color: var(--spiritual-blue); text-decoration: none; font-size: 0.85rem;">
                        View All Groups →
                    </a>
                </div>
                <?php endif; ?>
            </aside>
            
            <!-- Main Feed -->
            <div>
                <!-- Create Post -->
                <?php if (hasMinRole('family_leader')): ?>
                <div class="card">
                    <form action="api/create-post.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <textarea name="content" class="form-control" placeholder="What's on your mind, <?php echo $_SESSION['full_name']; ?>?" rows="3" required></textarea>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <label for="post-image" style="cursor: pointer; color: var(--primary-color);">
                                    <i class="fas fa-image"></i> Add Photo
                                </label>
                                <input type="file" id="post-image" name="image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                                
                                <select name="post_type" class="form-control" style="display: inline-block; width: auto; margin-left: 15px;">
                                    <option value="general">General Post</option>
                                    <option value="event">Event</option>
                                    <option value="announcement">Announcement</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Post</button>
                        </div>
                        <img id="image-preview" style="display: none; max-width: 100%; margin-top: 10px; border-radius: var(--border-radius);">
                    </form>
                </div>
                <?php endif; ?>
                
                <!-- Posts Feed -->
                <?php if ($posts_result->num_rows > 0): ?>
                    <?php while ($post = $posts_result->fetch_assoc()): ?>
                        <div class="post">
                            <div class="post-header">
                                <?php 
                                $profile_pic_path = !empty($post['profile_picture']) && $post['profile_picture'] !== 'default.jpg' && file_exists("uploads/profiles/{$post['profile_picture']}")
                                    ? "uploads/profiles/{$post['profile_picture']}" 
                                    : null;
                                ?>
                                <?php if ($profile_pic_path): ?>
                                <img src="<?php echo $profile_pic_path; ?>" alt="<?php echo $post['full_name']; ?>" class="post-avatar">
                                <?php else: ?>
                                <div class="profile-pic-default" style="width: 50px; height: 50px; border-radius: 50%;">
                                    <i class="fas fa-user" style="font-size: 1.5rem;"></i>
                                </div>
                                <?php endif; ?>
                                <div class="post-info">
                                    <h3><?php echo htmlspecialchars($post['full_name']); ?></h3>
                                    <span class="post-time"><?php echo timeAgo($post['created_at']); ?></span>
                                    <?php if ($post['post_type'] !== 'general'): ?>
                                        <span style="background: var(--secondary-color); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; margin-left: 10px;">
                                            <?php echo ucfirst($post['post_type']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="post-content">
                                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                            </div>
                            
                            <?php if ($post['image']): ?>
                                <img src="uploads/posts/<?php echo $post['image']; ?>" alt="Post image" class="post-image">
                            <?php endif; ?>
                            
                            <div class="post-actions">
                                <span class="post-action" id="like-btn-<?php echo $post['post_id']; ?>" onclick="reactToPost(<?php echo $post['post_id']; ?>, 'like')" 
                                      style="<?php echo $post['user_reaction'] === 'like' ? 'color: var(--spiritual-blue); font-weight: bold;' : ''; ?>">
                                    <i class="fas fa-thumbs-up"></i> Like (<?php echo $post['like_count']; ?>)
                                </span>
                                <span class="post-action" id="pray-btn-<?php echo $post['post_id']; ?>" onclick="reactToPost(<?php echo $post['post_id']; ?>, 'pray')"
                                      style="<?php echo $post['user_reaction'] === 'pray' ? 'color: var(--spiritual-gold); font-weight: bold;' : ''; ?>">
                                    <i class="fas fa-praying-hands"></i> Pray<?php echo $post['pray_count'] > 0 ? ' (' . $post['pray_count'] . ')' : ''; ?>
                                </span>
                                <span class="post-action" id="comment-btn-<?php echo $post['post_id']; ?>" onclick="toggleComments(<?php echo $post['post_id']; ?>)">
                                    <i class="fas fa-comment"></i> Comment (<?php echo $post['comment_count']; ?>)
                                </span>
                                <span class="post-action" onclick="sharePost(<?php echo $post['post_id']; ?>)">
                                    <i class="fas fa-share"></i> Share
                                </span>
                            </div>
                            
                            <!-- Comment Section -->
                            <div id="comments-<?php echo $post['post_id']; ?>" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--light-color);">
                                <!-- Comments List -->
                                <div id="comments-list-<?php echo $post['post_id']; ?>" style="margin-bottom: 15px;">
                                    <!-- Comments will be loaded here -->
                                </div>
                                
                                <!-- Add Comment -->
                                <div style="display: flex; gap: 10px;">
                                    <input type="text" id="comment-<?php echo $post['post_id']; ?>" class="form-control" placeholder="Write a comment..." onkeypress="if(event.key==='Enter') commentOnPost(<?php echo $post['post_id']; ?>)">
                                    <button class="btn btn-primary" onclick="commentOnPost(<?php echo $post['post_id']; ?>)">Send</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="card text-center">
                        <p>No posts yet. Be the first to share something!</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Sidebar -->
            <aside>
                <!-- Online Users -->
                <div class="card" id="online-users">
                    <h3 style="margin-bottom: 15px;">Online Now</h3>
                    <?php if ($online_users->num_rows > 0): ?>
                        <ul style="list-style: none;">
                            <?php while ($user = $online_users->fetch_assoc()): ?>
                                <li style="display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--light-color);">
                                    <?php 
                                    $profile_pic_path = !empty($user['profile_picture']) && $user['profile_picture'] !== 'default.jpg' && file_exists("uploads/profiles/{$user['profile_picture']}")
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
                </div>
                
                <!-- Upcoming Events -->
                <div class="card" style="margin-top: 20px;">
                    <h3 style="margin-bottom: 15px;">Upcoming Events</h3>
                    <?php
                    $events_query = "SELECT * FROM events WHERE event_date >= NOW() ORDER BY event_date LIMIT 5";
                    $events_result = $conn->query($events_query);
                    
                    if ($events_result->num_rows > 0):
                        while ($event = $events_result->fetch_assoc()):
                    ?>
                        <div style="padding: 10px 0; border-bottom: 1px solid var(--light-color);">
                            <h4 style="font-size: 0.95rem; margin-bottom: 5px;"><?php echo $event['title']; ?></h4>
                            <p style="font-size: 0.85rem; color: var(--gray);">
                                <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                            </p>
                        </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <p style="color: var(--gray);">No upcoming events</p>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</main>

<script>
// Image preview function
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

// React to post function
function reactToPost(postId, reactionType) {
    fetch('api/react.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `post_id=${postId}&reaction_type=${reactionType}`
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                updateReactionButtons(postId, data);
            } else {
                console.error('Reaction failed:', data.error);
            }
        } catch (e) {
            console.error('Reaction parse error:', e, text);
        }
    })
    .catch(error => console.error('Reaction error:', error));
}

// Update reaction buttons based on API response
function updateReactionButtons(postId, data) {
    const reactionCounts = data.reaction_counts || {};
    const userCurrentReaction = data.user_current_reaction;

    // Update like button
    const likeButton = document.getElementById(`like-btn-${postId}`);
    if (likeButton) {
        const likeCount = reactionCounts.like || 0;
        likeButton.innerHTML = `<i class="fas fa-thumbs-up"></i> Like (${likeCount})`;
        likeButton.style.color = userCurrentReaction === 'like' ? 'var(--spiritual-blue)' : '';
        likeButton.style.fontWeight = userCurrentReaction === 'like' ? 'bold' : '';
    }

    // Update pray button
    const prayButton = document.getElementById(`pray-btn-${postId}`);
    if (prayButton) {
        const prayCount = reactionCounts.pray || 0;
        prayButton.innerHTML = `<i class="fas fa-praying-hands"></i> Pray${prayCount > 0 ? ` (${prayCount})` : ''}`;
        prayButton.style.color = userCurrentReaction === 'pray' ? 'var(--spiritual-gold)' : '';
        prayButton.style.fontWeight = userCurrentReaction === 'pray' ? 'bold' : '';
    }
}

// Comment on post function
function commentOnPost(postId) {
    const commentInput = document.getElementById(`comment-${postId}`);
    const comment = commentInput.value.trim();

    if (!comment) {
        return;
    }

    fetch('api/comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${postId}&comment=${encodeURIComponent(comment)}`
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                commentInput.value = '';
                loadComments(postId);
                // Increment comment count
                const commentButton = document.getElementById(`comment-btn-${postId}`);
                if (commentButton) {
                    const match = commentButton.innerHTML.match(/Comment \((\d+)\)/);
                    if (match) {
                        const newCount = parseInt(match[1]) + 1;
                        commentButton.innerHTML = commentButton.innerHTML.replace(/Comment \(\d+\)/, `Comment (${newCount})`);
                    }
                }
            } else {
                console.error('Comment failed:', data.error);
            }
        } catch (e) {
            console.error('Comment parse error:', e, text);
        }
    })
    .catch(error => console.error('Comment error:', error));
}

// Share post function
function sharePost(postId) {
    const url = `${window.location.origin}${window.location.pathname.replace(/\/[^/]*$/, '')}/home.php#post-${postId}`;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            alert('Post link copied to clipboard!');
        }).catch(() => fallbackCopy(url));
    } else {
        fallbackCopy(url);
    }
}

function fallbackCopy(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
    alert('Post link copied to clipboard!');
}

// Toggle comments section
function toggleComments(postId) {
    const commentSection = document.getElementById(`comments-${postId}`);
    if (commentSection.style.display === 'none' || !commentSection.style.display) {
        commentSection.style.display = 'block';
        // Load comments if not already loaded
        loadComments(postId);
    } else {
        commentSection.style.display = 'none';
    }
}

// Load comments for a post
function loadComments(postId) {
    fetch(`api/get-comments.php?post_id=${postId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById(`comments-list-${postId}`).innerHTML = html;
        })
        .catch(error => console.error('Error loading comments:', error));
}

// Update online users every 30 seconds
setInterval(() => {
    fetch('api/online-users.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('online-users').innerHTML = html;
        })
        .catch(error => console.error('Error updating online users:', error));
}, 30000);
</script>

<?php include 'includes/footer.php'; ?>
