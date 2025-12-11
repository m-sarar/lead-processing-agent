<?php
require_once 'config.php';

/**
 * Database operations for the Phonetics Platform
 */

class Database {
    private $conn;

    public function __construct() {
        $this->conn = getDBConnection();
    }

    /**
     * Create database tables if they don't exist
     */
    public function initializeDatabase() {
        $this->createUsersTable();
        $this->createTracksTable();
        $this->createLanguagesTable();
        $this->createCategoriesTable();
        $this->createCommentsTable();
        $this->createLikesTable();
        $this->createPasswordResetsTable(); // Added password_resets table
    }

    /**
     * Create users table
     */
    private function createUsersTable() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            bio TEXT,
            profile_picture VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login DATETIME,
            status ENUM('active', 'suspended', 'banned') DEFAULT 'active',
            language_pref VARCHAR(10) DEFAULT 'en'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->conn->query($sql);
    }

    /**
     * Create tracks table
     */
    private function createTracksTable() {
        $sql = "CREATE TABLE IF NOT EXISTS tracks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            file_path VARCHAR(255) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_size INT NOT NULL,
            duration INT NOT NULL,
            language_id INT,
            category_id INT,
            artwork VARCHAR(255),
            is_public BOOLEAN DEFAULT TRUE,
            is_explicit BOOLEAN DEFAULT FALSE,
            download_count INT DEFAULT 0,
            play_count INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE SET NULL,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->conn->query($sql);
    }

    /**
     * Create languages table
     */
    private function createLanguagesTable() {
        $sql = "CREATE TABLE IF NOT EXISTS languages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(10) NOT NULL UNIQUE,
            name VARCHAR(50) NOT NULL,
            native_name VARCHAR(50),
            is_active BOOLEAN DEFAULT TRUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->conn->query($sql);
    }

    /**
     * Create categories table
     */
    private function createCategoriesTable() {
        $sql = "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            description TEXT,
            is_active BOOLEAN DEFAULT TRUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->conn->query($sql);
    }

    /**
     * Create comments table
     */
    private function createCommentsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            track_id INT NOT NULL,
            user_id INT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_approved BOOLEAN DEFAULT TRUE,
            FOREIGN KEY (track_id) REFERENCES tracks(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->conn->query($sql);
    }

    /**
     * Create likes table
     */
    private function createLikesTable() {
        $sql = "CREATE TABLE IF NOT EXISTS likes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            track_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY (track_id, user_id),
            FOREIGN KEY (track_id) REFERENCES tracks(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->conn->query($sql);
    }

    /**
     * Create password_resets table (NEW)
     */
    private function createPasswordResetsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->conn->query($sql);
    }

    /**
     * Insert initial data into languages table
     */
    public function insertInitialLanguages() {
        $languages = unserialize(SUPPORTED_LANGUAGES);

        foreach ($languages as $code => $name) {
            $sql = "INSERT IGNORE INTO languages (code, name, native_name) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('sss', $code, $name, $name);
            $stmt->execute();
        }
    }

    /**
     * Insert initial data into categories table
     */
    public function insertInitialCategories() {
        $categories = [
            ['Phonetics', 'Phonetic analysis and transcription'],
            ['Linguistics', 'Linguistic research and studies'],
            ['Language Learning', 'Language learning resources'],
            ['Speech Therapy', 'Speech therapy and pronunciation'],
            ['Music', 'Musical phonetics and analysis'],
            ['Research', 'Academic and research recordings']
        ];

        foreach ($categories as $category) {
            $sql = "INSERT IGNORE INTO categories (name, description) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ss', $category[0], $category[1]);
            $stmt->execute();
        }
    }

    /**
     * Get all languages
     */
    public function getLanguages() {
        $sql = "SELECT id, code, name FROM languages WHERE is_active = TRUE ORDER BY name";
        $result = $this->conn->query($sql);

        $languages = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $languages[] = $row;
            }
        }

        return $languages;
    }

    /**
     * Get all categories
     */
    public function getCategories() {
        $sql = "SELECT id, name, description FROM categories WHERE is_active = TRUE ORDER BY name";
        $result = $this->conn->query($sql);

        $categories = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }

        return $categories;
    }

    /**
     * Get recent tracks
     */
    public function getRecentTracks($limit = 6) {
        $sql = "SELECT t.*, u.username, u.full_name, l.name as language_name, c.name as category_name
                FROM tracks t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN languages l ON t.language_id = l.id
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.is_public = TRUE
                ORDER BY t.created_at DESC
                LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $tracks = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tracks[] = $row;
            }
        }

        return $tracks;
    }

    /**
     * Get track by ID
     */
    public function getTrackById($trackId) {
        $sql = "SELECT t.*, u.username, u.full_name, l.name as language_name, c.name as category_name
                FROM tracks t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN languages l ON t.language_id = l.id
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.id = ? AND t.is_public = TRUE";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $trackId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Search tracks
     */
    public function searchTracks($query, $languageId = null, $categoryId = null, $limit = 20) {
        $sql = "SELECT t.*, u.username, u.full_name, l.name as language_name, c.name as category_name
                FROM tracks t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN languages l ON t.language_id = l.id
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.is_public = TRUE
                AND (t.title LIKE ? OR t.description LIKE ? OR u.username LIKE ? OR u.full_name LIKE ?)";

        $params = [$query, $query, $query, $query];
        $types = 'ssss';

        if ($languageId) {
            $sql .= " AND t.language_id = ?";
            $params[] = $languageId;
            $types .= 'i';
        }

        if ($categoryId) {
            $sql .= " AND t.category_id = ?";
            $params[] = $categoryId;
            $types .= 'i';
        }

        $sql .= " ORDER BY t.created_at DESC LIMIT ?";
        $params[] = $limit;
        $types .= 'i';

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $tracks = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tracks[] = $row;
            }
        }

        return $tracks;
    }

    /**
     * Check if password reset token is valid
     */
    public function isValidResetToken($token) {
        $stmt = $this->conn->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires > NOW()");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    /**
     * Get user ID from reset token
     */
    public function getUserIdFromToken($token) {
        $stmt = $this->conn->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires > NOW()");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['user_id'];
        }

        return null;
    }

    /**
     * Delete used reset token
     */
    public function deleteResetToken($token) {
        $stmt = $this->conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->bind_param('s', $token);
        return $stmt->execute();
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}