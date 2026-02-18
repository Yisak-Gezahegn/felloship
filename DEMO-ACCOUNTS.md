# Demo Accounts - Haramaya Fellowship System

## How to Setup Demo Accounts

1. **Import the demo accounts SQL file:**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Select the `haramaya_fellowship` database
   - Click on "Import" tab
   - Choose the file `demo-accounts.sql`
   - Click "Go" to import

2. **Access the website:**
   - Visit: http://localhost/haramaya-fellowship/index.php
   - Click "Login" button

## Demo Account Credentials

All passwords are: **demo123** (except developer which is **admin123**)

### 1. DEVELOPER ACCOUNT
**Username:** `developer`  
**Password:** `admin123`  
**Access Level:** Full system control

**What you can do:**
- View all system statistics
- Manage all users (approve, suspend, delete)
- Manage all sections
- View all reports (section reports, financial reports)
- View all feedback from users
- Database backup and system settings
- Access to everything in the system

**Dashboard:** http://localhost/haramaya-fellowship/admin/dashboard.php

---

### 2. COORDINATOR ACCOUNT
**Username:** `coordinator`  
**Password:** `demo123`  
**Full Name:** Abebe Kebede

**What you can do:**
- Approve fellowship leaders and section leaders
- View and manage all members
- Access all section reports
- Create posts and events
- Manage fellowship leaders group
- Search members by section
- View financial reports

**Dashboard:** http://localhost/haramaya-fellowship/coordinator/dashboard.php

---

### 3. FELLOWSHIP LEADER ACCOUNT
**Username:** `fellow_leader`  
**Password:** `demo123`  
**Full Name:** Tigist Haile

**What you can do:**
- Create and manage posts/events
- Create and join groups
- Send private messages
- View financial reports
- Manage users (limited)
- Access system administration features

**Dashboard:** http://localhost/haramaya-fellowship/fellowship-leaders/dashboard.php

---

### 4. SECTION LEADER ACCOUNT
**Username:** `section_leader`  
**Password:** `demo123`  
**Full Name:** Daniel Tesfaye  
**Section:** Evangelism

**What you can do:**
- Manage section members
- Submit section reports
- Submit financial reports
- Create posts and events for section
- View section statistics
- Create section groups

**Dashboard:** http://localhost/haramaya-fellowship/section-leaders/dashboard.php

---

### 5. FAMILY LEADER ACCOUNT
**Username:** `family_leader`  
**Password:** `demo123`  
**Full Name:** Sara Alemayehu  
**Section:** Choirs

**What you can do:**
- Create family groups
- Manage family members
- Create posts and events
- Send messages to family members
- View family activities

**Dashboard:** http://localhost/haramaya-fellowship/family-leaders/dashboard.php

---

### 6. REGULAR MEMBER ACCOUNT
**Username:** `member`  
**Password:** `demo123`  
**Full Name:** Yohannes Bekele  
**Section:** Worship

**What you can do:**
- View posts and events
- React to posts (like, pray, amen)
- Comment on posts
- Join groups
- Send private messages
- View profile
- Edit own profile

**Home Page:** http://localhost/haramaya-fellowship/home.php

---

## Quick Login Steps

1. Go to: http://localhost/haramaya-fellowship/login.php
2. Enter username (e.g., `developer`)
3. Enter password (e.g., `admin123`)
4. Click "Login"
5. You'll be redirected to the appropriate dashboard

## Testing Different Features

### As Developer:
- Login and go to admin dashboard
- Click "Manage Users" to see all users
- Click "Feedback" to see user suggestions
- Check "Database Backup" for system info

### As Coordinator:
- Login and see pending leader approvals
- View section reports
- Search for members by section
- Access the Fellowship Leaders Group

### As Section Leader:
- Login and submit a section report
- Add financial transactions
- View your section members
- Create section-specific posts

### As Member:
- Login and view the home feed
- React to posts
- Comment on posts
- See online users
- View upcoming events

## Troubleshooting

**If login doesn't work:**
1. Make sure you imported `database.sql` first
2. Then import `demo-accounts.sql`
3. Clear browser cache (Ctrl + F5)
4. Check that XAMPP Apache and MySQL are running

**If you see "pending approval":**
- The account status is not "approved"
- Login as developer or coordinator to approve the account

**If images don't show:**
- Create folders: `uploads/profiles/`, `uploads/posts/`, `uploads/files/`
- Set folder permissions to 777 (writable)

## Need More Accounts?

You can register new accounts at:
http://localhost/haramaya-fellowship/register.php

Then login as coordinator or developer to approve them.

---

**Enjoy testing the Haramaya Fellowship System!**
