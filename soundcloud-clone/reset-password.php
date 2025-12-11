<?php
require_once 'includes/functions.php';
require_once 'includes/database.php';

$db = new Database();
$error = '';
$success = '';

$token = $_GET['token'] ?? '';

// Verify token exists
if (empty($token)) {
    $error = 'Invalid or missing reset token';
} else {
    // Check if token is valid
    $stmt = $db->conn->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error = 'Invalid or expired reset token';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Password must be at least 8 characters';
    } else {
        // Get user ID from token
        $stmt = $db->conn->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires > NOW()");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $reset = $result->fetch_assoc();

        if ($reset) {
            $userId = $reset['user_id'];

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update password
            $stmt = $db->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $hashedPassword, $userId);

            if ($stmt->execute()) {
                // Delete used token
                $stmt = $db->conn->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->bind_param('s', $token);
                $stmt->execute();

                $success = 'Password has been reset successfully! You can now <a href="login.php">login</a> with your new password.';
            } else {
                $error = 'Failed to update password: ' . $stmt->error;
            }
        } else {
            $error = 'Invalid or expired reset token';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Phonetics Platform</title>
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
                <h1><i class="fas fa-lock"></i> Reset Password</h1>
                <p>Please enter your new password.</p>

                <?php if ($error): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert success"><?php echo $success; ?></div>
                <?php else: ?>
                    <form action="reset-password.php?token=<?php echo $token; ?>" method="POST" class="auth-form">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required minlength="8">
                            <small>Minimum 8 characters</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                        </div>

                        <button type="submit" class="btn-primary">Reset Password</button>

                        <div class="auth-footer">
                            <p>Remember your password? <a href="login.php">Login here</a></p>
                        </div>
                    </form>
                <?php endif; ?>
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