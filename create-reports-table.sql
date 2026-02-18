-- Create content_reports table for tracking reported content
CREATE TABLE IF NOT EXISTS content_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    content_type ENUM('post', 'comment', 'user', 'message') NOT NULL,
    content_id INT NOT NULL,
    reporter_id INT NOT NULL,
    reason ENUM('inappropriate', 'spam', 'harassment', 'false_info', 'other') NOT NULL,
    description TEXT,
    status ENUM('pending', 'resolved', 'dismissed') DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_content (content_type, content_id),
    INDEX idx_reporter (reporter_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample reports for testing
INSERT INTO content_reports (content_type, content_id, reporter_id, reason, description, status) VALUES
('post', 1, 2, 'inappropriate', 'This post contains content that may not be appropriate for our fellowship community.', 'pending'),
('comment', 1, 3, 'spam', 'This comment appears to be spam and is not relevant to the discussion.', 'pending'),
('post', 2, 4, 'false_info', 'This post contains misleading information about our fellowship activities.', 'pending');
