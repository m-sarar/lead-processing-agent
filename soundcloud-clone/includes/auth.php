<?php
require_once 'config.php';

/**
 * Authentication functions
 */

class Auth {
    private $conn;

    public function __construct() {
        $this->conn = getDBConnection();
    }

    /**
     * Register a new user
     */
    public function register($username, $email, $password, $fullName = '') {
        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        // Check if username or email already exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert new user
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $username, $email, $hashedPassword, $fullName);

        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $userId];
        } else {
            return ['success' => false, 'message' => 'Registration failed: ' . $stmt->error];
        }
    }

    /**
     * Login user
     */
    public function login($username, $password) {
        // Check if user exists
        $stmt = $this->conn->prepare("SELECT id, username, email, password, full_name, language_pref FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Invalid username or email'];
        }

        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Update last login
            $stmt = $this->conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param('i', $user['id']);
            $stmt->execute();

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['language_pref'] = $user['language_pref'];

            return ['success' => true, 'message' => 'Login successful'];
        } else {
            return ['success' => false, 'message' => 'Invalid password'];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT id, username, email, full_name, bio, profile_picture, language_pref FROM users WHERE id = ?");
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    /**
     * Update user profile
     */
    public function updateProfile($fullName, $bio, $languagePref, $profilePicture = null) {
        $userId = $_SESSION['user_id'];

        if ($profilePicture) {
            $stmt = $this->conn->prepare("UPDATE users SET full_name = ?, bio = ?, language_pref = ?, profile_picture = ? WHERE id = ?");
            $stmt->bind_param('ssssi', $fullName, $bio, $languagePref, $profilePicture, $userId);
        } else {
            $stmt = $this->conn->prepare("UPDATE users SET full_name = ?, bio = ?, language_pref = ? WHERE id = ?");
            $stmt->bind_param('sssi', $fullName, $bio, $languagePref, $userId);
        }

        if ($stmt->execute()) {
            // Update session with new data
            $_SESSION['full_name'] = $fullName;
            $_SESSION['language_pref'] = $languagePref;

            return ['success' => true, 'message' => 'Profile updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Profile update failed: ' . $stmt->error];
        }
    }

    /**
     * Change password
     */
    public function changePassword($currentPassword, $newPassword) {
        $userId = $_SESSION['user_id'];

        // Get current password hash
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters'];
        }

        // Hash and update password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param('si', $hashedPassword, $userId);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Password changed successfully'];
        } else {
            return ['success' => false, 'message' => 'Password change failed: ' . $stmt->error];
        }
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}