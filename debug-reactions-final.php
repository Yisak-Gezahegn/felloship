<?php
$pageTitle = "Final Reaction Debug - Haramaya Fellowship";
include 'includes/header.php';

requireLogin();

// Get current reaction counts for all posts
$posts_with_reactions = $conn->query("
    SELECT p.post_id, p.content, u.full_name,
           (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id AND reaction_type = 'like') as like_count,
           (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.post_id AND reaction_type = 'pray') as pray_count,
           (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count
    FROM posts p 
    JOIN users u ON p.user_id = u.user_id 
    ORDER BY p.created_at DESC 
    LIMIT 3
");
?>

<main style="padding: 25px 0;">
    <div class="container">
        <h2>🔧 Final Reaction System Debug</h2>
        
        <div class="card" style="margin-bottom: 20px;">
            <h3>📊 Current Database State</h3>
            <?php
            $reaction_count = $conn->query("SELECT COUNT(*) as count FROM post_reactions")->fetch_assoc()['count'];
            $comment_count = $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];
            echo "<p>Total reactions in database: <strong>$reaction_count</strong></p>";
            echo "<p>Total comments in database: <strong>$comment_count</strong></p>";
            ?>
        </div>

        <?php while ($post = $posts_with_reactions->fetch_assoc()): ?>
        <div class="card" style="margin-bottom: 20px;">
            <h3>Post <?php echo $post['post_id']; ?> by <?php echo htmlspecialchars($post['full_name']); ?></h3>
            <p><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . '...'; ?></p>
            
            <div style="margin: 15px 0;">
                <strong>Current counts:</strong> 
                Like: <?php echo $post['like_count']; ?>, 
                Pray: <?php echo $post['pray_count']; ?>, 
                Comments: <?php echo $post['comment_count']; ?>
            </div>
            
            <div class="post-actions" style="margin: 15px 0;">
                <button class="btn btn-primary" onclick="testReactionSimple(<?php echo $post['post_id']; ?>, 'like')" style="margin: 5px;">
                    👍 Like (<?php echo $post['like_count']; ?>)
                </button>
                <button class="btn btn-secondary" onclick="testReactionSimple(<?php echo $post['post_id']; ?>, 'pray')" style="margin: 5px;">
                    🙏 Pray (<?php echo $post['pray_count']; ?>)
                </button>
                <button class="btn btn-info" onclick="testCommentSimple(<?php echo $post['post_id']; ?>)" style="margin: 5px;">
                    💬 Comment (<?php echo $post['comment_count']; ?>)
                </button>
            </div>
        </div>
        <?php endwhile; ?>
        
        <div class="card">
            <h3>🔍 Debug Log</h3>
            <div id="debug-log" style="background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; max-height: 400px; overflow-y: auto; border: 1px solid #ddd;"></div>
            <button onclick="clearLog()" class="btn btn-warning" style="margin-top: 10px;">Clear Log</button>
        </div>
    </div>
</main>

<script>
const debugLog = document.getElementById('debug-log');

function log(message) {
    const timestamp = new Date().toLocaleTimeString();
    debugLog.textContent += `[${timestamp}] ${message}\n`;
    debugLog.scrollTop = debugLog.scrollHeight;
    console.log(`[${timestamp}] ${message}`);
}

function clearLog() {
    debugLog.textContent = '';
}

function testReactionSimple(postId, reactionType) {
    log(`🔄 Starting reaction test: ${reactionType} on post ${postId}`);
    
    // Test 1: Check if fetch is available
    if (typeof fetch === 'undefined') {
        log('❌ ERROR: fetch is not available');
        alert('Fetch API not available');
        return;
    }
    log('✅ Fetch API is available');
    
    // Test 2: Prepare data
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('reaction_type', reactionType);
    log(`📤 Sending data: post_id=${postId}, reaction_type=${reactionType}`);
    
    // Test 3: Make request
    fetch('api/react.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        log(`📥 Response received - Status: ${response.status}`);
        log(`📥 Response OK: ${response.ok}`);
        log(`📥 Response headers: ${JSON.stringify(Object.fromEntries(response.headers))}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.text();
    })
    .then(text => {
        log(`📄 Raw response text (${text.length} chars): ${text.substring(0, 200)}${text.length > 200 ? '...' : ''}`);
        
        // Try to parse JSON
        try {
            const data = JSON.parse(text);
            log(`✅ JSON parsed successfully: ${JSON.stringify(data, null, 2)}`);
            
            if (data.success) {
                log(`🎉 SUCCESS: ${data.action} ${data.reaction_type}`);
                alert(`✅ Success! ${data.action} ${data.reaction_type}`);
                
                // Reload page to see updated counts
                setTimeout(() => {
                    log('🔄 Reloading page to show updated counts...');
                    location.reload();
                }, 1000);
            } else {
                log(`❌ API ERROR: ${data.error}`);
                alert(`❌ Error: ${data.error}`);
            }
        } catch (parseError) {
            log(`❌ JSON PARSE ERROR: ${parseError.message}`);
            log(`📄 Problematic text: "${text}"`);
            alert(`❌ JSON Parse Error: ${parseError.message}`);
        }
    })
    .catch(error => {
        log(`❌ FETCH ERROR: ${error.message}`);
        log(`❌ Error stack: ${error.stack}`);
        alert(`❌ Network Error: ${error.message}`);
    });
}

function testCommentSimple(postId) {
    const comment = prompt('Enter a test comment:');
    if (!comment) {
        log('❌ Comment cancelled by user');
        return;
    }
    
    log(`🔄 Starting comment test on post ${postId}: "${comment}"`);
    
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('comment', comment);
    
    fetch('api/comment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        log(`📥 Comment response - Status: ${response.status}`);
        return response.text();
    })
    .then(text => {
        log(`📄 Comment raw response: ${text}`);
        
        try {
            const data = JSON.parse(text);
            log(`✅ Comment JSON parsed: ${JSON.stringify(data, null, 2)}`);
            
            if (data.success) {
                log(`🎉 COMMENT SUCCESS`);
                alert('✅ Comment posted successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                log(`❌ COMMENT ERROR: ${data.error}`);
                alert(`❌ Comment Error: ${data.error}`);
            }
        } catch (parseError) {
            log(`❌ COMMENT JSON PARSE ERROR: ${parseError.message}`);
            alert(`❌ Comment Parse Error: ${parseError.message}`);
        }
    })
    .catch(error => {
        log(`❌ COMMENT FETCH ERROR: ${error.message}`);
        alert(`❌ Comment Network Error: ${error.message}`);
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    log('✅ Page loaded and JavaScript initialized');
    log('✅ Debug system ready for testing');
    log('ℹ️ Click the reaction buttons above to test the system');
});
</script>

<?php include 'includes/footer.php'; ?>