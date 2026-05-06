const express = require('express');
const session = require('express-session');
const bcrypt = require('bcryptjs');
const path = require('path');
const { getDB, calculateDistance } = require('./db');

const app = express();

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, '..', 'views'));
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use('/public', express.static(path.join(__dirname, '..', 'public')));

app.use(session({
    secret: 'loveconnect-secret-key-2024',
    resave: false,
    saveUninitialized: false,
    cookie: { maxAge: 24 * 60 * 60 * 1000 }
}));

// Middleware
app.use((req, res, next) => {
    res.locals.user = req.session.user || null;
    res.locals.isAdmin = req.session.isAdmin || false;
    next();
});

function requireLogin(req, res, next) {
    if (!req.session.user) return res.redirect('/login');
    next();
}

function requireAdmin(req, res, next) {
    if (!req.session.isAdmin) return res.redirect('/admin/login');
    next();
}

// HOME
app.get('/', (req, res) => {
    if (req.session.user) return res.redirect('/dashboard');
    res.render('index');
});

// LOGIN
app.get('/login', (req, res) => {
    if (req.session.user) return res.redirect('/dashboard');
    res.render('login', { error: null });
});

app.post('/login', async (req, res) => {
    const { email, password } = req.body;
    const db = await getDB();
    const result = db.exec("SELECT * FROM users WHERE email = ?", [email]);
    
    if (result.length > 0 && result[0].values.length > 0) {
        const cols = result[0].columns;
        const row = result[0].values[0];
        const user = {};
        cols.forEach((col, i) => user[col] = row[i]);
        
        if (bcrypt.compareSync(password, user.password)) {
            req.session.user = { id: user.id, name: user.name, email: user.email, account_type: user.account_type };
            return res.redirect('/dashboard');
        }
    }
    res.render('login', { error: 'Invalid email or password.' });
});

// REGISTER
app.get('/register', (req, res) => {
    if (req.session.user) return res.redirect('/dashboard');
    res.render('register', { error: null, success: null });
});

