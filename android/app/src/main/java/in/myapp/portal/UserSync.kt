package in.myapp.portal

import android.content.Context
import android.provider.Settings
import org.json.JSONObject
import kotlin.concurrent.thread

object UserSync {
    private fun prefs(context: Context) = context.getSharedPreferences("myapp_prefs", Context.MODE_PRIVATE)
    private fun token(context: Context) = prefs(context).getString("auth_token", "") ?: ""

    fun syncFavorite(context: Context, linkId: Int, add: Boolean) {
        val t = token(context); if (t.isBlank()) return
        thread {
            try {
                val payload = JSONObject().apply {
                    put("link_id", linkId)
                    put("action", if (add) "add" else "remove")
                }
                ApiUtil.postJson(ApiUtil.endpoint("favorites.php"), payload, t)
            } catch (_: Exception) {}
        }
    }

    fun pullFavorites(context: Context, onDone: (() -> Unit)? = null) {
        val t = token(context)
        if (t.isBlank()) { onDone?.invoke(); return }
        thread {
            try {
                val json = JSONObject(ApiUtil.get(ApiUtil.endpoint("favorites.php"), t))
                val arr = json.optJSONArray("favorites")
                val ids = mutableSetOf<String>()
                if (arr != null) for (i in 0 until arr.length()) ids.add(arr.getJSONObject(i).getInt("id").toString())
                prefs(context).edit().putStringSet("favorite_ids", ids).apply()
            } catch (_: Exception) {}
            onDone?.invoke()
        }
    }

    fun registerDevice(context: Context) {
        thread {
            try {
                val deviceId = Settings.Secure.getString(context.contentResolver, Settings.Secure.ANDROID_ID) ?: return@thread
                val pushToken = prefs(context).getString("push_token", "") ?: ""
                val payload = JSONObject().apply {
                    put("device_id", deviceId)
                    put("push_token", pushToken)
                }
                val t = token(context)
                ApiUtil.postJson(ApiUtil.endpoint("device_register.php"), payload, if (t.isBlank()) null else t)
            } catch (_: Exception) {}
        }
    }

    fun logActivity(context: Context, eventType: String, referenceId: Int? = null) {
        thread {
            try {
                val payload = JSONObject().apply {
                    put("event_type", eventType)
                    if (referenceId != null) put("reference_id", referenceId)
                }
                val t = token(context)
                ApiUtil.postJson(ApiUtil.endpoint("activity.php"), payload, if (t.isBlank()) null else t)
            } catch (_: Exception) {}
        }
    }
}
