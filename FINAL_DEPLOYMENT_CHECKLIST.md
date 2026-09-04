# My App — Final Deployment Checklist

## 1. Hosting
Use HTTPS hosting with PHP 8.1+ and MySQL/MariaDB.

1. Create a database and database user.
2. Copy `server/config.example.php` to `server/config.php`.
3. Enter database credentials.
4. Upload `server/` to your hosting/subdomain.
5. Open `/install.php` once and create the first admin.
6. Delete or rename `install.php` after successful installation.
7. Confirm `/api/health.php` returns `success:true`.

## 2. Android API URL
Edit:
`android/app/src/main/java/in/myapp/portal/AppConfig.kt`

Replace `https://YOUR-DOMAIN.com/myapp/api/home.php` with the live API URL.

## 3. Branding before release
Replace:
- package/application id `in.myapp.portal` if desired
- app icon
- app display name if different from My App
- final privacy policy and terms text/URLs
- support email

## 4. Firebase push notifications
Backend device-token storage is ready, but actual background push requires your Firebase project.
Add your final `google-services.json` and FCM server/service credentials during deployment.
Do not commit private Firebase server credentials into a public source package.

## 5. Build
Open `android/` in Android Studio, sync Gradle, then build a signed APK/AAB.

## 6. Admin URLs
- `/admin/login.php`
- Categories
- Links
- Import CSV
- Ads
- Notifications
- Users
- Settings
- Password
- Export JSON

## 7. Security
- HTTPS only
- strong admin password
- remove install.php after setup
- keep config.php private
- backup database regularly
- use a unique production package name
