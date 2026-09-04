<?php
require '_header.php';
$error='';
$edit=['id'=>'','title'=>'','message'=>'','target_url'=>'','publish_at'=>'','is_active'=>1];
if(isset($_GET['edit'])){$s=$pdo->prepare("SELECT * FROM notifications WHERE id=?");$s->execute([(int)$_GET['edit']]);if($r=$s->fetch())$edit=$r;}
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{verify_csrf();$a=$_POST['action']??'save';$id=(int)($_POST['id']??0);
  if($a==='delete'){$pdo->prepare("DELETE FROM notifications WHERE id=?")->execute([$id]);redirect('notifications.php');}
  if($a==='toggle'){$pdo->prepare("UPDATE notifications SET is_active=IF(is_active=1,0,1) WHERE id=?")->execute([$id]);redirect('notifications.php');}
  $title=trim((string)($_POST['title']??''));$message=trim((string)($_POST['message']??''));$url=trim((string)($_POST['target_url']??''));$publish=trim((string)($_POST['publish_at']??''))?:null;$active=isset($_POST['is_active'])?1:0;
  if($title===''||$message==='')throw new RuntimeException('Title and message required');
  if($id>0){$s=$pdo->prepare("UPDATE notifications SET title=?,message=?,target_url=?,is_active=?,publish_at=? WHERE id=?");$s->execute([$title,$message,$url?:null,$active,$publish,$id]);}
  else{$s=$pdo->prepare("INSERT INTO notifications(title,message,target_url,is_active,publish_at) VALUES(?,?,?,?,?)");$s->execute([$title,$message,$url?:null,$active,$publish]);}
  redirect('notifications.php');
 }catch(Throwable $e){$error=$e->getMessage();}
}
$rows=$pdo->query("SELECT * FROM notifications ORDER BY id DESC LIMIT 300")->fetchAll();
?>
<h1>Notifications</h1><?php if($error):?><div class="error"><?=e($error)?></div><?php endif;?>
<div class="card"><h2><?=$edit['id']?'Edit Notification':'Create Notification'?></h2><form method="post">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=e((string)$edit['id'])?>">
<label>Title</label><input name="title" value="<?=e((string)$edit['title'])?>" required><label>Message</label><textarea name="message" rows="4" maxlength="500" required><?=e((string)$edit['message'])?></textarea>
<label>Target URL (optional)</label><input name="target_url" type="url" value="<?=e((string)$edit['target_url'])?>"><label>Publish At (optional)</label><input name="publish_at" type="datetime-local" value="<?=e(str_replace(' ','T',(string)$edit['publish_at']))?>">
<label><input style="width:auto" type="checkbox" name="is_active" <?=(int)$edit['is_active']?'checked':''?>> Active</label><button type="submit"><?=$edit['id']?'Update':'Publish / Schedule'?></button></form></div>
<div class="card"><table><tr><th>Title</th><th>Publish</th><th>Status</th><th>Actions</th></tr><?php foreach($rows as $r):?><tr><td><strong><?=e($r['title'])?></strong><div class="small"><?=e($r['message'])?></div></td><td><?=e((string)$r['publish_at'])?></td><td><span class="badge <?=$r['is_active']?'on':'off'?>"><?=$r['is_active']?'Active':'Inactive'?></span></td><td class="actions"><a class="btn btn-secondary" href="?edit=<?=(int)$r['id']?>">Edit</a><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><button class="btn btn-secondary"><?=$r['is_active']?'Disable':'Enable'?></button></form><form method="post" onsubmit="return confirm('Delete notification?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><button class="btn-danger">Delete</button></form></td></tr><?php endforeach;?></table></div>
<?php require '_footer.php';?>
