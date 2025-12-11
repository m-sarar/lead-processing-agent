<?php
require_once 'includes/functions.php';
require_once 'includes/audio-processor.php';

requireLogin();

$auth = new Auth();
$user = $auth->getCurrentUser();
$processor = new AudioProcessor();
$error = '';
$success = '';
$audioInfo = null;
$waveform = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audio_file'])) {
    if ($_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
        $tempFile = $_FILES['audio_file']['tmp_name'];
        $originalName = $_FILES['audio_file']['name'];

        // Process the audio file
        $result = $processor->processUploadedAudio($tempFile, $tempFile);

        if ($result['success']) {
            $audioInfo = $result;
            $waveform = $result['waveform'] ?? '';

            $success = 'Audio file processed successfully!';
        } else {
            $error = $result['message'] ?? 'Failed to process audio file';
        }
    } else {
        $error = 'Error uploading file';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audio Processing - Phonetics Platform</title>
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

        <main class="audio-processing-page">
            <div class="processing-header">
                <h1>Audio Processing Tools</h1>
                <p>Upload and process audio files for professional quality</p>
            </div>

            <div class="processing-card">
                <h2>Upload Audio File</h2>

                <?php if ($error): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form action="audio-processing.php" method="POST" enctype="multipart/form-data" class="processing-form">
                    <div class="form-group">
                        <label for="audio_file">Select Audio File</label>
                        <input type="file" id="audio_file" name="audio_file" accept="audio/*" required>
                        <small>Supported formats: MP3, WAV, OGG (Max 20MB)</small>
                    </div>

                    <button type="submit" class="btn-primary">Process Audio</button>
                </form>
            </div>

            <?php if ($audioInfo): ?>
                <div class="processing-results">
                    <h2>Processing Results</h2>

                    <div class="result-section">
                        <h3>Audio Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Format</span>
                                <span class="info-value"><?php echo htmlspecialchars($audioInfo['format']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Duration</span>
                                <span class="info-value"><?php echo formatDuration($audioInfo['duration']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Bitrate</span>
                                <span class="info-value"><?php echo $audioInfo['bitrate']; ?> kbps</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Sample Rate</span>
                                <span class="info-value"><?php echo $audioInfo['sample_rate']; ?> Hz</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Channels</span>
                                <span class="info-value"><?php echo $audioInfo['channels']; ?> (<?php echo $audioInfo['channels'] == 1 ? 'Mono' : 'Stereo'; ?>)</span>
                            </div>
                        </div>
                    </div>

                    <?php if ($waveform): ?>
                        <div class="result-section">
                            <h3>Waveform Visualization</h3>
                            <div class="waveform-container">
                                <img src="<?php echo $waveform; ?>" alt="Audio Waveform" class="waveform-image">
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="result-section">
                        <h3>Processing Options</h3>
                        <div class="options-grid">
                            <div class="option-card">
                                <i class="fas fa-volume-up fa-2x"></i>
                                <h4>Normalize Volume</h4>
                                <p>Adjust audio volume to consistent level</p>
                                <button class="btn-secondary" onclick="alert('Volume normalization would be processed here')">Normalize</button>
                            </div>
                            <div class="option-card">
                                <i class="fas fa-file-export fa-2x"></i>
                                <h4>Convert Format</h4>
                                <p>Convert to different audio formats</p>
                                <button class="btn-secondary" onclick="alert('Format conversion would be processed here')">Convert</button>
                            </div>
                            <div class="option-card">
                                <i class="fas fa-crop fa-2x"></i>
                                <h4>Trim Audio</h4>
                                <p>Cut specific sections from audio</p>
                                <button class="btn-secondary" onclick="alert('Audio trimming would be processed here')">Trim</button>
                            </div>
                            <div class="option-card">
                                <i class="fas fa-microphone-alt fa-2x"></i>
                                <h4>Enhance Quality</h4>
                                <p>Improve audio quality and clarity</p>
                                <button class="btn-secondary" onclick="alert('Quality enhancement would be processed here')">Enhance</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="processing-info">
                <h2>Audio Processing Features</h2>
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <h3>Professional Quality</h3>
                        <p>Ensure your audio recordings meet professional standards</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <h3>Volume Normalization</h3>
                        <p>Automatically adjust volume levels for consistency</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <h3>Format Conversion</h3>
                        <p>Convert between MP3, WAV, OGG, and other formats</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <h3>Waveform Analysis</h3>
                        <p>Visual representation of your audio files</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <h3>Metadata Extraction</h3>
                        <p>Extract and edit audio file metadata</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <h3>Quality Enhancement</h3>
                        <p>Improve audio clarity and reduce noise</p>
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
</body>
</html>