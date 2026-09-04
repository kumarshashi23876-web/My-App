<?php
require '_header.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $id=(int)($_POST['id']??0);
    $active=(int)($_POST['active']??0);
    $pdo->prepare("UPDATE users SET is_active=? WHERE id=?")->execute([$active,$id]);
    redirect('users.php');
}
$rows=$pdo->query("SELECT id,name,email,mobile,is_active,created_at FROM users ORDER BY id DESC LIMIT 500")->fetchAll();
?>
<h1>Users</h1>
<div class="card">
<table><tr><th>User</th><th>Contact</th><th>Joined</th><th>Status</th><th>Action</th></tr>
<?php foreach($rows as $r):?>
<tr>
<td><strong><a href="user_detail.php?id=<?=(int)$r['id']?>"><?=e($r['name'])?></a></strong></td>
<td><?=e($r['email'])?><div class="small"><?=e((string)$r['mobile'])?></div></td>
<td><?=e($r['created_at'])?></td>
<td><span class="badge <?=$r['is_active']?'on':'off'?>"><?=$r['is_active']?'Active':'Blocked'?></span></td>
<td><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><input type="hidden" name="active" value="<?=$r['is_active']?0:1?>"><button class="<?=$r['is_active']?'btn-danger':'btn'?>"><?=$r['is_active']?'Block':'Activate'?></button></form></td>
</tr>
<?php endforeach;?></table>
</div>
<?php require '_footer.php';?>
