<?php
require '_header.php';

$categoryCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$linkCount = (int)$pdo->query("SELECT COUNT(*) FROM links")->fetchColumn();
$activeLinkCount = (int)$pdo->query("SELECT COUNT(*) FROM links WHERE is_active=1")->fetchColumn();
$totalClicks = (int)$pdo->query("SELECT COALESCE(SUM(click_count),0) FROM links")->fetchColumn();
$userCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$notificationCount = (int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE is_active=1")->fetchColumn();
$adCount = (int)$pdo->query("SELECT COUNT(*) FROM ads WHERE is_active=1")->fetchColumn();
$topLinks = $pdo->query(
    "SELECT l.title, l.click_count, c.name AS category_name
     FROM links l JOIN categories c ON c.id=l.category_id
     ORDER BY l.click_count DESC, l.id DESC
     LIMIT 10"
)->fetchAll();
?>
<h1>Dashboard</h1>

<div class="grid">
  <div class="card"><div class="small">Categories</div><div class="stat"><?= $categoryCount ?></div></div>
  <div class="card"><div class="small">Total Links</div><div class="stat"><?= $linkCount ?></div></div>
  <div class="card"><div class="small">Active Links</div><div class="stat"><?= $activeLinkCount ?></div></div>
  <div class="card"><div class="small">Total Clicks</div><div class="stat"><?= $totalClicks ?></div></div>
<div class="card"><div class="small">Users</div><div class="stat"><?= $userCount ?></div></div>
<div class="card"><div class="small">Live Notifications</div><div class="stat"><?= $notificationCount ?></div></div>
<div class="card"><div class="small">Active Ads</div><div class="stat"><?= $adCount ?></div></div>
</div>

<div class="card">
<h2>Quick Actions</h2>
<div class="actions">
<a class="btn" href="categories.php">Add / Manage Categories</a>
<a class="btn btn-secondary" href="links.php">Add / Manage Links</a>
<a class="btn btn-secondary" href="settings.php">App Settings</a>
<a class="btn btn-secondary" href="export.php">Export JSON</a>
</div>
</div>

<div class="card">
<h2>Top Links</h2>
<table>
<tr><th>Category</th><th>Link</th><th>Clicks</th></tr>
<?php foreach($topLinks as $r): ?>
<tr>
<td><?= e($r['category_name']) ?></td>
<td><?= e($r['title']) ?></td>
<td><?= (int)$r['click_count'] ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php require '_footer.php'; ?>
