<?php
require '_header.php';

$error='';
$edit=[
    'id'=>'','slot_name'=>'home_inline','title'=>'','image_url'=>'','target_url'=>'',
    'sort_order'=>0,'is_active'=>1,'starts_at'=>'','ends_at'=>''
];

if(isset($_GET['edit'])){
    $stmt=$pdo->prepare("SELECT * FROM ads WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    if($row=$stmt->fetch()) $edit=$row;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        verify_csrf();
        $action=$_POST['action']??'save';

        if($action==='delete'){
            $pdo->prepare("DELETE FROM ads WHERE id=?")->execute([(int)$_POST['id']]);
            redirect('ads.php');
        }

        if($action==='toggle'){
            $pdo->prepare("UPDATE ads SET is_active=IF(is_active=1,0,1) WHERE id=?")
                ->execute([(int)$_POST['id']]);
            redirect('ads.php');
        }

        $id=(int)($_POST['id']??0);
        $title=trim((string)($_POST['title']??''));
        $slot=$_POST['slot_name']??'home_inline';
        $target=trim((string)($_POST['target_url']??''));
        $image=trim((string)($_POST['image_url']??''));
        $order=(int)($_POST['sort_order']??0);
        $active=isset($_POST['is_active'])?1:0;
        $starts=trim((string)($_POST['starts_at']??'')) ?: null;
        $ends=trim((string)($_POST['ends_at']??'')) ?: null;

        if($title==='') throw new RuntimeException('Ad title required');
        if(!in_array($slot,['home_banner','home_inline','category_inline'],true)) $slot='home_inline';

        if($id>0){
            $stmt=$pdo->prepare(
                "UPDATE ads SET slot_name=?,title=?,image_url=?,target_url=?,
                 sort_order=?,is_active=?,starts_at=?,ends_at=? WHERE id=?"
            );
            $stmt->execute([$slot,$title,$image?:null,$target?:null,$order,$active,$starts,$ends,$id]);
        }else{
            $stmt=$pdo->prepare(
                "INSERT INTO ads(slot_name,title,image_url,target_url,sort_order,is_active,starts_at,ends_at)
                 VALUES(?,?,?,?,?,?,?,?)"
            );
            $stmt->execute([$slot,$title,$image?:null,$target?:null,$order,$active,$starts,$ends]);
        }
        redirect('ads.php');
    }catch(Throwable $e){$error=$e->getMessage();}
}

$rows=$pdo->query("SELECT * FROM ads ORDER BY slot_name,sort_order,id DESC")->fetchAll();
?>
<h1>Advertisements</h1>
<?php if($error):?><div class="error"><?=e($error)?></div><?php endif;?>

<div class="card">
<h2><?=$edit['id']?'Edit Advertisement':'Add Advertisement'?></h2>
<form method="post">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<input type="hidden" name="action" value="save">
<input type="hidden" name="id" value="<?=e((string)$edit['id'])?>">

<div class="grid">
<div>
<label>Slot</label>
<select name="slot_name">
<option value="home_banner" <?=$edit['slot_name']==='home_banner'?'selected':''?>>Home Banner</option>
<option value="home_inline" <?=$edit['slot_name']==='home_inline'?'selected':''?>>Home Inline</option>
<option value="category_inline" <?=$edit['slot_name']==='category_inline'?'selected':''?>>Category Inline</option>
</select>
</div>
<div><label>Title</label><input name="title" value="<?=e((string)$edit['title'])?>" required></div>
<div><label>Order</label><input name="sort_order" type="number" value="<?=e((string)$edit['sort_order'])?>"></div>
</div>

<label>Image URL</label>
<input name="image_url" type="url" value="<?=e((string)$edit['image_url'])?>" placeholder="https://...">

<label>Target URL</label>
<input name="target_url" type="url" value="<?=e((string)$edit['target_url'])?>" placeholder="https://...">

<div class="grid">
<div><label>Starts At</label><input name="starts_at" type="datetime-local" value="<?=e(str_replace(' ','T',(string)$edit['starts_at']))?>"></div>
<div><label>Ends At</label><input name="ends_at" type="datetime-local" value="<?=e(str_replace(' ','T',(string)$edit['ends_at']))?>"></div>
</div>

<label><input style="width:auto" type="checkbox" name="is_active" <?=(int)$edit['is_active']?'checked':''?>> Active</label>
<button type="submit"><?=$edit['id']?'Update Advertisement':'Add Advertisement'?></button>
</form>
</div>

<div class="card">
<table>
<tr><th>Slot</th><th>Title</th><th>Schedule</th><th>Clicks</th><th>Status</th><th>Actions</th></tr>
<?php foreach($rows as $r):?>
<tr>
<td><?=e($r['slot_name'])?></td>
<td><strong><?=e($r['title'])?></strong></td>
<td><div class="small">From: <?=e((string)$r['starts_at'])?></div><div class="small">To: <?=e((string)$r['ends_at'])?></div></td>
<td><?=(int)$r['click_count']?></td>
<td><span class="badge <?=$r['is_active']?'on':'off'?>"><?=$r['is_active']?'Active':'Inactive'?></span></td>
<td class="actions">
<a class="btn btn-secondary" href="?edit=<?=(int)$r['id']?>">Edit</a>
<form method="post">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<input type="hidden" name="action" value="toggle">
<input type="hidden" name="id" value="<?=(int)$r['id']?>">
<button type="submit" class="btn btn-secondary"><?=$r['is_active']?'Disable':'Enable'?></button>
</form>
<form method="post" onsubmit="return confirm('Delete ad?')">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?=(int)$r['id']?>">
<button class="btn-danger">Delete</button>
</form>
</td>
</tr>
<?php endforeach;?>
</table>
</div>
<?php require '_footer.php';?>
