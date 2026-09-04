package in.myapp.portal

import android.annotation.SuppressLint
import android.app.Activity
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.webkit.*
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast

class WebViewActivity : Activity() {

    private lateinit var webView: WebView
    private lateinit var progressBar: ProgressBar
    private var currentUrl: String = ""

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_webview)

        val title = intent.getStringExtra("title") ?: "My App"
        currentUrl = intent.getStringExtra("url") ?: ""

        findViewById<TextView>(R.id.pageTitle).text = title
        findViewById<TextView>(R.id.backButton).setOnClickListener {
            if (webView.canGoBack()) webView.goBack() else finish()
        }
        findViewById<TextView>(R.id.externalButton).setOnClickListener {
            try {
                startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(currentUrl)))
            } catch (_: Exception) {
                Toast.makeText(this, "External browser नहीं खुल सका।", Toast.LENGTH_SHORT).show()
            }
        }

        progressBar = findViewById(R.id.progressBar)
        webView = findViewById(R.id.webView)

        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            loadsImagesAutomatically = true
            useWideViewPort = true
            loadWithOverviewMode = true
            setSupportZoom(true)
            builtInZoomControls = false
            mixedContentMode = WebSettings.MIXED_CONTENT_COMPATIBILITY_MODE
        }

        webView.webViewClient = object : WebViewClient() {
            override fun shouldOverrideUrlLoading(
                view: WebView?,
                request: WebResourceRequest?
            ): Boolean {
                currentUrl = request?.url?.toString() ?: currentUrl
                return false
            }
        }

        webView.webChromeClient = object : WebChromeClient() {
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                progressBar.progress = newProgress
                progressBar.visibility =
                    if (newProgress >= 100) ProgressBar.GONE else ProgressBar.VISIBLE
            }
        }

        if (currentUrl.startsWith("https://") || currentUrl.startsWith("http://")) {
            webView.loadUrl(currentUrl)
        } else {
            Toast.makeText(this, "Invalid URL", Toast.LENGTH_SHORT).show()
            finish()
        }
    }

    override fun onBackPressed() {
        if (::webView.isInitialized && webView.canGoBack()) webView.goBack()
        else super.onBackPressed()
    }
}
