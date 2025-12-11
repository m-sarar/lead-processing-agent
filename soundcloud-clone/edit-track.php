<?php
require_once 'includes/functions.php';
require_once 'includes/database.php';

requireLogin();

$auth = new Auth();
$user = $auth->getCurrentUser();
$db = new Database();
$error = '';
$success = '';

// Get track ID from URL
$trackId = $_GET['id'] ?? 0;

// Get track details
$stmt = $db->conn->prepare("SELECT * FROM tracks WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $trackId, $user['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Track not found or you don't have permission to edit it");
}

$track = $result->fetch_assoc();

// Get languages and categories for dropdowns
$languages = $db->getLanguages();
$categories = $db->getCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $languageId = $_POST['language'] ?? null;
    $categoryId = $_POST['category'] ?? null;
    $isPublic = $_POST['visibility'] ?? 'public';
    $isExplicit = $_POST['explicit'] ?? 'no';

    // Handle file upload if new file is provided
    $filePath = $track['file_path'];
    $fileName = $track['file_name'];
    $fileSize = $track['file_size'];

    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleAudioUpload($_FILES['audio_file']);

        if ($uploadResult['success']) {
            $filePath = $uploadResult['file_path'];
            $fileName = $uploadResult['file_name'];
            $fileSize = $uploadResult['file_size'];
        } else {
            $error = $uploadResult['message'];
        }
    }

    if (empty($error)) {
        $isPublicBool = $isPublic === 'public';
        $isExplicitBool = $isExplicit === 'yes';

        $stmt = $db->conn->prepare("UPDATE tracks SET title = ?, description = ?, file_path = ?, file_name = ?, file_size = ?, language_id = ?, category_id = ?, is_public = ?, is_explicit = ? WHERE id = ?");
        $stmt->bind_param('ssssiisiii', $title, $description, $filePath, $fileName, $fileSize, $languageId, $categoryId, $isPublicBool, $isExplicitBool, $trackId);

        if ($stmt->execute()) {
            $success = 'Track updated successfully!';
            // Refresh track data
            $stmt = $db->conn->prepare("SELECT * FROM tracks WHERE id = ? AND user_id = ?");
            $stmt->bind_param('ii', $trackId, $user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $track = $result->fetch_assoc();
        } else {
            $error = 'Failed to update track: ' . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Track - Phonetics Platform</title>
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

        <main class="edit-track-page">
            <div class="edit-track-card">
                <h1>Edit Recording</h1>

                <?php if ($error): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form action="edit-track.php?id=<?php echo $track['id']; ?>" method="POST" enctype="multipart/form-data" class="edit-track-form">
                    <div class="form-group">
                        <label for="title">Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($track['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($track['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="audio_file">Audio File (Leave blank to keep current)</label>
                        <input type="file" id="audio_file" name="audio_file" accept="audio/*">
                        <small>Current file: <?php echo htmlspecialchars($track['file_name']); ?> (<?php echo formatFileSize($track['file_size']); ?>)</small>
                        <small>Supported formats: MP3, WAV, OGG (Max 20MB)</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="language">Language</label>
                            <select id="language" name="language">
                                <option value="">Select Language</option>
                                <?php foreach ($languages as $lang): ?>
                                    <option value="<?php echo $lang['id']; ?>" <?php echo ($track['language_id'] == $lang['id']) ? 'selected' : ''; ?>>
                                        <?php echo $lang['name']; ?> (<?php echo $lang['code']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($track['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo $cat['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Visibility</label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="visibility" value="public" <?php echo ($track['is_public'] ? 'checked' : ''); ?>>
                                    Public
                                </label>
                                <label>
                                    <input type="radio" name="visibility" value="private" <?php echo (!$track['is_public'] ? 'checked' : ''); ?>>
                                    Private
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Explicit Content</label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="explicit" value="no" <?php echo (!$track['is_explicit'] ? 'checked' : ''); ?>>
                                    No
                                </label>
                                <label>
                                    <input type="radio" name="explicit" value="yes" <?php echo ($track['is_explicit'] ? 'checked' : ''); ?>>
                                    Yes
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="track.php?id=<?php echo $track['id']; ?>" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Save Changes</button>
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