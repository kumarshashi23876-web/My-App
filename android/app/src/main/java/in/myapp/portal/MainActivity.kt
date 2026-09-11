package com.myapp.portal

import android.app.Activity
import android.content.Intent
import android.graphics.BitmapFactory
import android.graphics.Color
import android.graphics.Typeface
import android.net.Uri
import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.view.Gravity
import android.view.View
import android.widget.*
import org.json.JSONObject
import java.net.URL
import kotlin.concurrent.thread

class MainActivity : Activity() {
    private lateinit var appTitle: TextView
    private lateinit var appSubtitle: TextView
    private lateinit var searchBox: EditText
    private lateinit var categoryGrid: GridLayout
    private lateinit var featuredContainer: LinearLayout
    private lateinit var linkContainer: LinearLayout
    private lateinit var sectionTitle: TextView
    private lateinit var statusText: TextView
    private lateinit var navHome: TextView
    private lateinit var navFavorites: TextView
    private lateinit var navNotifications: TextView
    private lateinit var navProfile: TextView
    private lateinit var notificationDot: TextView

    private val categories = mutableListOf<CategoryItem>()
    private var currentMode = "home"
    private val prefs by lazy { getSharedPreferences("myapp_prefs", MODE_PRIVATE) }
    private fun dp(v:Int)=(v*resources.displayMetrics.density).toInt()
    private fun token()=prefs.getString("auth_token","") ?: ""

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState); setContentView(R.layout.activity_main)
        appTitle=findViewById(R.id.appTitle); appSubtitle=findViewById(R.id.appSubtitle)
        searchBox=findViewById(R.id.searchBox); categoryGrid=findViewById(R.id.categoryGrid)
        featuredContainer=findViewById(R.id.featuredContainer); linkContainer=findViewById(R.id.linkContainer)
        sectionTitle=findViewById(R.id.sectionTitle); statusText=findViewById(R.id.statusText)
        navHome=findViewById(R.id.navHome); navFavorites=findViewById(R.id.navFavorites)
        navNotifications=findViewById(R.id.navNotifications); navProfile=findViewById(R.id.navProfile)
        notificationDot=findViewById(R.id.notificationDot)

        navHome.setOnClickListener{showMode("home")}; navFavorites.setOnClickListener{showMode("favorites")}
        navNotifications.setOnClickListener{loadNotifications()}; navProfile.setOnClickListener{showProfile()}
        findViewById<TextView>(R.id.headerBell).setOnClickListener{loadNotifications()}
        findViewById<TextView>(R.id.menuButton).setOnClickListener{renderAllCategories()}
        findViewById<TextView>(R.id.seeAllCategories).setOnClickListener{renderAllCategories()}
        findViewById<TextView>(R.id.seeAllFeatured).setOnClickListener{showMode("featured")}

        searchBox.addTextChangedListener(object:TextWatcher{
            override fun beforeTextChanged(s:CharSequence?,start:Int,count:Int,after:Int){}
            override fun afterTextChanged(s:Editable?){}
            override fun onTextChanged(s:CharSequence?,start:Int,before:Int,count:Int){
                if(!s.isNullOrBlank()) showMode("search") else if(currentMode=="search") showMode("home")
            }
        })

        UserSync.registerDevice(this); UserSync.logActivity(this,"app_open")
        loadData(); refreshNotificationBadge()
    }

    private fun favoriteIds():MutableSet<String> = prefs.getStringSet("favorite_ids",emptySet())?.toMutableSet() ?: mutableSetOf()
    private fun isFavorite(id:Int)=favoriteIds().contains(id.toString())
    private fun toggleFavorite(id:Int){
        val ids=favoriteIds(); val k=id.toString(); val adding=!ids.contains(k)
        if(adding) ids.add(k) else ids.remove(k)
        prefs.edit().putStringSet("favorite_ids",ids).apply()
        UserSync.syncFavorite(this,id,adding); UserSync.logActivity(this,if(adding)"favorite_add" else "favorite_remove",id)
        renderCurrent()
    }
    private fun allLinks():List<LinkItem> = categories.flatMap{it.links}

    private fun loadData(){
        statusText.visibility=View.VISIBLE; statusText.text="Loading..."
        thread{
            try{
                val json=JSONObject(ApiUtil.get(AppConfig.API_URL))
                if(!json.optBoolean("success",false)) throw Exception("API")
                val app=json.optJSONObject("app"); val parsed=mutableListOf<CategoryItem>(); val cats=json.getJSONArray("categories")
                for(i in 0 until cats.length()){
                    val c=cats.getJSONObject(i); val links=mutableListOf<LinkItem>(); val la=c.getJSONArray("links")
                    for(j in 0 until la.length()){
                        val l=la.getJSONObject(j); links+=LinkItem(l.getInt("id"),l.getString("title"),l.optString("description",""),l.getString("url"),l.optString("logo_url",""),l.optString("open_mode","in_app"),l.optBoolean("is_featured",false))
                    }
                    parsed+=CategoryItem(c.getInt("id"),c.getString("name"),c.optString("icon","•"),links)
                }
                runOnUiThread{
                    appTitle.text=app?.optString("name","My App") ?: "My App"
                    appSubtitle.text=app?.optString("subtitle","All Your Apps, One Place") ?: "All Your Apps, One Place"
                    categories.clear(); categories.addAll(parsed); statusText.visibility=View.GONE; renderCurrent()
                    if(token().isNotBlank()) UserSync.pullFavorites(this){ runOnUiThread{ if(currentMode=="favorites") renderCurrent() } }
                }
            }catch(_:Exception){ runOnUiThread{loadDemoData()} }
        }
    }

    private fun loadDemoData(){
        categories.clear(); categories.addAll(listOf(
            CategoryItem(1,"News","📰", mutableListOf(LinkItem(101,"Aaj Tak","Hindi News","https://www.aajtak.in/","","in_app",true),LinkItem(102,"Zee News","News","https://zeenews.india.com/","","in_app",false))),
            CategoryItem(2,"Social","👥", mutableListOf(LinkItem(201,"Facebook","Profile / Page","https://www.facebook.com/","","external",true),LinkItem(202,"Instagram","Profile / Reel / Page","https://www.instagram.com/","","external",false))),
            CategoryItem(3,"Education","🎓", mutableListOf(LinkItem(301,"UGC","Official portal","https://www.ugc.gov.in/","","in_app",true))),
            CategoryItem(4,"Government","🏛", mutableListOf(LinkItem(401,"India Portal","Government services","https://www.india.gov.in/","","in_app",true))),
            CategoryItem(5,"Tools","🛠", mutableListOf(LinkItem(501,"Google","Search & tools","https://www.google.com/","","external",false)))
        )); statusText.visibility=View.GONE; renderCurrent()
    }

    private fun showMode(mode:String){currentMode=mode; renderCurrent()}
    private fun renderCurrent(){
        when(currentMode){
            "favorites"->renderListPage("Favorites",allLinks().filter{isFavorite(it.id)})
            "featured"->renderListPage("Featured",allLinks().filter{it.isFeatured})
            "search"->{val q=searchBox.text.toString().trim().lowercase(); renderListPage("Search Results",allLinks().filter{it.title.lowercase().contains(q)||it.description.lowercase().contains(q)})}
            else->renderHome()
        }; updateNav()
    }

    private fun renderHome(){showHomeBlocks(true); buildCategoryGrid(); buildFeatured(); sectionTitle.visibility=View.GONE; linkContainer.removeAllViews(); loadHomeAd()}
    private fun showHomeBlocks(show:Boolean){
        val v=if(show)View.VISIBLE else View.GONE; if(show)sectionTitle.visibility=View.GONE
        listOf(R.id.heroCard,R.id.categoryGrid,R.id.featuredHeader,R.id.featuredScroll,R.id.adContainer).forEach{findViewById<View>(it).visibility=v}
    }

    private fun buildCategoryGrid(){
        categoryGrid.removeAllViews()
        val defaults=listOf("News" to R.drawable.category_news_bg,"Social" to R.drawable.category_social_bg,"Education" to R.drawable.category_education_bg,"Government" to R.drawable.category_government_bg,"Tools" to R.drawable.category_tools_bg)
        defaults.forEachIndexed{i,p->
            val c=categories.firstOrNull{it.name.equals(p.first,true)} ?: CategoryItem(-(10+i),p.first,when(p.first){"News"->"📰";"Social"->"👥";"Education"->"🎓";"Government"->"🏛";else->"🛠"}, mutableListOf())
            categoryGrid.addView(makeCategoryCard(c,p.second),categoryGridLp(i))
        }
        categoryGrid.addView(makeCategoryCard(CategoryItem(-99,"More","•••", mutableListOf()),R.drawable.category_more_bg){renderAllCategories()},categoryGridLp(5))
    }
    private fun categoryGridLp(i:Int)=GridLayout.LayoutParams().apply{width=0;height=dp(104);columnSpec=GridLayout.spec(i%3,1f);rowSpec=GridLayout.spec(i/3);setMargins(dp(4),dp(4),dp(4),dp(4))}
    private fun makeCategoryCard(c:CategoryItem,bg:Int,custom:(()->Unit)?=null)=LinearLayout(this).apply{
        orientation=LinearLayout.VERTICAL; gravity=Gravity.CENTER; setPadding(dp(5),dp(10),dp(5),dp(10)); setBackgroundResource(bg)
        addView(TextView(this@MainActivity).apply{text=c.icon;textSize=25f;gravity=Gravity.CENTER})
        addView(TextView(this@MainActivity).apply{text=c.name;textSize=12f;setTextColor(Color.WHITE);setTypeface(typeface,Typeface.BOLD);gravity=Gravity.CENTER;setPadding(0,dp(5),0,0)})
        setOnClickListener{custom?.invoke() ?: renderCategoryLinks(c)}
    }

    private fun renderAllCategories(){
        currentMode="category_list"; showHomeBlocks(false); sectionTitle.visibility=View.VISIBLE;sectionTitle.text="All Categories";linkContainer.removeAllViews()
        categories.forEach{c->
            val row=LinearLayout(this).apply{orientation=LinearLayout.HORIZONTAL;gravity=Gravity.CENTER_VERTICAL;setPadding(dp(14),dp(14),dp(14),dp(14));setBackgroundResource(R.drawable.card_bg);setOnClickListener{renderCategoryLinks(c)}}
            row.addView(TextView(this).apply{text=c.icon;textSize=25f;gravity=Gravity.CENTER},LinearLayout.LayoutParams(dp(48),dp(48)))
            val mid=LinearLayout(this).apply{orientation=LinearLayout.VERTICAL;setPadding(dp(12),0,0,0)}
            mid.addView(TextView(this).apply{text=c.name;textSize=17f;setTypeface(typeface,Typeface.BOLD);setTextColor(Color.parseColor("#0F172A"))})
            mid.addView(TextView(this).apply{text="${c.links.size} links";textSize=12f;setTextColor(Color.parseColor("#64748B"))})
            row.addView(mid,LinearLayout.LayoutParams(0,LinearLayout.LayoutParams.WRAP_CONTENT,1f)); row.addView(TextView(this).apply{text="›";textSize=28f;setTextColor(Color.parseColor("#94A3B8"))})
            linkContainer.addView(row,LinearLayout.LayoutParams(-1,-2).apply{setMargins(0,0,0,dp(10))})
        }; updateNav()
    }
    private fun renderCategoryLinks(c:CategoryItem){currentMode="category_links";showHomeBlocks(false);sectionTitle.visibility=View.VISIBLE;sectionTitle.text=c.name;renderLinkCards(c.links);updateNav()}

    private fun buildFeatured(){
        featuredContainer.removeAllViews(); allLinks().filter{it.isFeatured}.take(8).forEach{item->
            val card=LinearLayout(this).apply{orientation=LinearLayout.VERTICAL;gravity=Gravity.CENTER;setPadding(dp(10),dp(10),dp(10),dp(10));setBackgroundResource(R.drawable.card_bg);setOnClickListener{openLink(item)}}
            val logo=ImageView(this).apply{setImageResource(R.drawable.ic_launcher);scaleType=ImageView.ScaleType.CENTER_CROP};card.addView(logo,LinearLayout.LayoutParams(dp(48),dp(48)))
            card.addView(TextView(this).apply{text=item.title;textSize=12f;maxLines=1;gravity=Gravity.CENTER;setTextColor(Color.parseColor("#0F172A"));setTypeface(typeface,Typeface.BOLD);setPadding(0,dp(6),0,0)})
            featuredContainer.addView(card,LinearLayout.LayoutParams(dp(108),dp(94)).apply{setMargins(0,0,dp(10),0)});loadLogo(item.logoUrl,logo)
        }
    }

    private fun renderListPage(title:String,items:List<LinkItem>){showHomeBlocks(false);sectionTitle.visibility=View.VISIBLE;sectionTitle.text=title;renderLinkCards(items)}
    private fun renderLinkCards(items:List<LinkItem>){
        linkContainer.removeAllViews(); if(items.isEmpty()){linkContainer.addView(infoCard("ℹ","Nothing here yet","No matching items available."));return}
        items.forEach{item->
            val row=LinearLayout(this).apply{orientation=LinearLayout.HORIZONTAL;gravity=Gravity.CENTER_VERTICAL;setPadding(dp(12),dp(12),dp(10),dp(12));setBackgroundResource(R.drawable.card_bg);setOnClickListener{openLink(item)}}
            val logo=ImageView(this).apply{setImageResource(R.drawable.ic_launcher);scaleType=ImageView.ScaleType.CENTER_CROP};row.addView(logo,LinearLayout.LayoutParams(dp(50),dp(50)))
            val mid=LinearLayout(this).apply{orientation=LinearLayout.VERTICAL;setPadding(dp(12),0,dp(6),0)}
            mid.addView(TextView(this).apply{text=item.title;textSize=16f;setTypeface(typeface,Typeface.BOLD);setTextColor(Color.parseColor("#0F172A"))})
            if(item.description.isNotBlank())mid.addView(TextView(this).apply{text=item.description;textSize=12f;maxLines=2;setTextColor(Color.parseColor("#64748B"))})
            row.addView(mid,LinearLayout.LayoutParams(0,-2,1f)); row.addView(TextView(this).apply{text=if(isFavorite(item.id))"★" else "☆";textSize=23f;gravity=Gravity.CENTER;setTextColor(if(isFavorite(item.id))Color.parseColor("#F59E0B") else Color.parseColor("#94A3B8"));setOnClickListener{toggleFavorite(item.id)}},LinearLayout.LayoutParams(dp(44),dp(44)))
            linkContainer.addView(row,LinearLayout.LayoutParams(-1,-2).apply{setMargins(0,0,0,dp(10))});loadLogo(item.logoUrl,logo)
        }
    }

    private fun loadHomeAd(){
        thread{try{
            val arr=JSONObject(ApiUtil.get(ApiUtil.endpoint("ads.php")+"?slot=home_inline")).optJSONArray("ads") ?: return@thread
            if(arr.length()==0)return@thread; val ad=arr.getJSONObject(0);val title=ad.optString("title","Sponsored");val target=ad.optString("target_url","");val id=ad.optInt("id",0)
            runOnUiThread{findViewById<TextView>(R.id.adTitle).text=title;findViewById<TextView>(R.id.adSubtitle).text="Sponsored / Promotion";val box=findViewById<LinearLayout>(R.id.adContainer);box.setOnClickListener{if(target.isNotBlank()){UserSync.logActivity(this,"ad_open",id);thread{try{ApiUtil.get(ApiUtil.endpoint("ad_click.php")+"?id=$id")}catch(_:Exception){}};try{startActivity(Intent(Intent.ACTION_VIEW,Uri.parse(target)))}catch(_:Exception){}}}}
        }catch(_:Exception){}}
    }

    private fun refreshNotificationBadge(){
        thread{try{val j=JSONObject(ApiUtil.get(ApiUtil.endpoint("notifications.php"),token().ifBlank{null}));val unread=j.optInt("unread_count",0);runOnUiThread{notificationDot.visibility=if(unread>0)View.VISIBLE else View.GONE}}catch(_:Exception){}}
    }
    private fun markNotificationRead(id:Int){
        val t=token();if(t.isBlank())return
        thread{try{ApiUtil.postJson(ApiUtil.endpoint("notification_read.php"),JSONObject().put("notification_id",id),t);refreshNotificationBadge()}catch(_:Exception){}}
    }
    private fun loadNotifications(){
        currentMode="notifications";showHomeBlocks(false);sectionTitle.visibility=View.VISIBLE;sectionTitle.text="Notifications";linkContainer.removeAllViews();updateNav();linkContainer.addView(infoCard("🔔","Loading notifications","Please wait..."))
        thread{try{
            val json=JSONObject(ApiUtil.get(ApiUtil.endpoint("notifications.php"),token().ifBlank{null}));val arr=json.getJSONArray("notifications")
            runOnUiThread{linkContainer.removeAllViews();notificationDot.visibility=if(json.optInt("unread_count",0)>0)View.VISIBLE else View.GONE
                if(arr.length()==0)linkContainer.addView(infoCard("🔔","No new notifications","Important updates will appear here.")) else for(i in 0 until arr.length()){
                    val n=arr.getJSONObject(i);val id=n.optInt("id");val url=n.optString("target_url","");val read=n.optInt("is_read",0)==1
                    val card=infoCard(if(read)"✓" else "🔔",n.optString("title"),n.optString("message"));card.setOnClickListener{markNotificationRead(id);UserSync.logActivity(this,"notification_open",id);if(url.isNotBlank())try{startActivity(Intent(Intent.ACTION_VIEW,Uri.parse(url)))}catch(_:Exception){};loadNotifications()}
                    linkContainer.addView(card,LinearLayout.LayoutParams(-1,-2).apply{setMargins(0,0,0,dp(10))})
                }}
        }catch(_:Exception){runOnUiThread{linkContainer.removeAllViews();linkContainer.addView(infoCard("🔔","Notifications unavailable","Connect the live server/API."))}}}
    }

    private fun showProfile(){
        currentMode="profile";showHomeBlocks(false);sectionTitle.visibility=View.VISIBLE;sectionTitle.text="Profile";linkContainer.removeAllViews();updateNav()
        val t=token()
        if(t.isBlank()){
            linkContainer.addView(infoCard("👤","Login / Create Account","Sync favorites and personalize your experience."))
            linkContainer.addView(actionButton("LOGIN / REGISTER","#2563EB"){startActivityForResult(Intent(this,LoginActivity::class.java),1001)},LinearLayout.LayoutParams(-1,-2).apply{setMargins(0,dp(12),0,0)})
        }else{
            val name=prefs.getString("user_name","User") ?: "User";val email=prefs.getString("user_email","") ?: ""
            linkContainer.addView(infoCard("👤",name,email));linkContainer.addView(actionButton("LOGOUT","#DC2626"){
                val old=t;prefs.edit().remove("auth_token").remove("user_name").remove("user_email").apply();UserSync.logActivity(this,"logout");thread{try{ApiUtil.postJson(ApiUtil.endpoint("logout.php"),JSONObject(),old)}catch(_:Exception){}};showProfile()
            },LinearLayout.LayoutParams(-1,-2).apply{setMargins(0,dp(12),0,0)})
        }
    }
    private fun actionButton(label:String,color:String,onClick:()->Unit)=TextView(this).apply{text=label;gravity=Gravity.CENTER;textSize=15f;setTypeface(typeface,Typeface.BOLD);setTextColor(Color.WHITE);setBackgroundColor(Color.parseColor(color));setPadding(dp(16),dp(14),dp(16),dp(14));setOnClickListener{onClick()}}

    override fun onActivityResult(requestCode:Int,resultCode:Int,data:Intent?){super.onActivityResult(requestCode,resultCode,data);if(requestCode==1001&&resultCode==RESULT_OK){UserSync.pullFavorites(this){runOnUiThread{showProfile()}};UserSync.registerDevice(this)}}
    private fun infoCard(icon:String,title:String,desc:String)=LinearLayout(this).apply{orientation=LinearLayout.VERTICAL;gravity=Gravity.CENTER;setPadding(dp(18),dp(24),dp(18),dp(24));setBackgroundResource(R.drawable.card_bg);addView(TextView(this@MainActivity).apply{text=icon;textSize=34f;gravity=Gravity.CENTER});addView(TextView(this@MainActivity).apply{text=title;textSize=18f;gravity=Gravity.CENTER;setTypeface(typeface,Typeface.BOLD);setTextColor(Color.parseColor("#0F172A"));setPadding(0,dp(10),0,0)});addView(TextView(this@MainActivity).apply{text=desc;textSize=13f;gravity=Gravity.CENTER;setTextColor(Color.parseColor("#64748B"));setPadding(0,dp(6),0,0)})}
    private fun updateNav(){val a=Color.parseColor("#2563EB");val i=Color.parseColor("#64748B");navHome.setTextColor(if(currentMode=="home"||currentMode.startsWith("category"))a else i);navFavorites.setTextColor(if(currentMode=="favorites")a else i);navNotifications.setTextColor(if(currentMode=="notifications")a else i);navProfile.setTextColor(if(currentMode=="profile")a else i)}
    private fun openLink(item:LinkItem){UserSync.logActivity(this,"link_open",item.id);thread{try{ApiUtil.get(ApiUtil.endpoint("click.php")+"?id=${item.id}")}catch(_:Exception){}};if(item.openMode=="external")try{startActivity(Intent(Intent.ACTION_VIEW,Uri.parse(item.url)))}catch(_:Exception){} else startActivity(Intent(this,WebViewActivity::class.java).apply{putExtra("title",item.title);putExtra("url",item.url)})}
    private fun loadLogo(url:String,iv:ImageView){if(url.isBlank())return;thread{try{val b=URL(url).openStream().use{BitmapFactory.decodeStream(it)};runOnUiThread{iv.setImageBitmap(b)}}catch(_:Exception){}}}
}
