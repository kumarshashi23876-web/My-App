<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');
require dirname(__DIR__) . '/lib/bootstrap.php';

$slot = $_GET['slot'] ?? 'home_inline';
$allowed = ['home_banner','home_inline','category_inline'];
if (!in_array($slot,$allowed,true)) $slot='home_inline';

$stmt = $pdo->prepare(
    "SELECT id,slot_name,title,COALESCE(image_url,'') image_url,COALESCE(target_url,'') target_url
     FROM ads
     WHERE slot_name=? AND is_active=1
       AND (starts_at IS NULL OR starts_at<=NOW())
       AND (ends_at IS NULL OR ends_at>=NOW())
     ORDER BY sort_order,id DESC
     LIMIT 10"
);
$stmt->execute([$slot]);
echo json_encode(['success'=>true,'ads'=>$stmt->fetchAll()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
