<?php
require_once 'includes/functions.php';
require_once 'includes/database.php';

$db = new Database();
$error = '';
$success = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if user exists
        $stmt = $db->conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Generate reset token (in production, use proper token with expiration)
            $resetToken = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store reset token in database
            $stmt = $db->conn->prepare("INSERT INTO password_resets (user_id, token, expires) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $user['id'], $resetToken, $expires);

            if ($stmt->execute()) {
                // In production, send email with reset link
                $resetLink = SITE_URL . "/reset-password.php?token=$resetToken";
                $message = "Password reset link has been sent to your email (simulated).<br><br>";
                $message .= "Reset Link: <a href='$resetLink'>$resetLink</a><br><br>";
                $message .= "<strong>Note:</strong> In a real implementation, this would be sent via email.";

                $success = true;
            } else {
                $error = 'Failed to create password reset: ' . $stmt->error;
            }
        } else {
            // Don't reveal that email doesn't exist for security
            $success = true;
            $message = "If this email exists in our system, you will receive a password reset link.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Phonetics Platform</title>
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
                    <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                </div>
            </nav>
        </header>

        <main class="auth-page">
            <div class="auth-card">
                <h1><i class="fas fa-key"></i> Forgot Password</h1>
                <p>Enter your email address and we'll send you a link to reset your password.</p>

                <?php if ($error): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert success"><?php echo $message; ?></div>
                <?php endif; ?>

                <form action="forgot-password.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <button type="submit" class="btn-primary">Send Reset Link</button>

                    <div class="auth-footer">
                        <p>Remember your password? <a href="login.php">Login here</a></p>
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