app.post('/register', async (req, res) => {
    const { name, email, password, confirm_password, gender, age, bio, latitude, longitude } = req.body;
    
    if (!name || !email || !password || !gender || !age || parseInt(age) < 18) {
        return res.render('register', { error: 'Please fill all required fields. Must be 18+.', success: null });
    }
    if (password !== confirm_password) {
        return res.render('register', { error: 'Passwords do not match.', success: null });
    }
    if (password.length < 6) {
        return res.render('register', { error: 'Password must be at least 6 characters.', success: null });
    }
    
    const db = await getDB();
    const existing = db.exec("SELECT id FROM users WHERE email = ?", [email]);
    if (existing.length > 0 && existing[0].values.length > 0) {
        return res.render('register', { error: 'Email already registered.', success: null });
    }
    
    const hashedPw = bcrypt.hashSync(password, 10);
    db.run("INSERT INTO users (name, email, password, gender, age, bio, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [name, email, hashedPw, gender, parseInt(age), bio || '', parseFloat(latitude) || null, parseFloat(longitude) || null]);
    
    res.render('register', { error: null, success: 'Registration successful! You can now login.' });
});

// LOGOUT
app.get('/logout', (req, res) => {
    req.session.destroy();
    res.redirect('/login');
});

// DASHBOARD
app.get('/dashboard', requireLogin, async (req, res) => {
    const db = await getDB();
    const currentResult = db.exec("SELECT * FROM users WHERE id = ?", [req.session.user.id]);
    let currentUser = {};
    if (currentResult.length > 0) {
        const cols = currentResult[0].columns;
        const row = currentResult[0].values[0];
        cols.forEach((col, i) => currentUser[col] = row[i]);
    }
    
    const usersResult = db.exec("SELECT * FROM users WHERE id != ? AND email != 'Romar' ORDER BY created_at DESC", [req.session.user.id]);
    let users = [];
    if (usersResult.length > 0) {
        const cols = usersResult[0].columns;
        users = usersResult[0].values.map(row => {
            const u = {};
            cols.forEach((col, i) => u[col] = row[i]);
            u.distance = calculateDistance(currentUser.latitude, currentUser.longitude, u.latitude, u.longitude);
            return u;
        });
    }
    
    res.render('dashboard', { currentUser, users });
});

// PROFILE
app.get('/profile', requireLogin, async (req, res) => {
    const db = await getDB();
    const result = db.exec("SELECT * FROM users WHERE id = ?", [req.session.user.id]);
    let user = {};
    if (result.length > 0) {
        const cols = result[0].columns;
        const row = result[0].values[0];
        cols.forEach((col, i) => user[col] = row[i]);
    }
    res.render('profile', { user, error: null, success: null });
});

app.post('/profile', requireLogin, async (req, res) => {
    const { name, age, gender, bio } = req.body;
    const db = await getDB();
    db.run("UPDATE users SET name = ?, age = ?, gender = ?, bio = ? WHERE id = ?",
        [name, parseInt(age), gender, bio || '', req.session.user.id]);
    req.session.user.name = name;
    
    const result = db.exec("SELECT * FROM users WHERE id = ?", [req.session.user.id]);
    let user = {};
    if (result.length > 0) {
        const cols = result[0].columns;
        const row = result[0].values[0];
        cols.forEach((col, i) => user[col] = row[i]);
    }
    res.render('profile', { user, error: null, success: 'Profile updated!' });
});

// CHAT
app.get('/chat', requireLogin, async (req, res) => {
    const db = await getDB();
    const userId = req.session.user.id;
    const selectedUserId = parseInt(req.query.user) || 0;
    
    // Get conversations
    const convResult = db.exec(`
        SELECT DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as other_id
        FROM messages WHERE sender_id = ? OR receiver_id = ?
    `, [userId, userId, userId]);
    
    let convIds = convResult.length > 0 ? convResult[0].values.map(r => r[0]) : [];
    if (selectedUserId && !convIds.includes(selectedUserId)) convIds.push(selectedUserId);
    
    let conversations = [];
    convIds.forEach(id => {
        const uResult = db.exec("SELECT id, name, profile_photo, account_type FROM users WHERE id = ? AND email != 'Romar'", [id]);
        if (uResult.length > 0 && uResult[0].values.length > 0) {
            const cols = uResult[0].columns;
            const row = uResult[0].values[0];
            const u = {};
            cols.forEach((col, i) => u[col] = row[i]);
            
            const lastMsg = db.exec("SELECT message, created_at FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at DESC LIMIT 1",
                [userId, id, id, userId]);
            u.last_message = lastMsg.length > 0 && lastMsg[0].values.length > 0 ? lastMsg[0].values[0][0] : '';
            u.last_time = lastMsg.length > 0 && lastMsg[0].values.length > 0 ? lastMsg[0].values[0][1] : '';
            conversations.push(u);
        }
    });
    
    let selectedUser = null;
    let messages = [];
    if (selectedUserId) {
        const suResult = db.exec("SELECT * FROM users WHERE id = ?", [selectedUserId]);
        if (suResult.length > 0 && suResult[0].values.length > 0) {
            const cols = suResult[0].columns;
            const row = suResult[0].values[0];
            selectedUser = {};
            cols.forEach((col, i) => selectedUser[col] = row[i]);
        }
        
        const msgResult = db.exec("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC",
            [userId, selectedUserId, selectedUserId, userId]);
        if (msgResult.length > 0) {
            const cols = msgResult[0].columns;
            messages = msgResult[0].values.map(row => {
                const m = {};
                cols.forEach((col, i) => m[col] = row[i]);
                return m;
            });
        }
    }
    
    const currentUser = req.session.user;
    const showUpgradeModal = req.query.error === 'upgrade';
    res.render('chat', { conversations, selectedUser, messages, currentUser, selectedUserId, showUpgradeModal });
});

app.post('/chat', requireLogin, async (req, res) => {
    const { message, receiver_id } = req.body;
    const userId = req.session.user.id;
    const db = await getDB();
    
    if (message && message.trim()) {
        const now = new Date().toISOString().replace('T', ' ').substring(0, 19);
        db.run("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, ?)",
            [userId, parseInt(receiver_id), message.trim(), now]);
    }
    res.redirect('/chat?user=' + receiver_id);
});

// UPGRADE
app.get('/upgrade', requireLogin, (req, res) => {
    res.render('upgrade', { currentUser: req.session.user });
});

// UPDATE LOCATION
app.post('/update-location', requireLogin, async (req, res) => {
    const { latitude, longitude } = req.body;
    if (latitude && longitude) {
        const db = await getDB();
        db.run("UPDATE users SET latitude = ?, longitude = ? WHERE id = ?",
            [parseFloat(latitude), parseFloat(longitude), req.session.user.id]);
    }
    res.json({ success: true });
});

// ADMIN LOGIN
app.get('/admin/login', (req, res) => {
    if (req.session.isAdmin) return res.redirect('/admin/dashboard');
    res.render('admin_login', { error: null });
});

app.post('/admin/login', (req, res) => {
    const { email, password } = req.body;
    if (email === 'Romar' && password === 'Romar') {
        req.session.isAdmin = true;
        return res.redirect('/admin/dashboard');
    }
    res.render('admin_login', { error: 'Invalid admin credentials.' });
});

// ADMIN DASHBOARD
app.get('/admin/dashboard', requireAdmin, async (req, res) => {
    const db = await getDB();
    const totalUsers = db.exec("SELECT COUNT(*) FROM users WHERE email != 'Romar'")[0].values[0][0];
    const premiumUsers = db.exec("SELECT COUNT(*) FROM users WHERE account_type = 'premium' AND email != 'Romar'")[0].values[0][0];
    const freeUsers = db.exec("SELECT COUNT(*) FROM users WHERE account_type = 'free' AND email != 'Romar'")[0].values[0][0];
    const totalMessages = db.exec("SELECT COUNT(*) FROM messages")[0].values[0][0];
    
    const usersResult = db.exec("SELECT * FROM users WHERE email != 'Romar' ORDER BY created_at DESC");
    let users = [];
    if (usersResult.length > 0) {
        const cols = usersResult[0].columns;
        users = usersResult[0].values.map(row => {
            const u = {};
            cols.forEach((col, i) => u[col] = row[i]);
            return u;
        });
    }
    
    res.render('admin_dashboard', { totalUsers, premiumUsers, freeUsers, totalMessages, users });
});

app.post('/admin/action', requireAdmin, async (req, res) => {
    const { action, user_id } = req.body;
    const db = await getDB();
    
    if (user_id) {
        switch (action) {
            case 'delete':
                db.run("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?", [user_id, user_id]);
                db.run("DELETE FROM users WHERE id = ? AND email != 'Romar'", [user_id]);
                break;
            case 'upgrade':
                db.run("UPDATE users SET account_type = 'premium' WHERE id = ?", [user_id]);
                break;
            case 'downgrade':
                db.run("UPDATE users SET account_type = 'free' WHERE id = ?", [user_id]);
                break;
        }
    }
    res.redirect('/admin/dashboard');
});

// Local dev server
if (process.env.NODE_ENV !== 'production') {
    const PORT = process.env.PORT || 3000;
    app.listen(PORT, () => console.log(`Server running on http://localhost:${PORT}`));
}

module.exports = app;
