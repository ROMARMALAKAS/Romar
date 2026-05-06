const initSqlJs = require('sql.js');
const bcrypt = require('bcryptjs');

let db = null;
let SQL = null;

async function getDB() {
    if (db) return db;
    
    if (!SQL) {
        SQL = await initSqlJs();
    }
    
    db = new SQL.Database();
    
    db.run(`
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
            account_type TEXT DEFAULT 'free',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    `);
    
    db.run(`
        CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sender_id INTEGER NOT NULL,
            receiver_id INTEGER NOT NULL,
            message TEXT DEFAULT '',
            photo TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id),
            FOREIGN KEY (receiver_id) REFERENCES users(id)
        )
    `);
    
    // Create admin
    const adminExists = db.exec("SELECT id FROM users WHERE email = 'Romar'");
    if (adminExists.length === 0 || adminExists[0].values.length === 0) {
        const hashedPw = bcrypt.hashSync('Romar', 10);
        db.run("INSERT INTO users (name, email, password, gender, age, bio, account_type) VALUES (?, ?, ?, ?, ?, ?, ?)",
            ['Admin', 'Romar', hashedPw, 'other', 0, 'System Administrator', 'premium']);
    }
    
    // Seed some demo users for showcase
    const userCount = db.exec("SELECT COUNT(*) FROM users WHERE email != 'Romar'");
    if (userCount[0].values[0][0] === 0) {
        const demoPassword = bcrypt.hashSync('password123', 10);
        const demoUsers = [
            ['Sofia Martinez', 'sofia@demo.com', demoPassword, 'female', 23, 'Love hiking and coffee! Looking for someone adventurous.', 14.5995, 120.9842, 'free'],
            ['James Rivera', 'james@demo.com', demoPassword, 'male', 25, 'Musician and foodie. Let\'s explore the city together!', 14.5547, 121.0244, 'premium'],
            ['Mia Santos', 'mia@demo.com', demoPassword, 'female', 22, 'Beach lover and bookworm. Swipe right if you love sunsets!', 14.6091, 120.9893, 'free'],
            ['Daniel Cruz', 'daniel@demo.com', demoPassword, 'male', 27, 'Fitness enthusiast and dog dad. Looking for my gym partner.', 14.5764, 121.0013, 'free'],
            ['Isabella Reyes', 'isabella@demo.com', demoPassword, 'female', 24, 'Nurse by day, artist by night. Looking for genuine connections.', 14.5896, 121.0561, 'premium'],
        ];
        demoUsers.forEach(u => {
            db.run("INSERT INTO users (name, email, password, gender, age, bio, latitude, longitude, account_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", u);
        });
    }
    
    return db;
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    if (!lat1 || !lon1 || !lat2 || !lon2) return null;
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return (R * c).toFixed(1);
}

module.exports = { getDB, calculateDistance };
