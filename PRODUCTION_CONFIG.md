# Production Configuration

Before public launch, fill the following values.

## Android

Current package:
`in.myapp.portal`

Recommended final package:
Use a permanent reverse-domain ID you own, for example:
`com.yourdomain.myapp`

API configuration file:
`android/app/src/main/java/in/myapp/portal/AppConfig.kt`

Set:
`API_URL = https://<your-domain>/<folder>/api/home.php`

## Server

Copy:
`server/config.example.php`

to:
`server/config.php`

Then fill:
- DB host
- DB name
- DB username
- DB password

Upload the `server` folder to HTTPS hosting.

## Admin

First-time setup:
`https://<your-domain>/<folder>/install.php`

Then:
- create admin user
- delete or rename `install.php`

Admin login:
`https://<your-domain>/<folder>/admin/login.php`

## Firebase

Background push notifications need:
- Firebase Android app registration
- final Android package ID
- `google-services.json`
- Firebase Cloud Messaging setup

The notification center works through the app/API even before background FCM delivery is connected.
