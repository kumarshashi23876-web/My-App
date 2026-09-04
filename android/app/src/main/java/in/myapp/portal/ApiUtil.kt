package in.myapp.portal

import org.json.JSONObject
import java.io.BufferedReader
import java.io.InputStreamReader
import java.net.HttpURLConnection
import java.net.URL

object ApiUtil {
    fun endpoint(file: String): String = AppConfig.API_URL.substringBeforeLast("/") + "/" + file

    private fun read(c: HttpURLConnection): String {
        val code = c.responseCode
        val stream = if (code in 200..299) c.inputStream else c.errorStream
        val text = if (stream != null) BufferedReader(InputStreamReader(stream)).use { it.readText() } else ""
        if (code !in 200..299) {
            val msg = try { JSONObject(text).optString("message", "Request failed ($code)") }
                      catch (_: Exception) { "Request failed ($code)" }
            throw Exception(msg)
        }
        return text
    }

    fun get(url: String, token: String? = null): String {
        val c = (URL(url).openConnection() as HttpURLConnection).apply {
            connectTimeout = 9000
            readTimeout = 9000
            requestMethod = "GET"
            setRequestProperty("Accept", "application/json")
            if (!token.isNullOrBlank()) setRequestProperty("Authorization", "Bearer $token")
        }
        return read(c)
    }

    fun postJson(url: String, json: JSONObject, token: String? = null): String {
        val c = (URL(url).openConnection() as HttpURLConnection).apply {
            connectTimeout = 9000
            readTimeout = 9000
            requestMethod = "POST"
            doOutput = true
            setRequestProperty("Content-Type", "application/json; charset=utf-8")
            setRequestProperty("Accept", "application/json")
            if (!token.isNullOrBlank()) setRequestProperty("Authorization", "Bearer $token")
        }
        c.outputStream.use { it.write(json.toString().toByteArray(Charsets.UTF_8)) }
        return read(c)
    }
}
