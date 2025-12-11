# Phonetics Platform - Database Schema Documentation

This document describes the complete database schema for the Phonetics Platform.

## Overview

The platform uses MySQL with InnoDB engine and utf8mb4 character set for full Unicode support. All tables are created with proper foreign key constraints and appropriate data types.

## Tables

### 1. users

**Purpose**: Stores user account information

```sql
CREATE TABLE users (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

**Fields**:
- `id`: Unique user identifier
- `username`: Unique username (50 chars)
- `email`: Unique email address (100 chars)
- `password`: Hashed password (255 chars)
- `full_name`: User's full name (100 chars)
- `bio`: User biography (TEXT)
- `profile_picture`: URL/path to profile picture
- `created_at`: Account creation timestamp
- `updated_at`: Last update timestamp
- `last_login`: Last login timestamp
- `status`: Account status (active, suspended, banned)
- `language_pref`: Preferred language code (10 chars)

### 2. tracks

**Purpose**: Stores audio recordings and their metadata

```sql
CREATE TABLE tracks (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

**Fields**:
- `id`: Unique track identifier
- `user_id`: Owner of the track (FK to users)
- `title`: Track title (255 chars)
- `description`: Track description (TEXT)
- `file_path`: Full path to audio file
- `file_name`: Original filename
- `file_size`: File size in bytes
- `duration`: Duration in seconds
- `language_id`: Language of the track (FK to languages)
- `category_id`: Category of the track (FK to categories)
- `artwork`: URL/path to artwork/image
- `is_public`: Visibility status (public/private)
- `is_explicit`: Explicit content flag
- `download_count`: Number of downloads
- `play_count`: Number of plays
- `created_at`: Upload timestamp
- `updated_at`: Last update timestamp

### 3. languages

**Purpose**: Stores supported languages for phonetic transcription

```sql
CREATE TABLE languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    native_name VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

**Fields**:
- `id`: Unique language identifier
- `code`: ISO language code (10 chars)
- `name`: Language name in English (50 chars)
- `native_name`: Language name in native script (50 chars)
- `is_active`: Active/inactive status

**Initial Data**: 9 languages (English, Arabic, French, Spanish, German, Russian, Chinese, Japanese, Hindi)

### 4. categories

**Purpose**: Stores track categories

```sql
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

**Fields**:
- `id`: Unique category identifier
- `name`: Category name (50 chars)
- `description`: Category description (TEXT)
- `is_active`: Active/inactive status

**Initial Data**: 6 categories (Phonetics, Linguistics, Language Learning, Speech Therapy, Music, Research)

### 5. comments

**Purpose**: Stores user comments on tracks

```sql
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    track_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_approved BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (track_id) REFERENCES tracks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

**Fields**:
- `id`: Unique comment identifier
- `track_id`: Track being commented on (FK to tracks)
- `user_id`: User who posted the comment (FK to users)
- `content`: Comment text (TEXT)
- `created_at`: Comment creation timestamp
- `updated_at`: Last update timestamp
- `is_approved`: Moderation approval status

### 6. likes

**Purpose**: Stores user likes on tracks

```sql
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    track_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (track_id, user_id),
    FOREIGN KEY (track_id) REFERENCES tracks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

**Fields**:
- `id`: Unique like identifier
- `track_id`: Track being liked (FK to tracks)
- `user_id`: User who liked the track (FK to users)
- `created_at`: Like timestamp
- `UNIQUE KEY`: Ensures one like per user per track

### 7. password_resets

**Purpose**: Stores password reset tokens

```sql
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

**Fields**:
- `id`: Unique reset identifier
- `user_id`: User requesting reset (FK to users)
- `token`: Reset token (255 chars)
- `expires`: Token expiration timestamp
- `created_at`: Token creation timestamp

## Relationships

### User Relationships
- One user can have many tracks (1:N)
- One user can have many comments (1:N)
- One user can have many likes (1:N)

### Track Relationships
- One track can have many comments (1:N)
- One track can have many likes (1:N)
- One track belongs to one user (N:1)
- One track belongs to one language (N:1, nullable)
- One track belongs to one category (N:1, nullable)

### Language Relationships
- One language can have many tracks (1:N)

### Category Relationships
- One category can have many tracks (1:N)

## Indexes

All primary keys are automatically indexed. Foreign keys have indexes for performance.

## Character Set and Collation

All tables use:
- **Character Set**: utf8mb4
- **Collation**: utf8mb4_unicode_ci

This ensures full Unicode support including emoji and special characters.

## Storage Engine

All tables use **InnoDB** engine for:
- Transaction support
- Foreign key constraints
- Better performance with large datasets

## Initialization

The database is automatically initialized when you visit `init-db.php`. This script:
1. Creates all 7 tables
2. Inserts initial language data (9 languages)
3. Inserts initial category data (6 categories)

## Backups

Regular backups are recommended. The admin panel includes a backup tool (placeholder for now).

## Migration Strategy

For future updates:
1. Add new columns with DEFAULT values
2. Create new tables as needed
3. Use ALTER TABLE for schema changes
4. Document all changes in migration files

## Performance Considerations

- All foreign keys are indexed
- Text fields are used appropriately
- Timestamps are indexed for sorting
- Proper data types are used for each field

## Security

- Passwords are hashed with bcrypt
- Sensitive data is properly escaped
- Input validation is implemented
- SQL injection prevention via prepared statements

This schema provides a solid foundation for the Phonetics Platform with room for future expansion.