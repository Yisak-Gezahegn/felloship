# Complete Installation Guide

## Step-by-Step Installation for XAMPP

### Step 1: Install XAMPP
1. Download XAMPP from: https://www.apachefriends.org/
2. Install XAMPP (default location: `C:\xampp`)
3. Run XAMPP Control Panel

### Step 2: Start Services
1. Open XAMPP Control Panel
2. Click "Start" for Apache
3. Click "Start" for MySQL
4. Both should show green "Running" status

### Step 3: Copy Project Files
1. Copy the entire `haramaya-fellowship` folder
2. Paste it into: `C:\xampp\htdocs\`
3. Final path should be: `C:\xampp\htdocs\haramaya-fellowship\`

### Step 4: Create Database
1. Open browser and go to: http://localhost/phpmyadmin
2. Click "New" on the left sidebar
3. Database name: `haramaya_fellowship`
4. Collation: `utf8mb4_general_ci`
5. Click "Create"

### Step 5: Import Database Structure
1. Click on `haramaya_fellowship` database (left sidebar)
2. Click "Import" tab at the top
3. Click "Choose File"
4. Select: `C:\xampp\htdocs\haramaya-fellowship\database.sql`
5. Click "Go" at the bottom
6. Wait for success message

### Step 6: Import Demo Accounts (Optional but Recommended)
1. Still in phpMyAdmin, with `haramaya_fellowship` selected
2. Click "Import" tab again
3. Click "Choose File"
4. Select: `C:\xampp\htdocs\haramaya-fellowship\demo-accounts.sql`
5. Click "Go"
6. Wait for success message

### Step 7: Create Upload Folders
1. Open: `C:\xampp\htdocs\haramaya-fellowship\`
2. Create a new folder called: `uploads`
3. Inside `uploads`, create three folders:
   - `profiles`
   - `posts`
   - `files`

Final structure:
```
haramaya-fellowship/
├── uploads/
│   ├── profiles/
│   ├── posts/
│   └── files/
```

### Step 8: Set Folder Permissions (Windows)
1. Right-click on `uploads` folder
2. Properties → Security tab
3. Click "Edit"
4. Select "Users"
5. Check "Full control"
6. Click "Apply" → "OK"

### Step 9: Access the Website
Open your browser and visit:
- **Main Website:** http://localhost/haramaya-fellowship/
- **Login Page:** http://localhost/haramaya-fellowship/login.php

### Step 10: Login as Developer
1. Go to login page
2. Username: `developer`
3. Password: `admin123`
4. Click "Login"
5. You'll be redirected to the developer dashboard

---

## Configure a Hosted MySQL Database

For hosted MySQL, copy `config/database.local.example.php` to
`config/database.local.php` and enter the connection values from your hosting
control panel. This local file is ignored by Git so credentials are not
committed. The application reads its host, username, password, database name,
and port from that file. Environment variables `DB_HOST`, `DB_USER`, `DB_PASS`,
`DB_NAME`, and `DB_PORT` override the file when provided.

The default XAMPP settings remain in effect when neither environment variables
nor a local config file are present. Some shared hosts only allow database
connections from their own web servers, so a hosted database may not accept
connections from a local development computer.

## Quick Test Checklist

✅ Apache is running (green in XAMPP)  
✅ MySQL is running (green in XAMPP)  
✅ Database `haramaya_fellowship` exists  
✅ Tables are created (check in phpMyAdmin)  
✅ Upload folders exist and are writable  
✅ Can access: http://localhost/haramaya-fellowship/  
✅ Can login with developer account  

---

## Common Issues & Solutions

### Issue: "Connection failed" error
**Solution:** 
- Make sure MySQL is running in XAMPP
- Check database name is `haramaya_fellowship`
- Check username is `root` with no password

### Issue: "Page not found" or 404 error
**Solution:**
- Check Apache is running
- Verify path: `C:\xampp\htdocs\haramaya-fellowship\`
- Use correct URL: http://localhost/haramaya-fellowship/

### Issue: Images/logos not showing
**Solution:**
- Clear browser cache (Ctrl + Shift + Delete)
- Check images are in: `assets/images/`
- Refresh page with Ctrl + F5

### Issue: "Cannot write to uploads folder"
**Solution:**
- Check folder permissions (see Step 8)
- Make sure folders exist: uploads/profiles/, uploads/posts/, uploads/files/

### Issue: CSS not loading (page looks plain)
**Solution:**
- Check file exists: `assets/css/style.css`
- Clear browser cache
- Check Apache is serving files correctly

---

## What's Included

### Database Tables (15 tables):
- users (all user accounts)
- sections (10 fellowship sections)
- families (family groups)
- posts (social media posts)
- post_reactions (likes, prays, etc.)
- comments (post comments)
- events (scheduled events)
- groups (user groups)
- group_members (group membership)
- group_messages (group chats)
- private_messages (direct messages)
- section_reports (section activity reports)
- financial_reports (income/expense tracking)
- feedback (user feedback)
- notifications (user notifications)

### User Roles:
1. Developer (full system access)
2. Coordinator (approve leaders, manage all)
3. Fellowship Leaders (create posts, manage system)
4. Section Leaders (manage section, submit reports)
5. Family Leaders (manage family groups)
6. Members (view, react, comment)

### Features:
- User registration with approval workflow
- Role-based dashboards
- Social media-like feed
- Post reactions and comments
- Event scheduling
- Group messaging
- Private messaging
- Section reporting
- Financial tracking
- User search
- Online status tracking
- Notifications system

---

## Next Steps After Installation

1. **Login as developer** to explore admin features
2. **Try other demo accounts** to see different dashboards
3. **Add your fellowship logo** to `assets/images/`
4. **Create upload folders** for user content
5. **Register new users** and approve them
6. **Create posts and events** to test the feed
7. **Customize** colors and branding in `assets/css/style.css`

---

## Support

For issues or questions:
1. Check this guide first
2. Review DEMO-ACCOUNTS.md for login credentials
3. Check browser console (F12) for errors
4. Verify XAMPP services are running

**Enjoy your Haramaya Fellowship Management System!**
