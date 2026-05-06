# LoveConnect - Dating Web App

A complete responsive dating web application built with PHP, SQLite, and Bootstrap 5.

## Features

- **User Registration & Login** - Register with name, email, password, gender, age, bio, profile photo, and location
- **User Dashboard** - View other profiles with distance calculation
- **Real-time Chat** - Message other users with conversation history
- **Photo Sending** - Premium feature: send photos in chat (locked for free users)
- **Account Types** - Free and Premium accounts with different access levels
- **Admin Panel** - Manage users, upgrade/downgrade accounts, view statistics
- **Responsive Design** - Modern, mobile-friendly UI with Bootstrap 5

## Requirements

- PHP 7.4 or higher
- SQLite3 extension enabled
- GD extension (for image handling)
- Web server (Apache/Nginx) or PHP built-in server

## Setup Instructions

### 1. Clone or download the project

```bash
git clone <repository-url>
cd dating-app
```

### 2. Ensure proper permissions

```bash
chmod 755 uploads/
chmod 755 uploads/chat/
chmod 755 database/
```

### 3. Start the PHP development server

```bash
php -S localhost:8000
```

### 4. Open in browser

Navigate to `http://localhost:8000`

The database will be automatically created on first visit.

## Admin Access

- **URL:** `/admin_login.php`
- **Email:** `Romar`
- **Password:** `Romar`

## Project Structure

```
dating-app/
├── includes/
│   ├── config.php          # Configuration, DB connection, helper functions
│   ├── init_db.php         # Database initialization
│   ├── header.php          # Common header with navigation
│   └── footer.php          # Common footer
├── uploads/                # User profile photos
│   └── chat/              # Chat photos
├── database/
│   ├── schema.sql         # SQL schema file
│   └── dating_app.db     # SQLite database (auto-created)
├── index.php              # Home page
├── login.php              # User login
├── register.php           # User registration
├── logout.php             # Logout handler
├── dashboard.php          # User dashboard with profiles
├── profile.php            # User profile page
├── chat.php               # Chat system
├── upgrade.php            # Upgrade page
├── update_location.php    # AJAX location updater
├── admin_login.php        # Admin login
├── admin_dashboard.php    # Admin panel
└── README.md              # This file
```

## Database Tables

### users
| Column | Type | Description |
|--------|------|-------------|
| id | INTEGER | Primary key |
| name | TEXT | User's full name |
| email | TEXT | Unique email |
| password | TEXT | Hashed password |
| gender | TEXT | male/female/other |
| age | INTEGER | User's age |
| bio | TEXT | User biography |
| profile_photo | TEXT | Photo filename |
| latitude | REAL | GPS latitude |
| longitude | REAL | GPS longitude |
| account_type | TEXT | free/premium |
| created_at | DATETIME | Registration date |

### messages
| Column | Type | Description |
|--------|------|-------------|
| id | INTEGER | Primary key |
| sender_id | INTEGER | FK to users |
| receiver_id | INTEGER | FK to users |
| message | TEXT | Message content |
| photo | TEXT | Photo filename (premium only) |
| created_at | DATETIME | Send timestamp |

## Security Features

- Password hashing with `password_hash()` (bcrypt)
- Prepared statements for all database queries (SQL injection prevention)
- Session-based authentication
- Image upload validation (MIME type, file size)
- XSS prevention with `htmlspecialchars()`
- Admin pages restricted to admin only
- User pages restricted to logged-in users only

## Account Types

- **Free** - Can view profiles, send text messages, see distance
- **Premium** - All free features + send/receive photos in chat

## License

MIT License
