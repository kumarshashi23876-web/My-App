<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require dirname(__DIR__) . '/lib/bootstrap.php';
$id=(int)($_GET['id']??0);
if($id>0){
    $stmt=$pdo->prepare("UPDATE ads SET click_count=click_count+1 WHERE id=?");
    $stmt->execute([$id]);
}
echo json_encode(['success'=>true]);
