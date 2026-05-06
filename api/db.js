const bcrypt = require('bcryptjs');

// Pure JS in-memory database (no WASM dependencies)
let users = [];
let messages = [];
let nextUserId = 1;
let nextMsgId = 1;
let initialized = false;

function initDB() {
    if (initialized) return;
    initialized = true;
    
    const adminPw = bcrypt.hashSync('Romar', 4);
    const demoPw = bcrypt.hashSync('password123', 4);
    
    users = [
        { id: nextUserId++, name: 'Admin', email: 'Romar', password: adminPw, gender: 'other', age: 0, bio: 'System Administrator', profile_photo: 'default.png', latitude: null, longitude: null, account_type: 'premium', created_at: new Date().toISOString() },
        { id: nextUserId++, name: 'Sofia Martinez', email: 'sofia@demo.com', password: demoPw, gender: 'female', age: 23, bio: 'Love hiking and coffee! Looking for someone adventurous.', profile_photo: 'default.png', latitude: 14.5995, longitude: 120.9842, account_type: 'free', created_at: new Date().toISOString() },
        { id: nextUserId++, name: 'James Rivera', email: 'james@demo.com', password: demoPw, gender: 'male', age: 25, bio: "Musician and foodie. Let's explore the city together!", profile_photo: 'default.png', latitude: 14.5547, longitude: 121.0244, account_type: 'premium', created_at: new Date().toISOString() },
        { id: nextUserId++, name: 'Mia Santos', email: 'mia@demo.com', password: demoPw, gender: 'female', age: 22, bio: 'Beach lover and bookworm. Swipe right if you love sunsets!', profile_photo: 'default.png', latitude: 14.6091, longitude: 120.9893, account_type: 'free', created_at: new Date().toISOString() },
        { id: nextUserId++, name: 'Daniel Cruz', email: 'daniel@demo.com', password: demoPw, gender: 'male', age: 27, bio: 'Fitness enthusiast and dog dad. Looking for my gym partner.', profile_photo: 'default.png', latitude: 14.5764, longitude: 121.0013, account_type: 'free', created_at: new Date().toISOString() },
        { id: nextUserId++, name: 'Isabella Reyes', email: 'isabella@demo.com', password: demoPw, gender: 'female', age: 24, bio: 'Nurse by day, artist by night. Looking for genuine connections.', profile_photo: 'default.png', latitude: 14.5896, longitude: 121.0561, account_type: 'premium', created_at: new Date().toISOString() },
    ];
    
    messages = [];
}

// Database query interface
const db = {
    findUser(query) {
        initDB();
        if (query.id) return users.find(u => u.id === query.id) || null;
        if (query.email) return users.find(u => u.email === query.email) || null;
        if (query.emailLower) return users.find(u => u.email.toLowerCase() === query.emailLower) || null;
        if (query.nameLower) return users.find(u => u.name.toLowerCase() === query.nameLower && u.email !== 'Romar') || null;
        return null;
    },
    getAllUsers(excludeId) {
        initDB();
        return users.filter(u => u.id !== excludeId && u.email !== 'Romar');
    },
    getAllUsersAdmin() {
        initDB();
        return users.filter(u => u.email !== 'Romar');
    },
    createUser(data) {
        initDB();
        const user = { id: nextUserId++, ...data, profile_photo: 'default.png', account_type: 'free', created_at: new Date().toISOString() };
        users.push(user);
        return user;
    },
    updateUser(id, data) {
        initDB();
        const user = users.find(u => u.id === id);
        if (user) Object.assign(user, data);
        return user;
    },
    deleteUser(id) {
        initDB();
        users = users.filter(u => u.id !== id || u.email === 'Romar');
        messages = messages.filter(m => m.sender_id !== id && m.receiver_id !== id);
    },
    upgradeUser(id) {
        initDB();
        const user = users.find(u => u.id === id);
        if (user) user.account_type = 'premium';
    },
    downgradeUser(id) {
        initDB();
        const user = users.find(u => u.id === id);
        if (user) user.account_type = 'free';
    },
    getMessages(userId1, userId2) {
        initDB();
        return messages.filter(m => 
            (m.sender_id === userId1 && m.receiver_id === userId2) ||
            (m.sender_id === userId2 && m.receiver_id === userId1)
        ).sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    },
    getConversationPartners(userId) {
        initDB();
        const partners = new Set();
        messages.forEach(m => {
            if (m.sender_id === userId) partners.add(m.receiver_id);
            if (m.receiver_id === userId) partners.add(m.sender_id);
        });
        return [...partners];
    },
    getLastMessage(userId, otherId) {
        initDB();
        const convMsgs = messages.filter(m => 
            (m.sender_id === userId && m.receiver_id === otherId) ||
            (m.sender_id === otherId && m.receiver_id === userId)
        );
        return convMsgs.length > 0 ? convMsgs[convMsgs.length - 1] : null;
    },
    sendMessage(senderId, receiverId, message) {
        initDB();
        const msg = { id: nextMsgId++, sender_id: senderId, receiver_id: receiverId, message, photo: null, created_at: new Date().toISOString() };
        messages.push(msg);
        return msg;
    },
    countUsers() {
        initDB();
        const allUsers = users.filter(u => u.email !== 'Romar');
        return {
            total: allUsers.length,
            premium: allUsers.filter(u => u.account_type === 'premium').length,
            free: allUsers.filter(u => u.account_type === 'free').length,
        };
    },
    countMessages() {
        initDB();
        return messages.length;
    }
};

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

module.exports = { db, calculateDistance };
