<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'phonetics_platform');

// Site configuration
define('SITE_NAME', 'Phonetics Platform');
define('SITE_URL', 'http://localhost/soundcloud-clone');
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set charset to utf8mb4 for full Unicode support
    $conn->set_charset("utf8mb4");

    return $conn;
}

// Supported languages
define('SUPPORTED_LANGUAGES', serialize([
    'en' => 'English',
    'ar' => 'Arabic',
    'fr' => 'French',
    'es' => 'Spanish',
    'de' => 'German',
    'ru' => 'Russian',
    'zh' => 'Chinese',
    'ja' => 'Japanese',
    'hi' => 'Hindi'
]));

// Audio formats allowed
define('ALLOWED_AUDIO_TYPES', [
    'audio/mpeg',
    'audio/wav',
    'audio/ogg',
    'audio/mp3',
    'audio/wave',
    'audio/x-wav',
    'audio/x-pn-wav'
]);

// File extensions allowed
define('ALLOWED_EXTENSIONS', ['mp3', 'wav', 'ogg', 'wave']);

// Max upload size (20MB)
define('MAX_UPLOAD_SIZE', 20971520); // 20MB in bytes