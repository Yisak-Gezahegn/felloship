<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

// Get the same posts query as home.php
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
                LIMIT 3";

$posts_result = $conn->query($posts_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Home Page</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .post-actions { display: flex; gap: 20px; padding-top: 15px; border-top: 1px solid #ddd; }
        .post-action { display: flex; align-items: center; gap: 5px; color: #6c757d; cursor: pointer; transition: color 0.3s; }
        .post-action:hover { color: #007bff; }
        #debug-log { background: #f8f9fa; padding: 15px; margin: 20px 0; border: 1px solid #ddd; border-radius: 5px; min-height: 200px; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <h1>Debug Home Page Structure</h1>
    
    <div class="debug-section">
        <h3>Current User Info:</h3>
        <p>User ID: <?php echo $_SESSION['user_id']; ?></p>
        <p>Username: <?php echo $_SESSION['username']; ?></p>
        <p>Full Name: <?php echo $_SESSION['full_name']; ?></p>
        <p>Role: <?php echo $_SESSION['role']; ?></p>
    </div>
    
    <div class="debug-section">
        <h3>Posts Query Result:</h3>
        <?php if ($posts_result): ?>
            <p>Query executed successfully. Found <?php echo $posts_result->num_rows; ?> posts.</p>
        <?php else: ?>
            <p style="color: red;">Query failed: <?php echo $conn->error; ?></p>
        <?php endif; ?>
    </div>
    
    <div class="debug-section">
        <h3>Generated Post HTML (First 3 posts):</h3>
        <?php if ($posts_result && $posts_result->num_rows > 0): ?>
            <?php while ($post = $posts_result->fetch_assoc()): ?>
                <div style="border: 2px solid #007bff; margin: 10px 0; padding: 15px; border-radius: 5px;">
                    <h4>Post ID: <?php echo $post['post_id']; ?></h4>
                    <p><strong>Content:</strong> <?php echo htmlspecialchars(substr($post['content'], 0, 100)); ?>...</p>
                    <p><strong>Author:</strong> <?php echo htmlspecialchars($post['full_name']); ?></p>
                    <p><strong>Like Count:</strong> <?php echo $post['like_count']; ?></p>
                    <p><strong>Pray Count:</strong> <?php echo $post['pray_count']; ?></p>
                    <p><strong>Comment Count:</strong> <?php echo $post['comment_count']; ?></p>
                    <p><strong>User Reaction:</strong> <?php echo $post['user_reaction'] ?: 'None'; ?></p>
                    
                    <!-- Test the exact same HTML structure as home.php -->
                    <div class="post-actions">
                        <span class="post-action" id="like-btn-<?php echo $post['post_id']; ?>" onclick="debugReactToPost(<?php echo $post['post_id']; ?>, 'like')" 
                              style="<?php echo $post['user_reaction'] === 'like' ? 'color: #007bff; font-weight: bold;' : ''; ?>">
                            👍 Like (<?php echo $post['like_count']; ?>)
                        </span>
                        <span class="post-action" id="pray-btn-<?php echo $post['post_id']; ?>" onclick="debugReactToPost(<?php echo $post['post_id']; ?>, 'pray')"
                              style="<?php echo $post['user_reaction'] === 'pray' ? 'color: #d4af37; font-weight: bold;' : ''; ?>">
                            🙏 Pray<?php echo $post['pray_count'] > 0 ? ' (' . $post['pray_count'] . ')' : ''; ?>
                        </span>
                        <span class="post-action" id="comment-btn-<?php echo $post['post_id']; ?>" onclick="debugToggleComments(<?php echo $post['post_id']; ?>)">
                            💬 Comment (<?php echo $post['comment_count']; ?>)
                        </span>
                        <span class="post-action" onclick="debugSharePost(<?php echo $post['post_id']; ?>)">
                            📤 Share
                        </span>
                    </div>
                    
                    <!-- Comment Section -->
                    <div id="comments-<?php echo $post['post_id']; ?>" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                        <div id="comments-list-<?php echo $post['post_id']; ?>" style="margin-bottom: 15px;">
                            <!-- Comments will be loaded here -->
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="comment-<?php echo $post['post_id']; ?>" placeholder="Write a comment..." style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <button onclick="debugCommentOnPost(<?php echo $post['post_id']; ?>)" style="padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Send</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No posts found.</p>
        <?php endif; ?>
    </div>
    
    <div id="debug-log">
        <strong>Debug Log:</strong><br>
        Page loaded...<br>
    </div>

    <script>
        function debugLog(message) {
            const logDiv = document.getElementById('debug-log');
            logDiv.innerHTML += new Date().toLocaleTimeString() + ': ' + message + '<br>';
            console.log(message);
        }

        function debugReactToPost(postId, reactionType) {
            debugLog('debugReactToPost called with postId: ' + postId + ', reactionType: ' + reactionType);
            alert('Debug: Reaction clicked! PostID: ' + postId + ', Reaction: ' + reactionType);
            
            // Test the actual API call
            fetch('api/react.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'post_id=' + postId + '&reaction_type=' + reactionType
            })
            .then(response => {
                debugLog('API Response status: ' + response.status);
                return response.text();
            })
            .then(text => {
                debugLog('API Raw response: ' + text);
                try {
                    const data = JSON.parse(text);
                    debugLog('API Parsed response: ' + JSON.stringify(data));
                    if (data.success) {
                        alert('API Success: ' + JSON.stringify(data));
                    } else {
                        alert('API Error: ' + data.error);
                    }
                } catch (e) {
                    debugLog('JSON parse error: ' + e.message);
                    alert('JSON Parse Error: ' + e.message + '\nRaw response: ' + text);
                }
            })
            .catch(error => {
                debugLog('Fetch error: ' + error.message);
                alert('Fetch Error: ' + error.message);
            });
        }

        function debugCommentOnPost(postId) {
            const commentInput = document.getElementById('comment-' + postId);
            const comment = commentInput.value.trim();
            
            debugLog('debugCommentOnPost called with postId: ' + postId + ', comment: ' + comment);
            alert('Debug: Comment clicked! PostID: ' + postId + ', Comment: "' + comment + '"');
            
            if (!comment) {
                alert('Please enter a comment');
                return;
            }
            
            // Test the actual API call
            fetch('api/comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'post_id=' + postId + '&comment=' + encodeURIComponent(comment)
            })
            .then(response => {
                debugLog('Comment API Response status: ' + response.status);
                return response.text();
            })
            .then(text => {
                debugLog('Comment API Raw response: ' + text);
                try {
                    const data = JSON.parse(text);
                    debugLog('Comment API Parsed response: ' + JSON.stringify(data));
                    if (data.success) {
                        commentInput.value = '';
                        alert('Comment Success: ' + JSON.stringify(data));
                    } else {
                        alert('Comment API Error: ' + data.error);
                    }
                } catch (e) {
                    debugLog('Comment JSON parse error: ' + e.message);
                    alert('Comment JSON Parse Error: ' + e.message + '\nRaw response: ' + text);
                }
            })
            .catch(error => {
                debugLog('Comment Fetch error: ' + error.message);
                alert('Comment Fetch Error: ' + error.message);
            });
        }

        function debugToggleComments(postId) {
            debugLog('debugToggleComments called with postId: ' + postId);
            const commentSection = document.getElementById('comments-' + postId);
            if (commentSection.style.display === 'none' || !commentSection.style.display) {
                commentSection.style.display = 'block';
                debugLog('Comments section shown for post: ' + postId);
            } else {
                commentSection.style.display = 'none';
                debugLog('Comments section hidden for post: ' + postId);
            }
        }

        function debugSharePost(postId) {
            debugLog('debugSharePost called with postId: ' + postId);
            alert('Debug: Share clicked! PostID: ' + postId);
        }

        // Test on page load
        document.addEventListener('DOMContentLoaded', function() {
            debugLog('✓ DOM loaded, JavaScript is working');
        });

        window.onload = function() {
            debugLog('✓ Window loaded');
            debugLog('debugReactToPost defined: ' + (typeof debugReactToPost !== 'undefined'));
            debugLog('debugCommentOnPost defined: ' + (typeof debugCommentOnPost !== 'undefined'));
        };
    </script>

    <p><a href="home.php">← Back to Home</a></p>
</body>
</html>