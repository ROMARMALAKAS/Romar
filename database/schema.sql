-- LoveConnect Dating App Database Schema
-- SQLite Compatible

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    gender TEXT NOT NULL,
    age INTEGER NOT NULL,
    bio TEXT DEFAULT '',
    profile_photo TEXT DEFAULT 'default.png',
    latitude REAL DEFAULT NULL,
    longitude REAL DEFAULT NULL,
    account_type TEXT DEFAULT 'free' CHECK(account_type IN ('free', 'premium')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sender_id INTEGER NOT NULL,
    receiver_id INTEGER NOT NULL,
    message TEXT DEFAULT '',
    photo TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create admin account
-- Email: Romar
-- Password: Romar (hashed)
INSERT OR IGNORE INTO users (name, email, password, gender, age, bio, account_type) 
VALUES ('Admin', 'Romar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'other', 0, 'System Administrator', 'premium');
-- Note: The above hash is a placeholder. The actual admin uses hardcoded credentials in admin_login.php (Email: Romar, Password: Romar)

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_messages_sender ON messages(sender_id);
CREATE INDEX IF NOT EXISTS idx_messages_receiver ON messages(receiver_id);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_account_type ON users(account_type);
