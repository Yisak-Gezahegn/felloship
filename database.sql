-- Haramaya University Kale Hiywot Family Fellowship Database
-- Created for XAMPP/MySQL

CREATE DATABASE IF NOT EXISTS haramaya_fellowship;
USE haramaya_fellowship;

-- Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT 'default.jpg',
    phone VARCHAR(20),
    role ENUM('developer', 'coordinator', 'fellowship_leader', 'section_leader', 'family_leader', 'member') DEFAULT 'member',
    status ENUM('pending', 'approved', 'suspended') DEFAULT 'pending',
    
    -- University Information
    student_id VARCHAR(50),
    department VARCHAR(100),
    year_joined INT,
    current_year INT,
    hometown VARCHAR(100),
    
    -- Fellowship Information
    section_id INT,
    family_id INT,
    join_date DATE,
    is_online BOOLEAN DEFAULT FALSE,
    last_seen DATETIME,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_role (role),
    INDEX idx_section (section_id),
    INDEX idx_status (status)
);

-- Sections Table
CREATE TABLE sections (
    section_id INT PRIMARY KEY AUTO_INCREMENT,
    section_name VARCHAR(100) NOT NULL,
    section_code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    section_logo VARCHAR(255),
    leader_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (leader_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Insert Default Sections
INSERT INTO sections (section_name, section_code, description) VALUES
('Evangelism Section', 'EVAN', 'Spreading the gospel and outreach programs'),
('Bible Study Section', 'BS', 'Deep study of the Word of God'),
('Choirs Section', 'CHOIR', 'Worship through music and singing'),
('Worship Section', 'WORSHIP', 'Leading worship and praise'),
('Prayers Section', 'PRAYER', 'Intercession and prayer ministry'),
('Fundraising Section', 'FUND', 'Financial support and fundraising activities'),
('Charity Section', 'CHARITY', 'Helping the needy and community service'),
('LAD Section', 'LAD', 'Leadership and discipleship training'),
('Media Section', 'MEDIA', 'Media production and documentation'),
('Counseling Section', 'COUNSEL', 'Spiritual and personal counseling');

-- Families Table
CREATE TABLE families (
    family_id INT PRIMARY KEY AUTO_INCREMENT,
    family_name VARCHAR(100) NOT NULL,
    mom_id INT,
    dad_id INT,
    section_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (mom_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (dad_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE
);

-- Posts Table
CREATE TABLE posts (
    post_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    post_type ENUM('general', 'event', 'announcement') DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_created (created_at DESC)
);

-- Post Reactions Table
CREATE TABLE post_reactions (
    reaction_id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    reaction_type ENUM('like', 'love', 'pray', 'amen') DEFAULT 'like',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_reaction (post_id, user_id)
);

-- Comments Table
CREATE TABLE comments (
    comment_id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Events Table
CREATE TABLE events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATETIME NOT NULL,
    location VARCHAR(200),
    image VARCHAR(255),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_event_date (event_date)
);

-- Groups Table
CREATE TABLE groups (
    group_id INT PRIMARY KEY AUTO_INCREMENT,
    group_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    group_type ENUM('public', 'private', 'family') DEFAULT 'public',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Group Members Table
CREATE TABLE group_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_membership (group_id, user_id)
);

-- Group Messages Table
CREATE TABLE group_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    message_text TEXT NOT NULL,
    file_attachment VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_group_created (group_id, created_at DESC)
);

-- Private Messages Table
CREATE TABLE private_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message_text TEXT NOT NULL,
    file_attachment VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_conversation (sender_id, receiver_id, created_at)
);

-- Section Reports Table
CREATE TABLE section_reports (
    report_id INT PRIMARY KEY AUTO_INCREMENT,
    section_id INT NOT NULL,
    submitted_by INT NOT NULL,
    report_title VARCHAR(200) NOT NULL,
    report_content TEXT NOT NULL,
    report_date DATE NOT NULL,
    file_attachment VARCHAR(255),
    status ENUM('pending', 'reviewed', 'approved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Financial Reports Table
CREATE TABLE financial_reports (
    finance_id INT PRIMARY KEY AUTO_INCREMENT,
    section_id INT,
    report_type ENUM('income', 'expense', 'donation') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL,
    transaction_date DATE NOT NULL,
    submitted_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (section_id) REFERENCES sections(section_id) ON DELETE SET NULL,
    FOREIGN KEY (submitted_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- System Feedback Table
CREATE TABLE feedback (
    feedback_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    feedback_type ENUM('bug', 'suggestion', 'complaint', 'praise') NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'in_progress', 'resolved') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Notifications Table
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read, created_at DESC)
);

-- Create default developer account (password: admin123)
-- NOTE: If password doesn't work, run fix-passwords.php once
INSERT INTO users (username, email, password, full_name, role, status) VALUES
('developer', 'developer@fellowship.com', '$2y$10$PlaceholderHashRunFixPasswordsScript', 'System Developer', 'developer', 'approved');
