-- Demo Accounts for Testing
-- All passwords are: demo123
-- NOTE: If passwords don't work, run fix-passwords.php once

USE haramaya_fellowship;

-- 1. Developer Account (Already exists in database.sql)
-- Username: developer
-- Password: admin123

-- 2. Coordinator Account
INSERT INTO users (username, email, password, full_name, phone, role, status, student_id, department, year_joined, current_year, hometown, section_id, join_date) VALUES
('coordinator', 'coordinator@fellowship.com', '$2y$10$YourHashWillBeGeneratedByFixPasswordsScript', 'Abebe Kebede', '+251911234567', 'coordinator', 'approved', 'HU/2020/001', 'Computer Science', 2020, 4, 'Addis Ababa', 1, '2020-09-01');

-- 3. Fellowship Leader Account
INSERT INTO users (username, email, password, full_name, phone, role, status, student_id, department, year_joined, current_year, hometown, section_id, join_date) VALUES
('fellow_leader', 'fellow@fellowship.com', '$2y$10$YourHashWillBeGeneratedByFixPasswordsScript', 'Tigist Haile', '+251922345678', 'fellowship_leader', 'approved', 'HU/2021/002', 'Business Management', 2021, 3, 'Dire Dawa', 2, '2021-09-01');

-- 4. Section Leader Account (Evangelism)
INSERT INTO users (username, email, password, full_name, phone, role, status, student_id, department, year_joined, current_year, hometown, section_id, join_date) VALUES
('section_leader', 'section@fellowship.com', '$2y$10$YourHashWillBeGeneratedByFixPasswordsScript', 'Daniel Tesfaye', '+251933456789', 'section_leader', 'approved', 'HU/2021/003', 'Engineering', 2021, 3, 'Harar', 1, '2021-09-01');

-- Update section leader
UPDATE sections SET leader_id = (SELECT user_id FROM users WHERE username = 'section_leader') WHERE section_code = 'EVAN';

-- 5. Family Leader Account
INSERT INTO users (username, email, password, full_name, phone, role, status, student_id, department, year_joined, current_year, hometown, section_id, join_date) VALUES
('family_leader', 'family@fellowship.com', '$2y$10$YourHashWillBeGeneratedByFixPasswordsScript', 'Sara Alemayehu', '+251944567890', 'family_leader', 'approved', 'HU/2022/004', 'Medicine', 2022, 2, 'Bahir Dar', 3, '2022-09-01');

-- 6. Regular Member Account
INSERT INTO users (username, email, password, full_name, phone, role, status, student_id, department, year_joined, current_year, hometown, section_id, join_date) VALUES
('member', 'member@fellowship.com', '$2y$10$YourHashWillBeGeneratedByFixPasswordsScript', 'Yohannes Bekele', '+251955678901', 'member', 'approved', 'HU/2023/005', 'Agriculture', 2023, 1, 'Gondar', 4, '2023-09-01');

-- Create a family for family leader
INSERT INTO families (family_name, mom_id, dad_id, section_id) VALUES
('Grace Family', (SELECT user_id FROM users WHERE username = 'family_leader'), NULL, 3);

-- Create some sample posts
INSERT INTO posts (user_id, content, post_type, created_at) VALUES
((SELECT user_id FROM users WHERE username = 'coordinator'), 'Welcome to our fellowship! We are excited to have you all here. Let us grow together in faith and love.', 'announcement', NOW()),
((SELECT user_id FROM users WHERE username = 'fellow_leader'), 'Prayer meeting this Friday at 6 PM. Everyone is welcome!', 'event', NOW() - INTERVAL 1 DAY),
((SELECT user_id FROM users WHERE username = 'section_leader'), 'Evangelism section will be conducting outreach this weekend. Join us!', 'event', NOW() - INTERVAL 2 DAY),
((SELECT user_id FROM users WHERE username = 'family_leader'), 'Blessed Sunday everyone! Remember to keep God first in everything you do.', 'general', NOW() - INTERVAL 3 DAY);

-- Create a sample event
INSERT INTO events (title, description, event_date, location, created_by) VALUES
('Weekly Prayer Meeting', 'Join us for our weekly prayer and worship session', DATE_ADD(NOW(), INTERVAL 3 DAY), 'Main Chapel', (SELECT user_id FROM users WHERE username = 'coordinator')),
('Bible Study Session', 'Deep dive into the book of Romans', DATE_ADD(NOW(), INTERVAL 5 DAY), 'Fellowship Hall', (SELECT user_id FROM users WHERE username = 'section_leader'));

-- Create a sample group for coordinator
INSERT INTO groups (group_name, description, created_by, group_type) VALUES
('Fellowship Leaders Group', 'Private group for fellowship main leaders only', (SELECT user_id FROM users WHERE username = 'coordinator'), 'private');

-- Add fellowship leader to the group
INSERT INTO group_members (group_id, user_id, role) VALUES
((SELECT group_id FROM groups WHERE group_name = 'Fellowship Leaders Group'), (SELECT user_id FROM users WHERE username = 'coordinator'), 'admin'),
((SELECT group_id FROM groups WHERE group_name = 'Fellowship Leaders Group'), (SELECT user_id FROM users WHERE username = 'fellow_leader'), 'member');

-- Create sample section report
INSERT INTO section_reports (section_id, submitted_by, report_title, report_content, report_date, status) VALUES
(1, (SELECT user_id FROM users WHERE username = 'section_leader'), 'Monthly Evangelism Report - January', 'This month we reached out to 50 students and 15 accepted Christ. We conducted 3 campus outreach programs.', CURDATE(), 'pending');

-- Create sample financial report
INSERT INTO financial_reports (section_id, report_type, amount, description, transaction_date, submitted_by) VALUES
(1, 'income', 5000.00, 'Monthly offerings from section members', CURDATE(), (SELECT user_id FROM users WHERE username = 'section_leader')),
(1, 'expense', 2000.00, 'Purchased materials for outreach program', CURDATE(), (SELECT user_id FROM users WHERE username = 'section_leader'));

-- Create sample feedback
INSERT INTO feedback (user_id, feedback_type, subject, message, status) VALUES
((SELECT user_id FROM users WHERE username = 'member'), 'suggestion', 'Mobile App Development', 'It would be great if we had a mobile app for easier access to fellowship activities.', 'new');

-- Create some notifications
INSERT INTO notifications (user_id, notification_type, title, message, link) VALUES
((SELECT user_id FROM users WHERE username = 'section_leader'), 'approval', 'Account Approved', 'Your account has been approved as Section Leader', 'dashboard.php'),
((SELECT user_id FROM users WHERE username = 'fellow_leader'), 'event', 'New Event', 'Prayer meeting scheduled for this Friday', 'events.php');
