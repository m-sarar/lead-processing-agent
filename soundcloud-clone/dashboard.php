<?php
require_once 'includes/functions.php';
require_once 'includes/database.php';

requireLogin();

$auth = new Auth();
$user = $auth->getCurrentUser();
$db = new Database();

// Get user's tracks
$stmt = $db->conn->prepare("SELECT * FROM tracks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $user['id']);
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
    <title>Dashboard - Phonetics Platform</title>
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
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </nav>
        </header>

        <main class="dashboard-page">
            <div class="dashboard-header">
                <h1>Your Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-music"></i></div>
                    <div class="stat-value"><?php echo $totalTracks; ?></div>
                    <div class="stat-label">Total Recordings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-play"></i></div>
                    <div class="stat-value"><?php echo number_format($totalPlays); ?></div>
                    <div class="stat-label">Total Plays</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-download"></i></div>
                    <div class="stat-value"><?php echo number_format($totalDownloads); ?></div>
                    <div class="stat-label">Total Downloads</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-heart"></i></div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Total Likes</div>
                </div>
            </div>

            <div class="dashboard-sections">
                <section class="recent-activity">
                    <h2>Recent Activity</h2>
                    <div class="activity-list">
                        <?php if (empty($userTracks)): ?>
                            <div class="no-activity">
                                <i class="fas fa-inbox fa-3x"></i>
                                <p>No recent activity</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($userTracks as $track): ?>
                                <div class="activity-item">
                                    <div class="activity-icon"><i class="fas fa-microphone"></i></div>
                                    <div class="activity-content">
                                        <h4><?php echo htmlspecialchars($track['title']); ?></h4>
                                        <p class="activity-meta">
                                            <span><i class="fas fa-clock"></i> <?php echo date('M j, Y', strtotime($track['created_at'])); ?></span>
                                            <span><i class="fas fa-play"></i> <?php echo number_format($track['play_count']); ?></span>
                                        </p>
                                    </div>
                                    <div class="activity-actions">
                                        <a href="track.php?id=<?php echo $track['id']; ?>" class="btn-small"><i class="fas fa-play"></i> Play</a>
                                        <a href="edit-track.php?id=<?php echo $track['id']; ?>" class="btn-small"><i class="fas fa-edit"></i> Edit</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="quick-actions">
                    <h2>Quick Actions</h2>
                    <div class="action-buttons">
                        <a href="upload.php" class="action-btn">
                            <i class="fas fa-upload"></i>
                            <span>Upload New Recording</span>
                        </a>
                        <a href="browse.php" class="action-btn">
                            <i class="fas fa-search"></i>
                            <span>Browse Recordings</span>
                        </a>
                        <a href="profile.php" class="action-btn">
                            <i class="fas fa-user"></i>
                            <span>Edit Profile</span>
                        </a>
                        <a href="settings.php" class="action-btn">
                            <i class="fas fa-cog"></i>
                            <span>Account Settings</span>
                        </a>
                    </div>
                </section>
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