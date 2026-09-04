# Build the My App APK

The Android project is already configured with:

- Android Gradle Plugin: 9.4.0
- Kotlin plugin: 2.3.21
- compileSdk: 36
- targetSdk: 36
- Gradle required by AGP 9.4: 9.6.0

## Option 1 — Android Studio

1. Extract the package.
2. Open the `android` folder in Android Studio.
3. Allow Gradle sync to complete.
4. Select **Build > Build APK(s)**.
5. The debug APK will be created under:
   `android/app/build/outputs/apk/debug/app-debug.apk`

The current app can run in Demo Mode when the live API is not configured.

## Option 2 — GitHub Actions (no Android Studio build required)

This source includes:

`.github/workflows/build-android-apk.yml`

Steps:

1. Create a GitHub repository.
2. Upload this project preserving the `.github` folder.
3. Open the **Actions** tab.
4. Select **Build My App APK**.
5. Choose **Run workflow**.
6. After completion, download the artifact named:
   `MyApp-debug-apk`
7. Extract it to get `app-debug.apk`.
8. Transfer the APK to an Android phone and install it.

## Live mode

Edit:

`android/app/src/main/java/in/myapp/portal/AppConfig.kt`

Change:

`https://YOUR-DOMAIN.com/myapp/api/home.php`

to the final live HTTPS API URL.

## Production release

A Play Store AAB / signed release APK needs:

- release signing keystore
- keystore alias
- signing passwords
- final package/application ID
- final live API URL
- privacy policy URL
