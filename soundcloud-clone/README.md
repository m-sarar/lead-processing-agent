# Phonetics Platform - SoundCloud-like Website

A professional phonetics platform for uploading, sharing, and discovering phonetic recordings with a comprehensive database and multi-language support.

## Features

- **User Authentication**: Secure login and registration system
- **Audio Upload**: Support for MP3, WAV, OGG formats (up to 20MB)
- **Multi-language Support**: Comprehensive support for phonetics in multiple languages
- **Advanced Dashboard**: Complete control over recordings with analytics
- **Search & Filter**: Find recordings by language, category, or keywords
- **Phonetic Transcription**: Automatic phonetic transcription of text
- **Responsive Design**: Mobile-friendly interface

## Installation

1. **Database Setup**:
   ```bash
   mysql -u root -p
   CREATE DATABASE phonetics_platform;
   USE phonetics_platform;
   ```

2. **Initialize Database Tables**:
   - The application will automatically create tables on first run
   - Or manually run the SQL from `includes/database.php`

3. **Configuration**:
   - Edit `includes/config.php` with your database credentials
   - Set `UPLOAD_DIR` to a writable directory

4. **Run the Application**:
   - Place files in your web server's document root
   - Access via browser: `http://localhost/soundcloud-clone`

## File Structure

```
soundcloud-clone/
├── index.php              # Home page
├── register.php           # User registration
├── login.php              # User login
├── upload.php             # Audio upload page
├── browse.php             # Browse recordings
├── dashboard.php          # User dashboard
├── profile.php            # User profile
├── logout.php             # Logout
├── track.php              # Individual track page
├── css/                   # CSS files
│   └── style.css          # Main stylesheet
├── js/                    # JavaScript files
│   └── main.js            # Main JavaScript
├── includes/              # PHP includes
│   ├── config.php         # Configuration
│   ├── database.php       # Database operations
│   ├── auth.php           # Authentication
│   └── functions.php      # Utility functions
└── uploads/               # Audio uploads directory
```

## Database Tables

1. **users**: User accounts
2. **tracks**: Audio recordings
3. **languages**: Supported languages
4. **categories**: Recording categories
5. **comments**: User comments
6. **likes**: User likes

## Supported Languages

- English
- Arabic
- French
- Spanish
- German
- Russian
- Chinese
- Japanese
- Hindi

## Phonetic Transcription

The platform includes a phonetic transcription utility that converts text to phonetic notation. This is used for:
- Displaying phonetic transcription of track titles
- Helping users understand pronunciation
- Search and filtering by phonetic patterns

## Security Features

- Password hashing with bcrypt
- Input sanitization
- Session management
- File upload validation

## Customization

- Edit `css/style.css` for styling changes
- Modify `includes/config.php` for configuration
- Add more languages in `includes/functions.php`

## License

This project is open source and available under the MIT License.

## Support

For issues or questions, please create an issue in the repository.