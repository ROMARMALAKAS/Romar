const express = require('express');
const cookieSession = require('cookie-session');
const bcrypt = require('bcryptjs');
const path = require('path');
const { db, calculateDistance } = require('./db');

const app = express();

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, '..', 'views'));
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use('/public', express.static(path.join(__dirname, '..', 'public')));

app.set('trust proxy', 1);
app.use(cookieSession({
    name: 'lc_session',
    keys: ['loveconnect-key-1-xyz', 'loveconnect-key-2-abc'],
    maxAge: 7 * 24 * 60 * 60 * 1000,
    sameSite: 'lax',
    secure: false
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

// ============ VALIDATION API ============
app.post('/api/check-email', (req, res) => {
    const { email } = req.body;
    if (!email) return res.json({ available: true });
    const user = db.findUser({ emailLower: email.trim().toLowerCase() });
    res.json({ available: !user });
});

app.post('/api/check-name', (req, res) => {
    const { name } = req.body;
    if (!name) return res.json({ available: true });
    const user = db.findUser({ nameLower: name.trim().toLowerCase() });
    res.json({ available: !user });
});

// ============ HOME ============
app.get('/', (req, res) => {
    if (req.session.user) return res.redirect('/dashboard');
    res.render('index');
});

// ============ LOGIN ============
app.get('/login', (req, res) => {
    if (req.session.user) return res.redirect('/dashboard');
    res.render('login', { error: null });
});

app.post('/login', (req, res) => {
    const { email, password } = req.body;
    if (!email || !password) {
        return res.render('login', { error: 'Please enter email and password.' });
    }
    
    const user = db.findUser({ email: email.trim() });
    if (user && bcrypt.compareSync(password, user.password)) {
        req.session.user = { id: user.id, name: user.name, email: user.email, account_type: user.account_type };
        return res.redirect('/dashboard');
    }
    res.render('login', { error: 'Invalid email or password.' });
});

// ============ REGISTER ============
app.get('/register', (req, res) => {
    if (req.session.user) return res.redirect('/dashboard');
    res.render('register', { errors: {}, values: {}, success: null });
});

app.post('/register', (req, res) => {
    const { name, email, password, confirm_password, gender, age, bio, latitude, longitude } = req.body;
    const errors = {};
    const values = { name, email, gender, age, bio };
    
    if (!name || name.trim().length < 2) errors.name = 'Name must be at least 2 characters.';
    if (!email) errors.email = 'Email is required.';
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.email = 'Please enter a valid email address.';
    if (!password) errors.password = 'Password is required.';
    else if (password.length < 6) errors.password = 'Password must be at least 6 characters.';
    if (password && password !== confirm_password) errors.confirm_password = 'Passwords do not match.';
    if (!gender) errors.gender = 'Please select your gender.';
    if (!age) errors.age = 'Age is required.';
    else if (parseInt(age) < 18) errors.age = 'You must be at least 18 years old.';
    else if (parseInt(age) > 100) errors.age = 'Please enter a valid age.';
    
    if (!errors.email) {
        const existing = db.findUser({ emailLower: email.trim().toLowerCase() });
        if (existing) errors.email = 'This email is already registered. Try logging in.';
    }
    if (!errors.name) {
        const existing = db.findUser({ nameLower: name.trim().toLowerCase() });
        if (existing) errors.name = 'This username is already taken. Try another one.';
    }
    
    if (Object.keys(errors).length > 0) {
        return res.render('register', { errors, values, success: null });
    }
    
    const hashedPw = bcrypt.hashSync(password, 4);
    db.createUser({
        name: name.trim(),
        email: email.trim().toLowerCase(),
        password: hashedPw,
        gender,
        age: parseInt(age),
        bio: bio || '',
        latitude: parseFloat(latitude) || null,
        longitude: parseFloat(longitude) || null
    });
    
    res.render('register', { errors: {}, values: {}, success: 'Account created successfully! You can now login.' });
});

// ============ LOGOUT ============
app.get('/logout', (req, res) => {
    req.session = null;
    res.redirect('/login');
});

// ============ DASHBOARD ============
app.get('/dashboard', requireLogin, (req, res) => {
    const currentUser = db.findUser({ id: req.session.user.id });
    const allUsers = db.getAllUsers(req.session.user.id);
    const users = allUsers.map(u => ({
        ...u,
        distance: calculateDistance(currentUser ? currentUser.latitude : null, currentUser ? currentUser.longitude : null, u.latitude, u.longitude)
    }));
    res.render('dashboard', { currentUser: currentUser || req.session.user, users });
});

// ============ PROFILE ============
app.get('/profile', requireLogin, (req, res) => {
    const user = db.findUser({ id: req.session.user.id });
    res.render('profile', { user: user || req.session.user, error: null, success: null });
});

app.post('/profile', requireLogin, (req, res) => {
    const { name, age, gender, bio } = req.body;
    db.updateUser(req.session.user.id, { name, age: parseInt(age), gender, bio: bio || '' });
    req.session.user.name = name;
    const user = db.findUser({ id: req.session.user.id });
    res.render('profile', { user: user || req.session.user, error: null, success: 'Profile updated!' });
});

// ============ CHAT ============
app.get('/chat', requireLogin, (req, res) => {
    const userId = req.session.user.id;
    const selectedUserId = parseInt(req.query.user) || 0;
    
    let convIds = db.getConversationPartners(userId);
    if (selectedUserId && !convIds.includes(selectedUserId)) convIds.push(selectedUserId);
    
    const conversations = convIds.map(id => {
        const u = db.findUser({ id });
        if (!u || u.email === 'Romar') return null;
        const lastMsg = db.getLastMessage(userId, id);
        return { ...u, last_message: lastMsg ? lastMsg.message : '' };
    }).filter(Boolean);
    
    let selectedUser = null;
    let messages = [];
    if (selectedUserId) {
        selectedUser = db.findUser({ id: selectedUserId });
        messages = db.getMessages(userId, selectedUserId);
    }
    
    const currentUser = req.session.user;
    const showUpgradeModal = req.query.error === 'upgrade';
    res.render('chat', { conversations, selectedUser, messages, currentUser, selectedUserId, showUpgradeModal });
});

app.post('/chat', requireLogin, (req, res) => {
    const { message, receiver_id } = req.body;
    if (message && message.trim()) {
        db.sendMessage(req.session.user.id, parseInt(receiver_id), message.trim());
    }
    res.redirect('/chat?user=' + receiver_id);
});

// ============ UPGRADE ============
app.get('/upgrade', requireLogin, (req, res) => {
    res.render('upgrade', { currentUser: req.session.user });
});

// ============ UPDATE LOCATION ============
app.post('/update-location', requireLogin, (req, res) => {
    const { latitude, longitude } = req.body;
    if (latitude && longitude) {
        db.updateUser(req.session.user.id, { latitude: parseFloat(latitude), longitude: parseFloat(longitude) });
    }
    res.json({ success: true });
});

// ============ ADMIN LOGIN ============
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

// ============ ADMIN DASHBOARD ============
app.get('/admin/dashboard', requireAdmin, (req, res) => {
    const counts = db.countUsers();
    const users = db.getAllUsersAdmin();
    res.render('admin_dashboard', {
        totalUsers: counts.total,
        premiumUsers: counts.premium,
        freeUsers: counts.free,
        totalMessages: db.countMessages(),
        users
    });
});

app.post('/admin/action', requireAdmin, (req, res) => {
    const { action, user_id } = req.body;
    const id = parseInt(user_id);
    if (id) {
        switch (action) {
            case 'delete': db.deleteUser(id); break;
            case 'upgrade': db.upgradeUser(id); break;
            case 'downgrade': db.downgradeUser(id); break;
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
