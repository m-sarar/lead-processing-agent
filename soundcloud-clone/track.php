<?php
require_once 'includes/functions.php';
require_once 'includes/database.php';

$db = new Database();
$auth = new Auth();

// Get track ID from URL
$trackId = $_GET['id'] ?? 0;

// Get track details
$track = $db->getTrackById($trackId);

if (!$track) {
    header("HTTP/1.0 404 Not Found");
    die("Track not found");
}

// Get comments for this track
$stmt = $db->conn->prepare("SELECT c.*, u.username, u.full_name, u.profile_picture FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.track_id = ? AND c.is_approved = TRUE ORDER BY c.created_at DESC");
$stmt->bind_param('i', $trackId);
$stmt->execute();
$result = $stmt->get_result();
$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $auth->isLoggedIn()) {
    $commentContent = sanitizeInput($_POST['comment'] ?? '');

    if (!empty($commentContent)) {
        $userId = $_SESSION['user_id'];
        $stmt = $db->conn->prepare("INSERT INTO comments (track_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $trackId, $userId, $commentContent);

        if ($stmt->execute()) {
            // Refresh comments
            $stmt = $db->conn->prepare("SELECT c.*, u.username, u.full_name, u.profile_picture FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.track_id = ? AND c.is_approved = TRUE ORDER BY c.created_at DESC");
            $stmt->bind_param('i', $trackId);
            $stmt->execute();
            $result = $stmt->get_result();
            $comments = [];
            while ($row = $result->fetch_assoc()) {
                $comments[] = $row;
            }
        }
    }
}

// Get phonetic transcription of title
$phoneticTitle = getPhoneticTranscription($track['title']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($track['title']); ?> - Phonetics Platform</title>
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

        <main class="track-page">
            <div class="track-header">
                <div class="track-artwork">
                    <i class="fas fa-microphone fa-5x"></i>
                </div>
                <div class="track-info">
                    <h1><?php echo htmlspecialchars($track['title']); ?></h1>
                    <?php if (!empty($phoneticTitle)): ?>
                        <p class="phonetic-transcription">Phonetic: <?php echo $phoneticTitle; ?></p>
                    <?php endif; ?>
                    <p class="track-artist">
                        <a href="profile.php?id=<?php echo $track['user_id']; ?>">
                            <?php echo htmlspecialchars($track['full_name'] ?? $track['username']); ?>
                        </a>
                    </p>
                    <div class="track-meta">
                        <span><i class="fas fa-clock"></i> <?php echo date('M j, Y', strtotime($track['created_at'])); ?></span>
                        <span><i class="fas fa-play"></i> <?php echo number_format($track['play_count']); ?> plays</span>
                        <span><i class="fas fa-download"></i> <?php echo number_format($track['download_count']); ?> downloads</span>
                        <span><i class="fas fa-clock"></i> <?php echo formatDuration($track['duration']); ?></span>
                    </div>
                    <div class="track-actions">
                        <button class="btn-primary play-btn" data-track-id="<?php echo $track['id']; ?>">
                            <i class="fas fa-play"></i> Play
                        </button>
                        <a href="<?php echo $track['file_path']; ?>" download class="btn-secondary">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <button class="btn-like" data-track-id="<?php echo $track['id']; ?>">
                            <i class="far fa-heart"></i> Like
                        </button>
                    </div>
                </div>
            </div>

            <div class="track-content">
                <div class="track-description">
                    <h2>Description</h2>
                    <p><?php echo htmlspecialchars($track['description'] ?? 'No description provided.'); ?></p>
                </div>

                <div class="track-details">
                    <h2>Details</h2>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Language</span>
                            <span class="detail-value"><?php echo htmlspecialchars($track['language_name'] ?? 'Not specified'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Category</span>
                            <span class="detail-value"><?php echo htmlspecialchars($track['category_name'] ?? 'Not specified'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">File Size</span>
                            <span class="detail-value"><?php echo formatFileSize($track['file_size']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">File Type</span>
                            <span class="detail-value"><?php echo pathinfo($track['file_name'], PATHINFO_EXTENSION); ?></span>
                        </div>
                    </div>
                </div>

                <div class="track-comments">
                    <h2>Comments (<?php echo count($comments); ?>)</h2>

                    <?php if ($auth->isLoggedIn()): ?>
                        <form class="comment-form" method="POST">
                            <textarea name="comment" placeholder="Add a comment..." required></textarea>
                            <button type="submit" class="btn-primary">Post Comment</button>
                        </form>
                    <?php else: ?>
                        <p>Please <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">login</a> to post a comment.</p>
                    <?php endif; ?>

                    <div class="comments-list">
                        <?php if (empty($comments)): ?>
                            <div class="no-comments">
                                <i class="fas fa-comments fa-3x"></i>
                                <p>No comments yet. Be the first to comment!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment">
                                    <div class="comment-header">
                                        <div class="comment-avatar">
                                            <?php if ($comment['profile_picture']): ?>
                                                <img src="<?php echo htmlspecialchars($comment['profile_picture']); ?>" alt="Profile">
                                            <?php else: ?>
                                                <i class="fas fa-user-circle"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="comment-info">
                                            <strong><?php echo htmlspecialchars($comment['full_name'] ?? $comment['username']); ?></strong>
                                            <span class="comment-time"><?php echo date('M j, Y \a\t g:i A', strtotime($comment['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="comment-content">
                                        <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
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

    <audio id="track-audio" src="<?php echo $track['file_path']; ?>" preload="none"></audio>

    <script src="js/main.js"></script>
    <script>
        // Play button functionality
        document.querySelector('.play-btn').addEventListener('click', function() {
            const audio = document.getElementById('track-audio');
            const btn = this;
            const icon = btn.querySelector('i');

            if (audio.paused) {
                audio.play();
                icon.className = 'fas fa-pause';
                btn.innerHTML = '<i class="fas fa-pause"></i> Pause';
            } else {
                audio.pause();
                icon.className = 'fas fa-play';
                btn.innerHTML = '<i class="fas fa-play"></i> Play';
            }
        });

        // Like button functionality
        document.querySelector('.btn-like').addEventListener('click', function() {
            const btn = this;
            const icon = btn.querySelector('i');
            const trackId = btn.dataset.trackId;

            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                btn.innerHTML = '<i class="fas fa-heart"></i> Liked';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                btn.innerHTML = '<i class="far fa-heart"></i> Like';
            }
        });

        // Update play count when track starts playing
        document.getElementById('track-audio').addEventListener('play', function() {
            const trackId = <?php echo $track['id']; ?>;
            // In a real implementation, this would make an AJAX call to update the play count
            console.log('Track ' + trackId + ' is playing');
        });
    </script>
</body>
</html>