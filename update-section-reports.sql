-- Update section_reports table to support approval/rejection workflow
-- Add missing columns and update status enum

-- Add reviewed_by column
ALTER TABLE section_reports 
ADD COLUMN reviewed_by INT NULL,
ADD COLUMN reviewed_at TIMESTAMP NULL,
ADD COLUMN review_comment TEXT NULL;

-- Add foreign key for reviewed_by
ALTER TABLE section_reports 
ADD FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL;

-- Update status enum to include 'rejected'
ALTER TABLE section_reports 
MODIFY COLUMN status ENUM('pending', 'reviewed', 'approved', 'rejected') DEFAULT 'pending';

-- Add some additional sample data for testing
INSERT INTO section_reports (section_id, submitted_by, report_title, report_content, report_date, status) VALUES
(2, (SELECT user_id FROM users WHERE username = 'family_leader'), 'Family Fellowship Report - February', 'Our family group had 12 members attend this month. We focused on prayer and Bible study. Two new families joined our group.', CURDATE() - INTERVAL 5 DAY, 'pending'),
(3, (SELECT user_id FROM users WHERE username = 'member'), 'Youth Outreach Report', 'Organized youth outreach program at the main campus. 25 students participated and 8 showed interest in joining fellowship.', CURDATE() - INTERVAL 10 DAY, 'approved');