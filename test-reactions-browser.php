<?php
$pageTitle = "Test Reactions - Haramaya Fellowship";
include 'includes/header.php';

requireLogin();

// Get a test post
$post_result = $conn->query("SELECT p.*, u.full_name FROM posts p JOIN users u ON p.user_id = u.user_id LIMIT 1");
$post = $post_result->fetch_assoc();
?>

<main style="padding: 25px 0;">
    <div class="container">
        <h2>🧪 Reaction System Test</h2>
        
        <?php if ($post): ?>
        <div class="card" style="max-width: 600px; margin: 20px auto;">
            <h3>Test Post</h3>
            <div class="post">
                <div class="post-header">
                    <div class="post-info">
                        <h3><?php echo htmlspecialchars($post['full_name']); ?></h3>
                        <span class="post-time"><?php echo timeAgo($post['created_at']); ?></span>
                    </div>
                </div>
                
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
                
                <div class="post-actions">
                    <span class="post-action" id="like-btn-<?php echo $post['post_id']; ?>" onclick="testReaction(<?php echo $post['post_id']; ?>, 'like')" style="cursor: pointer; padding: 10px; margin: 5px; background: #f0f0f0; border-radius: 5px; display: inline-block;">
                        <i class="fas fa-thumbs-up"></i> Like (<span id="like-count-<?php echo $post['post_id']; ?>">0</span>)
                    </span>
                    <span class="post-action" id="pray-btn-<?php echo $post['post_id']; ?>" onclick="testReaction(<?php echo $post['post_id']; ?>, 'pray')" style="cursor: pointer; padding: 10px; margin: 5px; background: #f0f0f0; border-radius: 5px; display: inline-block;">
                        <i class="fas fa-praying-hands"></i> Pray (<span id="pray-count-<?php echo $post['post_id']; ?>">0</span>)
                    </span>
                    <span class="post-action" id="comment-btn-<?php echo $post['post_id']; ?>" onclick="testComment(<?php echo $post['post_id']; ?>)" style="cursor: pointer; padding: 10px; margin: 5px; background: #f0f0f0; border-radius: 5px; display: inline-block;">
                        <i class="fas fa-comment"></i> Comment
                    </span>
                </div>
            </div>
        </div>
        
        <div class="card" style="max-width: 600px; margin: 20px auto;">
            <h3>🔍 Debug Information</h3>
            <div id="debug-info" style="background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; max-height: 300px; overflow-y: auto;"></div>
        </div>
        
        <div class="card" style="max-width: 600px; margin: 20px auto;">
            <h3>🧪 Manual Tests</h3>
            <button onclick="testFetch()" class="btn btn-primary" style="margin: 5px;">Test Fetch API</button>
            <button onclick="testConsole()" class="btn btn-secondary" style="margin: 5px;">Test Console</button>
            <button onclick="clearDebug()" class="btn btn-warning" style="margin: 5px;">Clear Debug</button>
        </div>
        
        <?php else: ?>
        <div class="card">
            <p>No posts found for testing. Please create a post first.</p>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
let debugInfo = document.getElementById('debug-info');

function addDebug(message) {
    const timestamp = new Date().toLocaleTimeString();
    debugInfo.textContent += `${timestamp}: ${message}\n`;
    debugInfo.scrollTop = debugInfo.scrollHeight;
    console.log(`${timestamp}: ${message}`);
}

function clearDebug() {
    debugInfo.textContent = '';
}

function testConsole() {
    addDebug('✓ Console and debug system working');
    console.log('Console test successful');
    alert('Console test - check debug info');
}

function testFetch() {
    addDebug('Testing fetch API...');
    fetch('api/online-users.php')
        .then(response => {
            addDebug(`Fetch response status: ${response.status}`);
            return response.text();
        })
        .then(text => {
            addDebug(`✓ Fetch API working - got ${text.length} characters`);
        })
        .catch(error => {
            addDebug(`✗ Fetch error: ${error.message}`);
        });
}

function testReaction(postId, reactionType) {
    addDebug(`🔄 Testing reaction: ${reactionType} on post ${postId}`);
    
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('reaction_type', reactionType);
    
    fetch('api/react.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        addDebug(`Response status: ${response.status}`);
        addDebug(`Response headers: ${JSON.stringify([...response.headers])}`);
        return response.text();
    })
    .then(text => {
        addDebug(`Raw response: ${text}`);
        try {
            const data = JSON.parse(text);
            addDebug(`Parsed JSON: ${JSON.stringify(data, null, 2)}`);
            
            if (data.success) {
                addDebug(`✓ Reaction successful: ${data.action} ${data.reaction_type}`);
                
                // Update UI
                if (data.reaction_counts) {
                    const likeCount = document.getElementById(`like-count-${postId}`);
                    const prayCount = document.getElementById(`pray-count-${postId}`);
                    
                    if (likeCount) likeCount.textContent = data.reaction_counts.like || 0;
                    if (prayCount) prayCount.textContent = data.reaction_counts.pray || 0;
                    
                    addDebug(`Updated counts - Like: ${data.reaction_counts.like || 0}, Pray: ${data.reaction_counts.pray || 0}`);
                }
                
                alert(`✓ Reaction ${data.action}: ${data.reaction_type}`);
            } else {
                addDebug(`✗ Reaction failed: ${data.error}`);
                alert(`✗ Reaction failed: ${data.error}`);
            }
        } catch (e) {
            addDebug(`✗ JSON parse error: ${e.message}`);
            alert(`✗ JSON parse error: ${e.message}`);
        }
    })
    .catch(error => {
        addDebug(`✗ Fetch error: ${error.message}`);
        alert(`✗ Fetch error: ${error.message}`);
    });
}

function testComment(postId) {
    const comment = prompt('Enter a test comment:');
    if (!comment) return;
    
    addDebug(`🔄 Testing comment on post ${postId}: "${comment}"`);
    
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('comment', comment);
    
    fetch('api/comment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        addDebug(`Comment response status: ${response.status}`);
        return response.text();
    })
    .then(text => {
        addDebug(`Comment raw response: ${text}`);
        try {
            const data = JSON.parse(text);
            addDebug(`Comment parsed JSON: ${JSON.stringify(data, null, 2)}`);
            
            if (data.success) {
                addDebug(`✓ Comment successful`);
                alert('✓ Comment posted successfully!');
            } else {
                addDebug(`✗ Comment failed: ${data.error}`);
                alert(`✗ Comment failed: ${data.error}`);
            }
        } catch (e) {
            addDebug(`✗ Comment JSON parse error: ${e.message}`);
            alert(`✗ Comment JSON parse error: ${e.message}`);
        }
    })
    .catch(error => {
        addDebug(`✗ Comment fetch error: ${error.message}`);
        alert(`✗ Comment fetch error: ${error.message}`);
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    addDebug('✓ Page loaded, JavaScript initialized');
    addDebug('✓ Debug system ready');
});
</script>

<?php include 'includes/footer.php'; ?>