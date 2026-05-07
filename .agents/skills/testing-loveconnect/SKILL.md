---
name: testing-loveconnect
description: Test the LoveConnect dating app end-to-end on its live Vercel deployment. Use when verifying UI changes, auth flows, or admin panel functionality.
---

# Testing LoveConnect Dating App

## Live Environment

- **URL:** https://dating-app-eight-gamma.vercel.app
- **Platform:** Vercel serverless (Node.js/Express)
- **Database:** In-memory (resets on cold start) — demo users are re-seeded automatically

## Credentials

- **User:** `sofia@demo.com` / `password123`
- **Other users:** `james@demo.com`, `mia@demo.com`, `daniel@demo.com`, `isabella@demo.com` (all `password123`)
- **Admin:** Email `Romar` / Password `Romar` at `/admin/login`

## Devin Secrets Needed

- `VERCEL_TOKEN` — for deploying updates via `npx vercel deploy --token=$VERCEL_TOKEN --yes --prod`

## Key Pages to Test

| Page | URL | What to verify |
|------|-----|----------------|
| Login | `/login` | Email/password form, error banner on bad creds |
| Register | `/register` | Real-time validation (duplicate email/username shows red indicator) |
| Dashboard | `/dashboard` | Profile list, bottom nav, distance display |
| Chat | `/chat` | Message sidebar, conversation area |
| Upgrade | `/upgrade` | Plan cards, confirmation modal on "Upgrade Now" click |
| Profile | `/profile` | Account form, save button |
| Admin | `/admin/dashboard` | Stats grid, user list, confirmation modals on upgrade/downgrade/delete |

## Testing Flow

1. Navigate to `/login`, login as `sofia@demo.com`
2. Verify dashboard has bottom nav (Home, Upgrade, Profile, Chats, Account)
3. Navigate via bottom nav to `/upgrade`
4. Click "Upgrade Now" → verify confirmation modal appears
5. Click Cancel → verify modal dismisses
6. Logout, login as admin at `/admin/login`
7. Click delete button on any user → verify confirmation modal with user name
8. Click Cancel → verify user preserved

## Known Issues & Workarounds

- **Vercel edge cache:** After redeployment, the browser might show stale cached content. Use a cache-busting query param (e.g., `?_v=new`) or hard refresh to force the new version.
- **In-memory DB resets:** If the serverless function cold starts, all data resets to demo seed data. Don't rely on data persistence between test runs.
- **Session cookies:** Uses `cookie-session` with signed cookies. Session survives across page loads but not across deployments with different secrets.

## Deployment

To deploy latest code:
```bash
cd /home/ubuntu/dating-app
npm install
npx vercel deploy --token=$VERCEL_TOKEN --yes --prod
```

The Vercel project is configured in `.vercel/project.json`. All routes go through `api/index.js` per `vercel.json`.
