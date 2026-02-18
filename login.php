<?php
$pageTitle = "Login - Haramaya Fellowship";

// Start output buffering to prevent header errors
ob_start();

include 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please enter username and password";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['status'] = $user['status'];
                $_SESSION['profile_picture'] = $user['profile_picture'] ?? 'default.jpg';
                $_SESSION['section_id'] = $user['section_id'];
                
                // Update online status
                updateOnlineStatus($conn, $user['user_id']);
                
                // Clear output buffer and redirect
                ob_end_clean();
                
                // Redirect based on role and status
                if ($user['status'] !== 'approved') {
                    header('Location: pending.php');
                    exit();
                }
                
                switch ($user['role']) {
                    case 'developer':
                        header('Location: admin/dashboard.php');
                        break;
                    case 'coordinator':
                        header('Location: coordinator/dashboard.php');
                        break;
                    case 'fellowship_leader':
                        header('Location: fellowship-leaders/dashboard.php');
                        break;
                    case 'section_leader':
                        header('Location: section-leaders/dashboard.php');
                        break;
                    case 'family_leader':
                        header('Location: family-leaders/dashboard.php');
                        break;
                    default:
                        header('Location: home.php');
                        break;
                }
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
    }
}
?>

<main style="padding: 60px 0; min-height: 80vh;">
    <div class="container">
        <div style="max-width: 450px; margin: 0 auto;">
            <div class="card">
                <h2 style="text-align: center; margin-bottom: 30px;">Login to Fellowship</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Username or Email</label>
                        <input type="text" name="username" class="form-control" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1.1rem;">Login</button>
                </form>
                
                <p style="text-align: center; margin-top: 20px;">
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
            </div>
        </div>
    </div>
</main>

<?php 
include 'includes/footer.php';
ob_end_flush();
?>
