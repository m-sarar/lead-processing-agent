<?php
require_once 'includes/functions.php';
require_once 'includes/database.php';

$db = new Database();
$auth = new Auth();

// Get search parameters
$searchQuery = $_GET['q'] ?? '';
$languageId = $_GET['language'] ?? null;
$categoryId = $_GET['category'] ?? null;

// Get recent tracks
$tracks = $db->searchTracks($searchQuery, $languageId, $categoryId);

// Get languages and categories for filters
$languages = $db->getLanguages();
$categories = $db->getCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse - Phonetics Platform</title>
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

        <main class="browse-page">
            <div class="browse-header">
                <h1>Browse Phonetic Recordings</h1>

                <form class="search-form" method="GET" action="browse.php">
                    <div class="search-container">
                        <input type="text" name="q" placeholder="Search recordings..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </div>

                    <div class="filter-container">
                        <select name="language">
                            <option value="">All Languages</option>
                            <?php foreach ($languages as $lang): ?>
                                <option value="<?php echo $lang['id']; ?>" <?php echo ($languageId == $lang['id']) ? 'selected' : ''; ?>>
                                    <?php echo $lang['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($categoryId == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo $cat['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="btn-secondary">Apply Filters</button>
                    </div>
                </form>
            </div>

            <?php if (empty($tracks)): ?>
                <div class="no-results">
                    <i class="fas fa-search fa-3x"></i>
                    <h3>No recordings found</h3>
                    <p>Try adjusting your search criteria or browse all recordings</p>
                </div>
            <?php else: ?>
                <div class="tracks-grid">
                    <?php foreach ($tracks as $track): ?>
                        <div class="track-card">
                            <div class="track-artwork">
                                <i class="fas fa-microphone"></i>
                            </div>
                            <div class="track-info">
                                <h3><?php echo htmlspecialchars($track['title']); ?></h3>
                                <p class="track-meta">
                                    <span class="track-artist"><?php echo htmlspecialchars($track['full_name'] ?? $track['username']); ?></span>
                                    <?php if ($track['language_name']): ?>
                                        <span class="track-language"><?php echo htmlspecialchars($track['language_name']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($track['category_name']): ?>
                                        <span class="track-category"><?php echo htmlspecialchars($track['category_name']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="track-description"><?php echo htmlspecialchars(substr($track['description'] ?? '', 0, 100) . (strlen($track['description'] ?? '') > 100 ? '...' : '')); ?></p>
                                <div class="track-stats">
                                    <span><i class="fas fa-play"></i> <?php echo number_format($track['play_count']); ?></span>
                                    <span><i class="fas fa-download"></i> <?php echo number_format($track['download_count']); ?></span>
                                    <span><i class="fas fa-clock"></i> <?php echo formatDuration($track['duration']); ?></span>
                                </div>
                                <a href="track.php?id=<?php echo $track['id']; ?>" class="btn-primary">Listen Now</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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