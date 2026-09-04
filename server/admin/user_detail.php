<?php
require '_header.php';

$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare("SELECT id,name,email,mobile,is_active,created_at,updated_at FROM users WHERE id=?");
$stmt->execute([$id]);
$user=$stmt->fetch();

if(!$user){
    echo '<div class="error">User not found.</div>';
    require '_footer.php';
    exit;
}

$favStmt=$pdo->prepare(
    "SELECT l.title,c.name category_name,f.created_at
     FROM user_favorites f
     JOIN links l ON l.id=f.link_id
     JOIN categories c ON c.id=l.category_id
     WHERE f.user_id=?
     ORDER BY f.created_at DESC
     LIMIT 100"
);
$favStmt->execute([$id]);
$favs=$favStmt->fetchAll();

$actStmt=$pdo->prepare(
    "SELECT event_type,reference_id,created_at
     FROM user_activity
     WHERE user_id=?
     ORDER BY id DESC
     LIMIT 100"
);
$actStmt->execute([$id]);
$activity=$actStmt->fetchAll();
?>
<h1>User Detail</h1>
<div class="card">
<h2><?=e($user['name'])?></h2>
<p><strong>Email:</strong> <?=e($user['email'])?></p>
<p><strong>Mobile:</strong> <?=e((string)$user['mobile'])?></p>
<p><strong>Joined:</strong> <?=e($user['created_at'])?></p>
</div>

<div class="card">
<h2>Favorites</h2>
<table><tr><th>Category</th><th>Link</th><th>Added</th></tr>
<?php foreach($favs as $r):?>
<tr><td><?=e($r['category_name'])?></td><td><?=e($r['title'])?></td><td><?=e($r['created_at'])?></td></tr>
<?php endforeach;?>
</table>
</div>

<div class="card">
<h2>Recent Activity</h2>
<table><tr><th>Event</th><th>Reference</th><th>Time</th></tr>
<?php foreach($activity as $r):?>
<tr><td><?=e($r['event_type'])?></td><td><?=e((string)$r['reference_id'])?></td><td><?=e($r['created_at'])?></td></tr>
<?php endforeach;?>
</table>
</div>
<?php require '_footer.php';?>
