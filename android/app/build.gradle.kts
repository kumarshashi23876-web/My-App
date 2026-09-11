plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

android {
    namespace = "in.myapp.portal"
    compileSdk = 35

    defaultConfig {
        applicationId = "in.myapp.portal"
        minSdk = 23
        targetSdk = 35
        versionCode = 1
        versionName = "1.0.0"
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = "17"
    }
}

/*
RELEASE SIGNING TEMPLATE
For a signed Play Store build, configure a signing key in Android Studio
or use CI secrets. Do not commit real passwords/keystore secrets to source control.
*/