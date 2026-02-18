# Haramaya University Kale Hiywot Family Fellowship Website

A comprehensive fellowship management system for Haramaya University Christian students.

## Features

- User registration and authentication with role-based access
- Multiple user roles: Developer, Coordinator, Fellowship Leaders, Section Leaders, Family Leaders, Members
- Social media-like home feed with posts, reactions, and comments
- 10 sections: Evangelism, Bible Study, Choirs, Worship, Prayers, Fundraising, Charity, LAD, Media, Counseling
- Group messaging and private messaging
- Event scheduling and management
- Section and financial reporting
- User approval system
- Online user tracking
- Responsive design

## Installation (XAMPP)

1. **Install XAMPP** - Download from https://www.apachefriends.org/

2. **Copy project to htdocs**
   - Copy the entire project folder to `C:\xampp\htdocs\haramaya-fellowship`

3. **Start XAMPP**
   - Start Apache and MySQL from XAMPP Control Panel

4. **Create Database**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Import the `database.sql` file

5. **Create Upload Folders**
   - Create folders: `uploads/profiles/`, `uploads/posts/`, `uploads/files/`
   - Set permissions to 777 (right-click > Properties > Security)

6. **Access Website**
   - Visit: http://localhost/haramaya-fellowship

7. **Default Login**
   - Username: `developer`
   - Password: `admin123`

## User Roles & Permissions

- **Developer**: Full system access, database management
- **Coordinator**: Approve leaders, manage all users, view all reports
- **Fellowship Leaders**: Create posts/events, manage system, financial reports
- **Section Leaders**: Manage section members, submit reports
- **Family Leaders**: Create family groups, post content
- **Members**: View content, react, comment

## Technology Stack

- PHP 7.4+
- MySQL 5.7+
- HTML5, CSS3, JavaScript
- Font Awesome icons

## Support

For issues or questions, contact the fellowship administrator.
