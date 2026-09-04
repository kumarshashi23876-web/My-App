<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/api_auth.php';

$user = api_user($pdo);
$userId = $user ? (int)$user['id'] : 0;

if ($userId > 0) {
    $stmt = $pdo->prepare(
        "SELECT n.id,n.title,n.message,COALESCE(n.target_url,'') target_url,n.created_at,
                CASE WHEN r.notification_id IS NULL THEN 0 ELSE 1 END AS is_read
         FROM notifications n
         LEFT JOIN notification_reads r
           ON r.notification_id=n.id AND r.user_id=?
         WHERE n.is_active=1 AND (n.publish_at IS NULL OR n.publish_at<=NOW())
         ORDER BY COALESCE(n.publish_at,n.created_at) DESC,n.id DESC
         LIMIT 100"
    );
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();
} else {
    $rows = $pdo->query(
        "SELECT id,title,message,COALESCE(target_url,'') target_url,created_at,0 AS is_read
         FROM notifications
         WHERE is_active=1 AND (publish_at IS NULL OR publish_at<=NOW())
         ORDER BY COALESCE(publish_at,created_at) DESC,id DESC
         LIMIT 100"
    )->fetchAll();
}

$unread = 0;
foreach ($rows as $r) {
    if ((int)$r['is_read'] === 0) $unread++;
}

echo json_encode([
    'success'=>true,
    'unread_count'=>$unread,
    'notifications'=>$rows
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
