# MY APP — FINAL BETA PACKAGE

A dynamic Android portal where the administrator controls categories, websites, social profiles, ads, notifications, and users without rebuilding the app for normal content changes.

## Android features
- Approved Home UI baseline
- Dynamic categories and links
- Search
- Featured links
- Favorites
- Logged-in favorites sync
- In-app WebView / external browser-app mode
- User registration/login/logout
- Notification center with unread state
- Notification badge
- Ads loaded from admin/API
- Ad/link click analytics
- Device registration backend hook
- Offline demo mode when API is not configured

## Admin features
- Secure admin login
- Dashboard counters and top links
- Category CRUD
- Link CRUD
- Featured / active / display order / open mode
- Logo URL + upload
- CSV import
- JSON export/backup
- Ads add/edit/enable/disable/schedule/click counts
- Notifications add/edit/enable/disable/schedule
- User management/blocking
- User detail + favorites + recent activity
- App settings
- Admin password change

## Server/API
- PHP + MySQL/MariaDB
- Prepared statements
- CSRF on admin writes
- password hashing
- bearer-token user sessions
- favorites sync
- notification read/unread
- device token storage
- user activity tracking
- health endpoint

## Environment-specific items still required before public release
These cannot be safely pre-filled without your deployment details:
1. live HTTPS domain/database credentials
2. final app icon/logo/package name (if changing)
3. Firebase project credentials for background push
4. final legal Privacy Policy / Terms content
5. signed release keystore for Play Store APK/AAB

See `FINAL_DEPLOYMENT_CHECKLIST.md`.
