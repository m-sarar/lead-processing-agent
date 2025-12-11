<?php
require_once 'includes/functions.php';
require_once 'includes/database.php';

requireLogin();

$auth = new Auth();
$user = $auth->getCurrentUser();
$db = new Database();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $bio = sanitizeInput($_POST['bio'] ?? '');
    $languagePref = $_POST['language_pref'] ?? 'en';

    $result = $auth->updateProfile($fullName, $bio, $languagePref);

    if ($result['success']) {
        $success = $result['message'];
        // Refresh user data
        $user = $auth->getCurrentUser();
    } else {
        $error = $result['message'];
    }
}

// Get user's tracks
$stmt = $db->conn->prepare("SELECT * FROM tracks WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$result = $stmt->get_result();
$userTracks = [];
while ($row = $result->fetch_assoc()) {
    $userTracks[] = $row;
}

// Get languages for dropdown
$languages = $db->getLanguages();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Phonetics Platform</title>
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

        <main class="profile-page">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <i class="fas fa-user-circle fa-5x"></i>
                    <?php endif; ?>
                </div>
                <h1><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h1>
                <p class="username">@<?php echo htmlspecialchars($user['username']); ?></p>
            </div>

            <div class="profile-content">
                <div class="profile-edit">
                    <h2>Edit Profile</h2>

                    <?php if ($error): ?>
                        <div class="alert error"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form action="profile.php" method="POST" class="profile-form">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="language_pref">Preferred Language</label>
                            <select id="language_pref" name="language_pref">
                                <?php foreach ($languages as $lang): ?>
                                    <option value="<?php echo $lang['code']; ?>" <?php echo ($user['language_pref'] == $lang['code']) ? 'selected' : ''; ?>>
                                        <?php echo $lang['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn-primary">Save Changes</button>
                    </form>
                </div>

                <div class="profile-stats">
                    <h2>Your Statistics</h2>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo count($userTracks); ?></div>
                            <div class="stat-label">Recordings</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format(array_reduce($userTracks, function($carry, $track) {
                                return $carry + $track['play_count'];
                            }, 0)); ?></div>
                            <div class="stat-label">Plays</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format(array_reduce($userTracks, function($carry, $track) {
                                return $carry + $track['download_count'];
                            }, 0)); ?></div>
                            <div class="stat-label">Downloads</div>
                        </div>
                    </div>
                </div>

                <div class="profile-tracks">
                    <h2>Your Recent Recordings</h2>
                    <?php if (empty($userTracks)): ?>
                        <div class="no-tracks">
                            <i class="fas fa-music fa-3x"></i>
                            <p>You haven't uploaded any recordings yet</p>
                            <a href="upload.php" class="btn-primary">Upload Your First Recording</a>
                        </div>
                    <?php else: ?>
                        <div class="tracks-list">
                            <?php foreach ($userTracks as $track): ?>
                                <div class="track-item">
                                    <div class="track-info">
                                        <h4><?php echo htmlspecialchars($track['title']); ?></h4>
                                        <p class="track-meta">
                                            <span><i class="fas fa-clock"></i> <?php echo date('M j, Y', strtotime($track['created_at'])); ?></span>
                                            <span><i class="fas fa-play"></i> <?php echo number_format($track['play_count']); ?></span>
                                        </p>
                                    </div>
                                    <div class="track-actions">
                                        <a href="track.php?id=<?php echo $track['id']; ?>" class="btn-small"><i class="fas fa-play"></i> Play</a>
                                        <a href="edit-track.php?id=<?php echo $track['id']; ?>" class="btn-small"><i class="fas fa-edit"></i> Edit</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="view-all">
                            <a href="dashboard.php">View All Recordings</a>
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