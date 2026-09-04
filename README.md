# My App v2 — Dynamic Multi-Link Android Portal

यह version पहले prototype का upgraded build है।

## Included

### Android App
- Dynamic categories from Admin/API
- Search across all links
- Favorites (local device)
- Featured links
- Short descriptions
- Logo images
- Share button
- In-app WebView
- External app/browser mode
- Click reporting to Admin analytics
- Dynamic app name/subtitle

### Admin Panel
- Login
- Category Add/Edit/Delete
- Link Add/Edit/Delete
- Active/Inactive
- Display order
- Featured flag
- Short description
- In-App / External mode
- Logo via URL
- Logo direct upload (PNG/JPG/WEBP, max 2 MB)
- Dashboard counters
- Click analytics
- Top links
- JSON export
- App name/subtitle/color settings

### Server
- PHP + MySQL/MariaDB
- JSON API
- Click counter API
- CSRF protection
- Password hashing
- Prepared SQL statements
- Protected config/database files via .htaccess

## Folder structure

- `android/` Android Studio project
- `server/admin/` Admin Panel
- `server/api/` Android API
- `server/uploads/` created automatically after first logo upload
- `server/database.sql` fresh installation schema
- `server/upgrade_v2.sql` upgrade from v1
- `server/demo_seed.sql` optional demo links

## Fresh installation

1. Create MySQL database + user.
2. Copy `server/config.example.php` to `server/config.php`.
3. Fill DB credentials.
4. Upload `server/` to HTTPS hosting.
5. Open:
   `https://yourdomain.com/myapp/install.php`
6. Create admin.
7. Delete/rename `install.php`.
8. Admin:
   `https://yourdomain.com/myapp/admin/login.php`
9. API:
   `https://yourdomain.com/myapp/api/home.php`

## Android configuration

Edit:

`android/app/src/main/java/in/myapp/portal/AppConfig.kt`

and replace:

```kotlin
const val API_URL = "https://YOUR-DOMAIN.com/myapp/api/home.php"
```

with your live API.

Then open the `android/` folder in Android Studio.

## Upgrade from v1

Run `server/upgrade_v2.sql` once in phpMyAdmin or MySQL client.

## Recommended next refinements after your review

- Final logo/icon
- Splash screen
- Final color/theme
- Bottom navigation if desired
- Push notifications
- Banner/advertisement slots
- User login (optional)
- Language toggle Hindi/English
- Admin roles
- Import from Excel/CSV
- Play Store ready privacy policy / terms

## Offline Demo Mode

अगर API URL अभी configure नहीं है या server उपलब्ध नहीं है, Android app demo mode में
News / Social / Education sample categories के साथ खुल जाएगा। इससे final hosting से पहले
UI/flow review किया जा सकता है। API connect होने के बाद app automatically live admin data use करेगा.


## v3 UX Decision

- Dedicated Categories screen has been removed from bottom navigation.
- Bottom navigation is intentionally reduced to:
  - Home
  - Favorites
  - Notifications
  - Profile
- Home shows only 3 main categories + More.
- "See All" / "More" opens a simple lightweight list, not a separate permanent navigation section.
- This keeps the app clean and reduces navigation complexity.
- Ads remain present as an admin-controlled slot without dominating the home screen.
- Login and notifications remain first-class core sections.


## v4 Core Features Added

### User Login
- Email/password registration and login
- Optional mobile number
- 30-day bearer token sessions
- Profile screen
- Logout
- Admin can block/activate users

### Notifications
- Admin can create or schedule notifications
- Notification Center in Android app
- Optional target URL
- This version provides in-app notification delivery.
- True background push notifications require Firebase Cloud Messaging credentials and will be connected when the final Android package/domain/project credentials are available.

### Advertisements
- Admin-controlled ad records
- Slots: Home Banner, Home Inline, Category Inline
- Start/end dates
- Target URL
- Click counter
- Android Home Inline slot loads dynamically from API

### Admin
- Ads management
- Notifications management
- Users management
- Dashboard counts for users, notifications and ads

### Clean UX remains unchanged
Bottom navigation remains:
Home | Favorites | Notifications | Profile

Categories remain on Home and do not get a dedicated bottom-nav tab.


## v4 Light UI Update
- Dark/black heavy blocks removed from preview direction.
- Header switched to a lighter sky-blue/lavender gradient.
- Hero card is now white/light pastel instead of dark.
- Icon/logo placeholders use soft light gradients.
- Overall UI kept cleaner and closer to the approved sample feel.


## v5 Approved Home Screen

The approved reference-style Home UI is now the active baseline:

- Gradient top header
- Hamburger menu + centered My App + notification bell
- Good Morning greeting
- Search box
- Dark purple/blue hero banner with rocket visual
- 6 category tiles:
  News, Social, Education, Government, Tools, More
- Featured horizontal cards
- Clean green CTA / admin-ad slot
- Bottom navigation:
  Home, Favorites, Notifications, Profile
- No dedicated Categories item in bottom navigation
- No Popular Links section on Home, keeping the screen closer to the approved reference and less cluttered


## v5.1 Copy Update
- Header subtitle changed to: **All Your Apps, One Place**


## v6 Functional Core

UI remains frozen at the approved Home baseline.

Added:
- Logged-in user favorites sync
- Server-side favorites table/API
- Notification read/unread tracking
- Mark one / mark all notification APIs
- Device registration table/API for future FCM push
- User activity logging API
- Admin user detail page with favorites and recent activity
- Ads edit / enable / disable / schedule controls
- API documentation file: `API-v6.md`

### Push notification status
The backend is now prepared to store device/push tokens.
Actual FCM background push delivery still requires the final Firebase project credentials / `google-services.json`.


## Header hierarchy update
- My App = primary heading
- All Your Apps, One Place = tagline directly below heading
- Good Morning 👋 = secondary greeting below tagline
