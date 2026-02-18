// Main JavaScript for Fellowship Website

// Post reaction handler
function reactToPost(postId, reactionType) {
    fetch('/api/react.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            post_id: postId,
            reaction_type: reactionType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Comment on post
function commentOnPost(postId) {
    const commentText = document.getElementById(`comment-${postId}`).value;
    
    if (!commentText.trim()) {
        alert('Please write a comment');
        return;
    }
    
    fetch('/api/comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            post_id: postId,
            comment_text: commentText
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Image preview before upload
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('image-preview');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Search users
function searchUsers(query) {
    if (query.length < 2) {
        document.getElementById('search-results').innerHTML = '';
        return;
    }
    
    fetch(`/api/search-users.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data);
        })
        .catch(error => console.error('Error:', error));
}

function displaySearchResults(users) {
    const resultsDiv = document.getElementById('search-results');
    if (users.length === 0) {
        resultsDiv.innerHTML = '<p>No users found</p>';
        return;
    }
    
    let html = '<ul class="user-list">';
    users.forEach(user => {
        html += `
            <li>
                <img src="/uploads/profiles/${user.profile_picture}" alt="${user.full_name}" onerror="this.src='https://via.placeholder.com/40x40?text=User'">
                <div>
                    <strong>${user.full_name}</strong>
                    <span>${user.section_name || 'No section'}</span>
                </div>
                <button onclick="viewUserInfo(${user.user_id})"><i class="fas fa-info-circle"></i></button>
            </li>
        `;
    });
    html += '</ul>';
    resultsDiv.innerHTML = html;
}

// View user info in modal
function viewUserInfo(userId) {
    fetch(`/api/user-info.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            showUserModal(data);
        })
        .catch(error => console.error('Error:', error));
}

function showUserModal(user) {
    const modal = document.getElementById('user-modal');
    const modalContent = document.getElementById('modal-user-content');
    
    modalContent.innerHTML = `
        <div class="user-info-modal">
            <img src="/uploads/profiles/${user.profile_picture}" alt="${user.full_name}" onerror="this.src='https://via.placeholder.com/150x150?text=User'">
            <h2>${user.full_name}</h2>
            <p><strong>Email:</strong> ${user.email}</p>
            <p><strong>Phone:</strong> ${user.phone || 'N/A'}</p>
            <p><strong>Student ID:</strong> ${user.student_id || 'N/A'}</p>
            <p><strong>Department:</strong> ${user.department || 'N/A'}</p>
            <p><strong>Year Joined University:</strong> ${user.year_joined || 'N/A'}</p>
            <p><strong>Current Year:</strong> ${user.current_year || 'N/A'}</p>
            <p><strong>Hometown:</strong> ${user.hometown || 'N/A'}</p>
            <p><strong>Section:</strong> ${user.section_name || 'N/A'}</p>
            <p><strong>Role:</strong> ${user.role.replace('_', ' ').toUpperCase()}</p>
            <p><strong>Joined Fellowship:</strong> ${user.join_date || 'N/A'}</p>
        </div>
    `;
    
    modal.style.display = 'block';
}

// Close modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Auto-refresh online users
setInterval(() => {
    const onlineUsersDiv = document.getElementById('online-users');
    if (onlineUsersDiv) {
        fetch('/api/online-users.php')
            .then(response => response.json())
            .then(data => {
                updateOnlineUsers(data);
            })
            .catch(error => console.error('Error:', error));
    }
}, 30000); // Refresh every 30 seconds

function updateOnlineUsers(users) {
    const onlineUsersDiv = document.getElementById('online-users');
    let html = '<h3>Online Now</h3><ul class="online-list">';
    
    users.forEach(user => {
        html += `
            <li>
                <img src="/uploads/profiles/${user.profile_picture}" alt="${user.full_name}" onerror="this.src='https://via.placeholder.com/30x30?text=User'">
                <span>${user.full_name}</span>
                <span class="online-indicator"></span>
            </li>
        `;
    });
    
    html += '</ul>';
    onlineUsersDiv.innerHTML = html;
}

// Notification handler
function markNotificationRead(notificationId) {
    fetch('/api/mark-notification-read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'red';
            isValid = false;
        } else {
            input.style.borderColor = '#ddd';
        }
    });
    
    return isValid;
}

// Click outside modal to close
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
};
