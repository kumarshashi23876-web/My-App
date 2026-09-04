# Firebase Push — Final Environment Step

The project includes:
- `device_tokens` database table
- `/api/device_register.php`
- Android device registration hook
- Notifications center and read/unread state

Actual background push cannot be hard-coded generically because it requires the final Firebase project credentials tied to the production Android package.

At deployment time:
1. Create/select Firebase project.
2. Register the final Android package name.
3. Download `google-services.json` into `android/app/`.
4. Add Firebase Messaging dependency/plugin in Android Studio.
5. Store FCM server/service credentials securely on the server (never in the public APK/source).
6. Send push messages for active notification records.

The in-app notification center works independently of FCM.
