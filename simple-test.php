<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

// Get one test post
$test_post = $conn->query("SELECT post_id, content FROM posts LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-button { 
            padding: 10px 20px; 
            margin: 10px; 
            background: #007bff; 
            color: white; 
            border: none; 
            cursor: pointer; 
            border-radius: 5px;
        }
        .test-button:hover { background: #0056b3; }
        #results { 
            background: #f8f9fa; 
            padding: 15px; 
            margin: 20px 0; 
            border: 1px solid #ddd; 
            border-radius: 5px;
            min-height: 100px;
        }
    </style>
</head>
<body>
    <h1>Simple Reaction Test</h1>
    
    <?php if ($test_post): ?>
        <p><strong>Test Post:</strong> <?php echo htmlspecialchars(substr($test_post['content'], 0, 100)); ?>...</p>
        <p><strong>Post ID:</strong> <?php echo $test_post['post_id']; ?></p>
        
        <div>
            <button class="test-button" onclick="testBasicAlert()">Test Basic Alert</button>
            <button class="test-button" onclick="alert('Inline onclick works!')">Test Inline Alert</button>
            <button class="test-button" onclick="testReaction(<?php echo $test_post['post_id']; ?>)">Test Like Reaction</button>
            <button class="test-button" onclick="testComment(<?php echo $test_post['post_id']; ?>)">Test Comment</button>
            <button class="test-button" onclick="testFetch()">Test Fetch API</button>
        </div>
        
        <!-- Test the exact same structure as home.php -->
        <div style="margin: 20px 0; padding: 20px; border: 2px solid #007bff; border-radius: 5px;">
            <h3>Exact Home.php Structure Test:</h3>
            <div class="post-actions" style="display: flex; gap: 20px; padding-top: 15px;">
                <span class="post-action" id="like-btn-<?php echo $test_post['post_id']; ?>" onclick="reactToPost(<?php echo $test_post['post_id']; ?>, 'like')" style="display: flex; align-items: center; gap: 5px; color: #6c757d; cursor: pointer;">
                    <i class="fas fa-thumbs-up"></i> Like (0)
                </span>
                <span class="post-action" id="pray-btn-<?php echo $test_post['post_id']; ?>" onclick="reactToPost(<?php echo $test_post['post_id']; ?>, 'pray')" style="display: flex; align-items: center; gap: 5px; color: #6c757d; cursor: pointer;">
                    <i class="fas fa-praying-hands"></i> Pray
                </span>
                <span class="post-action" onclick="alert('Comment clicked!')" style="display: flex; align-items: center; gap: 5px; color: #6c757d; cursor: pointer;">
                    <i class="fas fa-comment"></i> Comment (0)
                </span>
            </div>
        </div>
        
        <div id="results">
            <h3>Test Results:</h3>
            <p>Click buttons above to test functionality...</p>
        </div>
        
    <?php else: ?>
        <p style="color: red;">No posts found! Please create a post first.</p>
    <?php endif; ?>

    <script>
        function log(message) {
            const results = document.getElementById('results');
            results.innerHTML += '<p>' + new Date().toLocaleTimeString() + ': ' + message + '</p>';
            console.log(message);
        }

        function testBasicAlert() {
            alert('Basic JavaScript is working!');
            log('✓ Basic alert test passed');
        }

        function testReaction(postId) {
            log('Testing reaction for post ID: ' + postId);
            
            fetch('api/react.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'post_id=' + postId + '&reaction_type=like'
            })
            .then(response => {
                log('Response status: ' + response.status);
                return response.text();
            })
            .then(text => {
                log('Raw response: ' + text);
                try {
                    const data = JSON.parse(text);
                    log('✓ JSON parsed successfully: ' + JSON.stringify(data));
                    if (data.success) {
                        log('✓ Reaction API call successful!');
                    } else {
                        log('✗ Reaction API returned error: ' + data.error);
                    }
                } catch (e) {
                    log('✗ JSON parse error: ' + e.message);
                }
            })
            .catch(error => {
                log('✗ Fetch error: ' + error.message);
            });
        }

        function testComment(postId) {
            const testComment = 'Test comment from simple test page';
            log('Testing comment for post ID: ' + postId);
            
            fetch('api/comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'post_id=' + postId + '&comment=' + encodeURIComponent(testComment)
            })
            .then(response => {
                log('Comment response status: ' + response.status);
                return response.text();
            })
            .then(text => {
                log('Comment raw response: ' + text);
                try {
                    const data = JSON.parse(text);
                    log('✓ Comment JSON parsed: ' + JSON.stringify(data));
                    if (data.success) {
                        log('✓ Comment API call successful!');
                    } else {
                        log('✗ Comment API returned error: ' + data.error);
                    }
                } catch (e) {
                    log('✗ Comment JSON parse error: ' + e.message);
                }
            })
            .catch(error => {
                log('✗ Comment fetch error: ' + error.message);
            });
        }

        function testFetch() {
            log('Testing basic fetch to home page...');
            fetch('home.php')
                .then(response => {
                    log('Home page fetch status: ' + response.status);
                    if (response.ok) {
                        log('✓ Basic fetch is working');
                    } else {
                        log('✗ Basic fetch failed');
                    }
                })
                .catch(error => {
                    log('✗ Basic fetch error: ' + error.message);
                });
        }

        // Test the exact same function as home.php
        function reactToPost(postId, reactionType) {
            log('reactToPost called with postId: ' + postId + ', reactionType: ' + reactionType);
            alert('reactToPost function called! PostID: ' + postId + ', Reaction: ' + reactionType);
            
            fetch('api/test-react.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'post_id=' + postId + '&reaction_type=' + reactionType
            })
            .then(response => {
                log('reactToPost response status: ' + response.status);
                return response.text();
            })
            .then(text => {
                log('reactToPost raw response: ' + text);
                try {
                    const data = JSON.parse(text);
                    log('✓ reactToPost JSON parsed: ' + JSON.stringify(data));
                    if (data.success) {
                        log('✓ reactToPost successful!');
                        alert('Reaction successful!');
                    } else {
                        log('✗ reactToPost error: ' + data.error);
                        alert('Reaction failed: ' + data.error);
                    }
                } catch (e) {
                    log('✗ reactToPost JSON parse error: ' + e.message);
                    alert('JSON parse error: ' + e.message);
                }
            })
            .catch(error => {
                log('✗ reactToPost fetch error: ' + error.message);
                alert('Fetch error: ' + error.message);
            });
        }

        // Test on page load
        document.addEventListener('DOMContentLoaded', function() {
            log('Page loaded, JavaScript is working');
        });
    </script>

    <p><a href="home.php">← Back to Home</a></p>
</body>
</html>