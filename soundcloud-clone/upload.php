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
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $languageId = $_POST['language'] ?? null;
    $categoryId = $_POST['category'] ?? null;
    $isPublic = $_POST['visibility'] ?? 'public';
    $isExplicit = $_POST['explicit'] ?? 'no';

    // Handle file upload
    if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleAudioUpload($_FILES['audio_file']);

        if ($uploadResult['success']) {
            // Get audio duration (simplified for demo)
            $duration = getAudioDuration($uploadResult['file_path']);

            // Insert track into database
            $isPublicBool = $isPublic === 'public';
            $isExplicitBool = $isExplicit === 'yes';

            $stmt = $db->conn->prepare("INSERT INTO tracks (user_id, title, description, file_path, file_name, file_size, duration, language_id, category_id, is_public, is_explicit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('isssisiiiii', $user['id'], $title, $description, $uploadResult['file_path'], $uploadResult['file_name'], $uploadResult['file_size'], $duration, $languageId, $categoryId, $isPublicBool, $isExplicitBool);

            if ($stmt->execute()) {
                $success = 'Track uploaded successfully!';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Failed to save track: ' . $stmt->error;
            }
        } else {
            $error = $uploadResult['message'];
        }
    } else {
        $error = 'No file uploaded or upload error';
    }
}

// Get languages and categories for dropdowns
$languages = $db->getLanguages();
$categories = $db->getCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload - Phonetics Platform</title>
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

        <main class="upload-page">
            <div class="upload-card">
                <h1>Upload Phonetic Recording</h1>

                <?php if ($error): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form action="upload.php" method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <label for="title">Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title" value="<?php echo $_POST['title'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo $_POST['description'] ?? ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="audio_file">Audio File <span class="required">*</span></label>
                        <input type="file" id="audio_file" name="audio_file" accept="audio/*" required>
                        <small>Supported formats: MP3, WAV, OGG (Max 20MB)</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="language">Language</label>
                            <select id="language" name="language">
                                <option value="">Select Language</option>
                                <?php foreach ($languages as $lang): ?>
                                    <option value="<?php echo $lang['id']; ?>" <?php echo (($_POST['language'] ?? '') == $lang['id']) ? 'selected' : ''; ?>>
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
                                    <option value="<?php echo $cat['id']; ?>" <?php echo (($_POST['category'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
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
                                    <input type="radio" name="visibility" value="public" <?php echo (($_POST['visibility'] ?? 'public') == 'public') ? 'checked' : ''; ?>>
                                    Public
                                </label>
                                <label>
                                    <input type="radio" name="visibility" value="private" <?php echo (($_POST['visibility'] ?? 'public') == 'private') ? 'checked' : ''; ?>>
                                    Private
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Explicit Content</label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="explicit" value="no" <?php echo (($_POST['explicit'] ?? 'no') == 'no') ? 'checked' : ''; ?>>
                                    No
                                </label>
                                <label>
                                    <input type="radio" name="explicit" value="yes" <?php echo (($_POST['explicit'] ?? 'no') == 'yes') ? 'checked' : ''; ?>>
                                    Yes
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Upload Recording</button>
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