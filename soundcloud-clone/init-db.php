<?php
/**
 * Database Initialization Script
 * This script creates the database tables and inserts initial data
 */

require_once 'includes/config.php';
require_once 'includes/database.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Database Initialization - Phonetics Platform</title>";
echo "<link rel='stylesheet' href='css/style.css'>";
echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<header>";
echo "<nav>";
echo "<div class='logo'>🎵 Phonetics Platform</div>";
echo "</nav>";
echo "</header>";

echo "<main class='init-db-page'>";
echo "<div class='init-db-card'>";
echo "<h1><i class='fas fa-database'></i> Database Initialization</h1>";
echo "<p>This script will create all necessary database tables and insert initial data.</p>";

// Check if database connection is successful
try {
    $db = new Database();
    $conn = $db->conn;

    echo "<div class='init-status'>";
    echo "<h2>Initialization Status</h2>";

    // Create tables
    echo "<div class='init-step'>";
    echo "<h3>1. Creating Database Tables</h3>";
    echo "<div class='progress-bar'>";
    echo "<div class='progress' style='width: 0%'></div>";
    echo "</div>";

    $tables = [
        'users' => 'Creating users table...',
        'tracks' => 'Creating tracks table...',
        'languages' => 'Creating languages table...',
        'categories' => 'Creating categories table...',
        'comments' => 'Creating comments table...',
        'likes' => 'Creating likes table...'
    ];

    $db->initializeDatabase();

    // Insert initial data
    echo "<h3>2. Inserting Initial Data</h3>";
    echo "<div class='progress-bar'>";
    echo "<div class='progress' style='width: 0%'></div>";
    echo "</div>";

    $dataItems = [
        'languages' => 'Inserting supported languages...',
        'categories' => 'Inserting categories...'
    ];

    $db->insertInitialLanguages();
    $db->insertInitialCategories();

    echo "</div>";

    echo "<div class='init-success'>";
    echo "<i class='fas fa-check-circle fa-3x'></i>";
    echo "<h2>Database Initialization Complete!</h2>";
    echo "<p>All tables have been created and initial data has been inserted.</p>";
    echo "<div class='init-actions'>";
    echo "<a href='index.php' class='btn-primary'>Visit Home Page</a>";
    echo "<a href='register.php' class='btn-secondary'>Create Admin Account</a>";
    echo "</div>";
    echo "</div>";

    $db->__destruct();
} catch (Exception $e) {
    echo "<div class='init-error'>";
    echo "<i class='fas fa-exclamation-triangle fa-3x'></i>";
    echo "<h2>Initialization Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in <code>includes/config.php</code></p>";
    echo "</div>";
}

echo "</div>";
echo "</main>";

echo "<footer>";
echo "<div class='footer-content'>";
echo "<div class='footer-section'>";
echo "<h4>Phonetics Platform</h4>";
echo "<p>Professional sound platform for phonetic research and sharing</p>";
echo "</div>";
echo "</div>";
echo "<div class='copyright'>";
echo "<p>&copy; " . date('Y') . " Phonetics Platform. All rights reserved.</p>";
echo "</div>";
echo "</footer>";
echo "</div>";
echo "</body>";
echo "</html>";
?>