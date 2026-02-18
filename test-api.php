<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

echo "<h2>API Test Page</h2>";
echo "<p>Current User: " . $_SESSION['full_name'] . " (ID: " . $_SESSION['user_id'] . ")</p>";

// Get a test post
$post_result = $conn->query("SELECT post_id, content FROM posts LIMIT 1");
if ($post_result && $post_result->num_rows > 0) {
    $test_post = $post_result->fetch_assoc();
    $post_id = $test_post['post_id'];
    
    echo "<h3>Test Post: " . substr($test_post['content'], 0, 50) . "...</h3>";
    echo "<p>Post ID: $post_id</p>";
    
    // Test buttons
    echo "<div style='margin: 20px 0;'>";
    echo "<button onclick='testReaction($post_id, \"like\")' style='margin: 5px; padding: 10px;'>Test Like</button>";
    echo "<button onclick='testReaction($post_id, \"pray\")' style='margin: 5px; padding: 10px;'>Test Pray</button>";
    echo "<button onclick='testComment($post_id)' style='margin: 5px; padding: 10px;'>Test Comment</button>";
    echo "</div>";
    
    echo "<div id='results' style='background: #f0f0f0; padding: 15px; margin: 20px 0; min-height: 100px;'>";
    echo "<h4>Results will appear here:</h4>";
    echo "</div>";
    
} else {
    echo "<p>No posts found for testing. Please create a post first.</p>";
}
?>

<script>
function testReaction(postId, reactionType) {
    console.log('Testing reaction:', postId, reactionType);
    document.getElementById('results').innerHTML += `<p>Testing ${reactionType} reaction for post ${postId}...</p>`;
    
    fetch('api/react.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `post_id=${postId}&reaction_type=${reactionType}`
    })
    .then(response => {
        console.log('Response status:', response.status);
        document.getElementById('results').innerHTML += `<p>Response status: ${response.status}</p>`;
        return response.text(); // Get as text first to see what we're getting
    })
    .then(text => {
        console.log('Raw response:', text);
        document.getElementById('results').innerHTML += `<p>Raw response: <code>${text}</code></p>`;
        
        try {
            const data = JSON.parse(text);
            console.log('Parsed data:', data);
            document.getElementById('results').innerHTML += `<p>Parsed JSON: <code>${JSON.stringify(data, null, 2)}</code></p>`;
        } catch (e) {
            console.error('JSON parse error:', e);
            document.getElementById('results').innerHTML += `<p style="color: red;">JSON parse error: ${e.message}</p>`;
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        document.getElementById('results').innerHTML += `<p style="color: red;">Fetch error: ${error.message}</p>`;
    });
}

function testComment(postId) {
    const comment = 'Test comment from API test page';
    console.log('Testing comment:', postId, comment);
    document.getElementById('results').innerHTML += `<p>Testing comment for post ${postId}...</p>`;
    
    fetch('api/comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `post_id=${postId}&comment=${encodeURIComponent(comment)}`
    })
    .then(response => {
        console.log('Comment response status:', response.status);
        document.getElementById('results').innerHTML += `<p>Comment response status: ${response.status}</p>`;
        return response.text();
    })
    .then(text => {
        console.log('Comment raw response:', text);
        document.getElementById('results').innerHTML += `<p>Comment raw response: <code>${text}</code></p>`;
        
        try {
            const data = JSON.parse(text);
            console.log('Comment parsed data:', data);
            document.getElementById('results').innerHTML += `<p>Comment parsed JSON: <code>${JSON.stringify(data, null, 2)}</code></p>`;
        } catch (e) {
            console.error('Comment JSON parse error:', e);
            document.getElementById('results').innerHTML += `<p style="color: red;">Comment JSON parse error: ${e.message}</p>`;
        }
    })
    .catch(error => {
        console.error('Comment fetch error:', error);
        document.getElementById('results').innerHTML += `<p style="color: red;">Comment fetch error: ${error.message}</p>`;
    });
}
</script>

<p><a href="home.php">← Back to Home</a></p>