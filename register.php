<?php
$pageTitle = "Register - Haramaya Fellowship";
include 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = clean($_POST['full_name']);
    $phone = clean($_POST['phone']);
    $student_id = clean($_POST['student_id']);
    $department = clean($_POST['department']);
    $year_joined = clean($_POST['year_joined']);
    $current_year = clean($_POST['current_year']);
    $hometown = clean($_POST['hometown']);
    $section_id = clean($_POST['section_id']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = "Please fill all required fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // Check if username or email exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username or email already exists";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, student_id, department, year_joined, current_year, hometown, section_id, join_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssssssiisi", $username, $email, $hashed_password, $full_name, $phone, $student_id, $department, $year_joined, $current_year, $hometown, $section_id);
            
            if ($stmt->execute()) {
                $success = "Registration successful! Please wait for approval.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}

// Get sections
$sections_result = $conn->query("SELECT * FROM sections ORDER BY section_name");
?>

<main style="padding: 40px 0; min-height: 80vh;">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto;">
            <div class="card">
                <h2 style="text-align: center; margin-bottom: 30px;">Join Our Fellowship</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <p style="text-align: center;"><a href="login.php">Click here to login</a></p>
                <?php else: ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Password * (min 6 characters)</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <h3 style="margin: 30px 0 20px;">University Information</h3>
                    
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" name="student_id" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" name="department" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Year Joined University</label>
                        <input type="number" name="year_joined" class="form-control" min="2000" max="2030">
                    </div>
                    
                    <div class="form-group">
                        <label>Current Year</label>
                        <select name="current_year" class="form-control">
                            <option value="">Select Year</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                            <option value="5">5th Year</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Hometown</label>
                        <input type="text" name="hometown" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Preferred Section</label>
                        <select name="section_id" class="form-control">
                            <option value="">Select Section</option>
                            <?php while ($section = $sections_result->fetch_assoc()): ?>
                                <option value="<?php echo $section['section_id']; ?>">
                                    <?php echo $section['section_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1.1rem;">Register</button>
                </form>
                
                <p style="text-align: center; margin-top: 20px;">
                    Already have an account? <a href="login.php">Login here</a>
                </p>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
