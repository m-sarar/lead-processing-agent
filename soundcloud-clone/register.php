<?php
require_once 'includes/functions.php';

$auth = new Auth();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = sanitizeInput($_POST['full_name'] ?? '');

    $result = $auth->register($username, $email, $password, $fullName);

    if ($result['success']) {
        $success = $result['message'];
        redirect('index.php');
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Phonetics Platform</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo">🎵 Phonetics Platform</div>
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="browse.php">Browse</a>
                </div>
                <div class="auth-buttons">
                    <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                </div>
            </nav>
        </header>

        <main class="auth-page">
            <div class="auth-card">
                <h1>Create Account</h1>

                <?php if ($error): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form action="register.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required minlength="8">
                        <small>Minimum 8 characters</small>
                    </div>

                    <button type="submit" class="btn-primary">Register</button>

                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </form>
            </div>
        </main>

        <footer>
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Phonetics Platform</h4>
                    <p>Professional sound platform for phonetic research and sharing</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Phonetics Platform. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>