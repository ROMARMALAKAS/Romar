# LoveConnect - Dating Web App

A complete responsive dating web app built with Node.js, Express, and Bootstrap 5.

**Live Demo:** https://dating-app-vercel-eight.vercel.app

## Features

- **Login & Register** - Secure auth with bcrypt password hashing and sessions
- **User Dashboard** - Browse profiles with distance calculation (Haversine formula)
- **Chat System** - Real-time messaging with conversation history
- **Photo Lock Feature** - Premium-only photo sending with upgrade modal
- **Account Types** - Free and Premium tiers
- **Admin Panel** - Manage users, upgrade/downgrade, view stats

## Tech Stack

- **Backend:** Node.js + Express
- **Database:** sql.js (in-memory SQLite)
- **Frontend:** Bootstrap 5, EJS templates, Poppins font
- **Hosting:** Vercel (serverless)

## Demo Accounts

| Account | Email | Password |
|---------|-------|----------|
| Admin | Romar | Romar |
| Demo User | sofia@demo.com | password123 |
| Demo User | james@demo.com | password123 |
| Demo User | mia@demo.com | password123 |

## Pages

- `/` - Landing page
- `/login` - User login
- `/register` - User registration
- `/dashboard` - Browse profiles
- `/profile` - Edit profile
- `/chat` - Message system
- `/upgrade` - Premium plans
- `/admin/login` - Admin login
- `/admin/dashboard` - Admin panel

## Run Locally

```bash
npm install
npm run dev
# Visit http://localhost:3000
```

## Deploy to Vercel

```bash
npm i -g vercel
vercel --prod
```

## Database

Uses sql.js (pure JavaScript SQLite). Data is stored in-memory and includes demo users on startup.

### Tables

**users:** id, name, email, password, gender, age, bio, profile_photo, latitude, longitude, account_type, created_at

**messages:** id, sender_id, receiver_id, message, photo, created_at

## Security

- Bcrypt password hashing
- Session-based authentication
- SQL prepared statements (parameterized queries)
- Admin route protection
- Input sanitization

## License

MIT
