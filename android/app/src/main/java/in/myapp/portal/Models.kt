package in.myapp.portal

data class LinkItem(
    val id: Int,
    val title: String,
    val description: String,
    val url: String,
    val logoUrl: String,
    val openMode: String,
    val isFeatured: Boolean
)

data class CategoryItem(
    val id: Int,
    val name: String,
    val icon: String,
    val links: MutableList<LinkItem>
)
