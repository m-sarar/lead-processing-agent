<?php
require_once 'config.php';
require_once 'database.php';
require_once 'auth.php';

/**
 * General utility functions
 */

/**
 * Get phonetic transcription of text
 */
function getPhoneticTranscription($text) {
    if (empty($text)) {
        return '';
    }

    $text = strtolower($text);
    $vowels = ['a', 'e', 'i', 'o', 'u'];
    $consonantSounds = [
        'b' => 'b', 'c' => 'k', 'd' => 'd', 'f' => 'f', 'g' => 'g', 'h' => 'h',
        'j' => 'j', 'k' => 'k', 'l' => 'l', 'm' => 'm', 'n' => 'n', 'p' => 'p',
        'q' => 'k', 'r' => 'r', 's' => 's', 't' => 't', 'v' => 'v', 'w' => 'w',
        'x' => 'ks', 'y' => 'i', 'z' => 'z',
        'ch' => 'ch', 'sh' => 'sh', 'th' => 'θ', 'ph' => 'f', 'gh' => 'g',
        'kn' => 'n', 'wh' => 'w', 'qu' => 'kw'
    ];

    $result = '';
    $i = 0;
    $len = strlen($text);

    while ($i < $len) {
        $found = false;

        // Check for digraphs first
        foreach ($consonantSounds as $digraph => $sound) {
            if (strlen($digraph) === 2 && substr($text, $i, 2) === $digraph) {
                $result .= $sound;
                $i += 2;
                $found = true;
                break;
            }
        }

        if ($found) continue;

        // Check for single characters
        $char = $text[$i];
        if (isset($consonantSounds[$char])) {
            $result .= $consonantSounds[$char];
            $i += 1;
            $found = true;
        }

        if ($found) continue;

        // Vowels - keep as is
        if (in_array($char, $vowels)) {
            $result .= $char;
            $i += 1;
            $found = true;
        }

        if ($found) continue;

        // Default: just add the character
        $result .= $char;
        $i += 1;
    }

    return $result;
}

/**
 * Format file size
 */
function formatFileSize($bytes, $decimals = 2) {
    $size = ['B', 'KB', 'MB', 'GB', 'TB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $size[$factor];
}

/**
 * Format duration (seconds to MM:SS)
 */
function formatDuration($seconds) {
    $minutes = floor($seconds / 60);
    $secs = $seconds % 60;
    return sprintf("%02d:%02d", $minutes, $secs);
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($originalName) {
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $name = pathinfo($originalName, PATHINFO_FILENAME);
    $timestamp = time();
    $random = rand(1000, 9999);
    return "{$name}_{$timestamp}_{$random}.{$ext}";
}

/**
 * Validate audio file upload
 */
function validateAudioUpload($file) {
    $errors = [];

    // Check file was uploaded
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error';
    }

    // Check file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $errors[] = 'File too large. Max size: ' . formatFileSize(MAX_UPLOAD_SIZE);
    }

    // Check file type
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, ALLOWED_AUDIO_TYPES)) {
        $errors[] = 'Invalid file type. Allowed types: ' . implode(', ', ALLOWED_EXTENSIONS);
    }

    return $errors;
}

/**
 * Handle audio file upload
 */
function handleAudioUpload($file) {
    $errors = validateAudioUpload($file);

    if (!empty($errors)) {
        return ['success' => false, 'message' => implode('; ', $errors)];
    }

    // Generate unique filename
    $originalName = $file['name'];
    $uniqueName = generateUniqueFilename($originalName);
    $uploadPath = UPLOAD_DIR . $uniqueName;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return [
            'success' => true,
            'file_path' => $uploadPath,
            'file_name' => $uniqueName,
            'file_size' => $file['size'],
            'original_name' => $originalName
        ];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}

/**
 * Get audio duration from file
 */
function getAudioDuration($filePath) {
    // This is a simplified version
    // In production, you would use a library like getID3 or FFmpeg
    return 120; // Default to 2 minutes for demo
}

/**
 * Sanitize input
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to URL
 */
function redirect($url, $statusCode = 303) {
    header('Location: ' . $url, true, $statusCode);
    exit();
}

/**
 * Check if user is logged in, redirect if not
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

/**
 * Get current language
 */
function getCurrentLanguage() {
    return $_SESSION['language_pref'] ?? 'en';
}

/**
 * Set language preference
 */
function setLanguagePreference($lang) {
    if (isset($_SESSION['user_id'])) {
        $db = new Database();
        $db->conn->query("UPDATE users SET language_pref = '{$lang}' WHERE id = {$_SESSION['user_id']}");
        $_SESSION['language_pref'] = $lang;
    }
}

/**
 * Get language translations
 */
function getTranslations($lang = null) {
    $lang = $lang ?? getCurrentLanguage();

    $translations = [
        'en' => [
            'welcome' => 'Welcome',
            'home' => 'Home',
            'browse' => 'Browse',
            'upload' => 'Upload',
            'dashboard' => 'Dashboard',
            'login' => 'Login',
            'register' => 'Register',
            'logout' => 'Logout',
            'profile' => 'Profile',
            'search' => 'Search',
            'recent_uploads' => 'Recent Uploads',
            'no_results' => 'No results found',
            'loading' => 'Loading...'
        ],
        'ar' => [
            'welcome' => 'مرحبا',
            'home' => 'الرئيسية',
            'browse' => 'تصفح',
            'upload' => 'تحميل',
            'dashboard' => 'لوحة التحكم',
            'login' => 'تسجيل الدخول',
            'register' => 'تسجيل',
            'logout' => 'تسجيل الخروج',
            'profile' => 'الملف الشخصي',
            'search' => 'بحث',
            'recent_uploads' => 'التحميلات الأخيرة',
            'no_results' => 'لا يوجد نتائج',
            'loading' => 'جاري التحميل...'
        ],
        // Add more languages as needed
    ];

    return $translations[$lang] ?? $translations['en'];
}

/**
 * Translate text
 */
function __($key, $lang = null) {
    $translations = getTranslations($lang);
    return $translations[$key] ?? $key;
}