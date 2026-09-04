package in.myapp.portal

import android.app.Activity
import android.os.Bundle
import android.view.View
import android.widget.*
import org.json.JSONObject
import kotlin.concurrent.thread

class LoginActivity : Activity() {
    private var registerMode = false
    private lateinit var nameInput: EditText
    private lateinit var mobileInput: EditText
    private lateinit var emailInput: EditText
    private lateinit var passwordInput: EditText
    private lateinit var heading: TextView
    private lateinit var submit: Button
    private lateinit var switchMode: TextView
    private lateinit var status: TextView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)
        nameInput=findViewById(R.id.nameInput); mobileInput=findViewById(R.id.mobileInput)
        emailInput=findViewById(R.id.emailInput); passwordInput=findViewById(R.id.passwordInput)
        heading=findViewById(R.id.loginHeading); submit=findViewById(R.id.submitButton)
        switchMode=findViewById(R.id.switchMode); status=findViewById(R.id.loginStatus)
        switchMode.setOnClickListener { registerMode=!registerMode; refreshMode() }
        submit.setOnClickListener { submitForm() }
        refreshMode()
    }

    private fun refreshMode() {
        nameInput.visibility=if(registerMode) View.VISIBLE else View.GONE
        mobileInput.visibility=if(registerMode) View.VISIBLE else View.GONE
        heading.text=if(registerMode) "Create Account" else "Login"
        submit.text=if(registerMode) "Create Account" else "Login"
        switchMode.text=if(registerMode) "Already have an account? Login" else "New user? Create account"
        status.text=""
    }

    private fun submitForm() {
        status.text="Please wait..."; submit.isEnabled=false
        val payload=JSONObject().apply {
            put("email",emailInput.text.toString().trim())
            put("password",passwordInput.text.toString())
            if(registerMode){ put("name",nameInput.text.toString().trim()); put("mobile",mobileInput.text.toString().trim()) }
        }
        val file=if(registerMode) "register.php" else "login.php"
        thread {
            try {
                val json=JSONObject(ApiUtil.postJson(ApiUtil.endpoint(file),payload))
                if(!json.optBoolean("success")) throw Exception(json.optString("message","Login failed"))
                val token=json.getString("token"); val user=json.getJSONObject("user")
                getSharedPreferences("myapp_prefs",MODE_PRIVATE).edit()
                    .putString("auth_token",token).putString("user_name",user.optString("name","User"))
                    .putString("user_email",user.optString("email","")).apply()
                UserSync.registerDevice(this)
                UserSync.logActivity(this,"login")
                UserSync.pullFavorites(this)
                runOnUiThread { Toast.makeText(this,"Welcome ${user.optString("name","")}",Toast.LENGTH_SHORT).show(); setResult(RESULT_OK); finish() }
            } catch(e:Exception) {
                runOnUiThread { status.text=e.message ?: "Could not login"; submit.isEnabled=true }
            }
        }
    }
}
