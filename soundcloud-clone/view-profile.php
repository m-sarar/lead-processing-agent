<?php
require_once 'includes/functions.php';
require_once 'includes/database.php';

$db = new Database();
$auth = new Auth();

// Get user ID from URL or session
$viewedUserId = $_GET['id'] ?? null;
$currentUser = $auth->getCurrentUser();
$isOwnProfile = $currentUser && $currentUser['id'] == $viewedUserId;

if (!$viewedUserId) {
    if ($currentUser) {
        $viewedUserId = $currentUser['id'];
    } else {
        redirect('login.php');
    }
}

// Get user profile
$stmt = $db->conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $viewedUserId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found");
}

$user = $result->fetch_assoc();

// Get user's tracks
$stmt = $db->conn->prepare("SELECT * FROM tracks WHERE user_id = ? AND is_public = TRUE ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param('i', $viewedUserId);
$stmt->execute();
$result = $stmt->get_result();
$userTracks = [];
while ($row = $result->fetch_assoc()) {
    $userTracks[] = $row;
}

// Get statistics
$totalTracks = count($userTracks);
$totalPlays = array_reduce($userTracks, function($carry, $track) {
    return $carry + $track['play_count'];
}, 0);

$totalDownloads = array_reduce($userTracks, function($carry, $track) {
    return $carry + $track['download_count'];
}, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?> - Phonetics Platform</title>
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
                    <?php if($auth->isLoggedIn()): ?>
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </header>

        <main class="view-profile-page">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <i class="fas fa-user-circle fa-5x"></i>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h1>
                    <p class="username">@<?php echo htmlspecialchars($user['username']); ?></p>

                    <?php if ($user['bio']): ?>
                        <p class="bio"><?php echo htmlspecialchars($user['bio']); ?></p>
                    <?php endif; ?>

                    <div class="profile-stats">
                        <span><i class="fas fa-music"></i> <?php echo $totalTracks; ?> recordings</span>
                        <span><i class="fas fa-play"></i> <?php echo number_format($totalPlays); ?> plays</span>
                        <span><i class="fas fa-download"></i> <?php echo number_format($totalDownloads); ?> downloads</span>
                    </div>

                    <?php if ($isOwnProfile): ?>
                        <div class="profile-actions">
                            <a href="profile.php" class="btn-primary">Edit Profile</a>
                            <a href="settings.php" class="btn-secondary">Account Settings</a>
                        </div>
                    <?php else: ?>
                        <div class="profile-actions">
                            <button class="btn-primary"><i class="fas fa-user-plus"></i> Follow</button>
                            <button class="btn-secondary"><i class="fas fa-envelope"></i> Message</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-content">
                <div class="profile-tracks">
                    <h2>Recordings</h2>

                    <?php if (empty($userTracks)): ?>
                        <div class="no-tracks">
                            <i class="fas fa-music fa-3x"></i>
                            <p>This user hasn't uploaded any recordings yet</p>
                        </div>
                    <?php else: ?>
                        <div class="tracks-grid">
                            <?php foreach ($userTracks as $track): ?>
                                <div class="track-card">
                                    <div class="track-artwork">
                                        <i class="fas fa-microphone"></i>
                                    </div>
                                    <div class="track-info">
                                        <h3><?php echo htmlspecialchars($track['title']); ?></h3>
                                        <p class="track-description"><?php echo htmlspecialchars(substr($track['description'] ?? '', 0, 80) . (strlen($track['description'] ?? '') > 80 ? '...' : '')); ?></p>
                                        <div class="track-stats">
                                            <span><i class="fas fa-play"></i> <?php echo number_format($track['play_count']); ?></span>
                                            <span><i class="fas fa-download"></i> <?php echo number_format($track['download_count']); ?></span>
                                            <span><i class="fas fa-clock"></i> <?php echo formatDuration($track['duration']); ?></span>
                                        </div>
                                        <a href="track.php?id=<?php echo $track['id']; ?>" class="btn-primary">Listen</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
</body>
</html>