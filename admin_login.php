<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

init_session();

// Check if already logged in
if (is_logged_in() && check_role('admin')) {
    redirect('admin_dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $query = "SELECT id, username, password, full_name, role FROM users 
                  WHERE username = '$username' AND role = 'admin'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                redirect('admin_dashboard.php', 'Welcome back, ' . $user['full_name']);
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Invalid username';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Login - Aparna Public School</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <h1>APARNA PUBLIC SCHOOL</h1>
            <p>Administrator Login</p>
        </header>

        <main>
            <div class="form-container">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit">Login</button>
                </form>

                <p style="margin-top: 20px; text-align: center;">
                    <a href="index.php" style="color: #1e3c72; text-decoration: none;">← Back to Home</a>
                </p>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> Aparna Public School. All rights reserved.</p>
        </footer>
    </div>
</body>
</html> 