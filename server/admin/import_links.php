<?php
require '_header.php';
$msg='';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{verify_csrf();if(empty($_FILES['csv']['tmp_name']))throw new RuntimeException('CSV file required');
  $fh=fopen($_FILES['csv']['tmp_name'],'r');if(!$fh)throw new RuntimeException('Cannot read CSV');$header=fgetcsv($fh);if(!$header)throw new RuntimeException('Empty CSV');
  $map=array_flip(array_map('trim',$header));$required=['category','title','url'];foreach($required as $r)if(!isset($map[$r]))throw new RuntimeException("Missing column: $r");
  $count=0;$pdo->beginTransaction();while(($row=fgetcsv($fh))!==false){$cat=trim($row[$map['category']]??'');$title=trim($row[$map['title']]??'');$url=trim($row[$map['url']]??'');if($cat===''||$title===''||!filter_var($url,FILTER_VALIDATE_URL))continue;
   $s=$pdo->prepare("SELECT id FROM categories WHERE name=? LIMIT 1");$s->execute([$cat]);$cid=$s->fetchColumn();if(!$cid){$s=$pdo->prepare("INSERT INTO categories(name,icon,sort_order,is_active) VALUES(?,'📁',999,1)");$s->execute([$cat]);$cid=$pdo->lastInsertId();}
   $desc=isset($map['description'])?trim($row[$map['description']]??''):'';$logo=isset($map['logo_url'])?trim($row[$map['logo_url']]??''):'';$mode=isset($map['open_mode'])&&trim($row[$map['open_mode']]??'')==='external'?'external':'in_app';$featured=isset($map['is_featured'])?(int)(trim($row[$map['is_featured']]??'')==='1'):0;
   $s=$pdo->prepare("INSERT INTO links(category_id,title,description,url,logo_url,open_mode,is_featured,sort_order,is_active) VALUES(?,?,?,?,?,?,?,999,1)");$s->execute([$cid,$title,$desc,$url,$logo?:null,$mode,$featured]);$count++;}
  fclose($fh);$pdo->commit();$msg="$count links imported.";
 }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$error=$e->getMessage();}
}
?>
<h1>Import Links</h1><?php if($msg):?><div class="success"><?=e($msg)?></div><?php endif;?><?php if($error):?><div class="error"><?=e($error)?></div><?php endif;?>
<div class="card"><p>CSV columns: <strong>category,title,url,description,logo_url,open_mode,is_featured</strong></p><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>CSV File</label><input type="file" name="csv" accept=".csv,text/csv" required><button type="submit">Import</button></form></div>
<?php require '_footer.php';?>
