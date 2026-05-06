<?php
require_once __DIR__ . '/config.php';

function initializeDatabase() {
    $db = getDB();
    
    $db->exec("
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
        )
    ");
    
    $db->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sender_id INTEGER NOT NULL,
            receiver_id INTEGER NOT NULL,
            message TEXT DEFAULT '',
            photo TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Create admin account if not exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = 'Romar'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $adminPassword = password_hash('Romar', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, gender, age, bio, account_type) VALUES ('Admin', 'Romar', ?, 'other', 0, 'System Administrator', 'premium')");
        $stmt->execute([$adminPassword]);
    }
    
    return $db;
}

initializeDatabase();
?>
