<?php
// SoundCloud-like platform - Main entry point
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phonetics Platform - SoundCloud-like</title>
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
                    <a href="upload.php">Upload</a>
                    <a href="dashboard.php">Dashboard</a>
                </div>
                <div class="auth-buttons">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </header>

        <main>
            <section class="hero">
                <h1>Professional Phonetics Platform</h1>
                <p>Upload, share, and discover phonetic recordings with a comprehensive database</p>
                <div class="hero-buttons">
                    <a href="browse.php" class="btn-primary">Browse Recordings</a>
                    <a href="upload.php" class="btn-secondary">Upload Now</a>
                </div>
            </section>

            <section class="features">
                <div class="feature">
                    <i class="fas fa-microphone fa-3x"></i>
                    <h3>High Quality Audio</h3>
                    <p>Support for multiple audio formats with professional quality</p>
                </div>
                <div class="feature">
                    <i class="fas fa-globe fa-3x"></i>
                    <h3>Multi-language Support</h3>
                    <p>Comprehensive support for phonetics in multiple languages</p>
                </div>
                <div class="feature">
                    <i class="fas fa-tachometer-alt fa-3x"></i>
                    <h3>Advanced Dashboard</h3>
                    <p>Complete control over your recordings and analytics</p>
                </div>
                <div class="feature">
                    <i class="fas fa-database fa-3x"></i>
                    <h3>Comprehensive Database</h3>
                    <p>Extensive phonetic database with search capabilities</p>
                </div>
            </section>

            <section class="recent-uploads">
                <h2>Recent Uploads</h2>
                <div class="tracks">
                    <!-- Tracks will be loaded via JavaScript -->
                    <div class="track-placeholder">
                        <div class="track-artwork"></div>
                        <div class="track-info">
                            <h4>Loading...</h4>
                            <p>Recent phonetic recordings</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Phonetics Platform</h4>
                    <p>Professional sound platform for phonetic research and sharing</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="browse.php">Browse</a></li>
                        <li><a href="upload.php">Upload</a></li>
                        <li><a href="dashboard.php">Dashboard</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Connect</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Phonetics Platform. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <script src="js/main.js"></script>
</body>
</html>