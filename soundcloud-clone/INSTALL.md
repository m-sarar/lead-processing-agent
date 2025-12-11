# Phonetics Platform - Installation Guide

This guide will help you set up the Phonetics Platform on your server.

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache or Nginx recommended)
- Write permissions for the `uploads/` directory

## Installation Steps

### 1. Database Setup

1. **Create the database**:
   ```bash
   mysql -u root -p
   CREATE DATABASE phonetics_platform;
   USE phonetics_platform;
   ```

2. **Initialize the database tables**:
   - Open your browser and navigate to: `http://yourdomain.com/soundcloud-clone/init-db.php`
   - The script will automatically create all necessary tables and insert initial data
   - You should see a "Database Initialization Complete" message

### 2. Configuration

1. **Edit configuration file**:
   - Open `includes/config.php`
   - Update the database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'phonetics_platform');
     ```

2. **Set upload directory permissions**:
   ```bash
   chmod 755 soundcloud-clone/uploads/
   ```

### 3. Create Admin Account

1. Navigate to: `http://yourdomain.com/soundcloud-clone/register.php`
2. Register as the first user (this will automatically become the admin)
3. Use this account to manage the platform

### 4. Verify Installation

1. Visit the home page: `http://yourdomain.com/soundcloud-clone/`
2. Test the registration and login functionality
3. Upload a test audio file to verify the upload feature

## Database Structure

The platform creates the following tables:

- **users**: User accounts and profiles
- **tracks**: Audio recordings with metadata
- **languages**: Supported languages for phonetic transcription
- **categories**: Recording categories
- **comments**: User comments on tracks
- **likes**: User likes on tracks

## Initial Data

The platform automatically inserts:

- **9 supported languages**: English, Arabic, French, Spanish, German, Russian, Chinese, Japanese, Hindi
- **6 categories**: Phonetics, Linguistics, Language Learning, Speech Therapy, Music, Research

## Troubleshooting

### Common Issues

1. **Database connection failed**:
   - Verify your database credentials in `includes/config.php`
   - Check if MySQL server is running
   - Ensure the database user has proper permissions

2. **Uploads not working**:
   - Check `uploads/` directory permissions (should be writable)
   - Verify PHP has file uploads enabled in `php.ini`
   - Check file size limits in `includes/config.php`

3. **Blank page after initialization**:
   - Check PHP error logs
   - Ensure all required PHP extensions are installed (mysqli, pdo_mysql)

## Security Recommendations

1. **Change default admin password** after installation
2. **Set proper file permissions** (755 for directories, 644 for files)
3. **Enable HTTPS** for secure connections
4. **Regularly backup** your database

## Updating

To update to a new version:

1. Backup your database and uploads directory
2. Replace all files except:
   - `includes/config.php`
   - `uploads/` directory
3. Run the initialization script again if database changes are required

## Support

For issues or questions, please refer to the README.md file or create an issue in the repository.

---

**Happy phonetic research!** 🎵