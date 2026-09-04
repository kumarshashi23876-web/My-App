<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/api_auth.php';

$user = require_api_user($pdo);
$userId = (int)$user['id'];

$stmt = $pdo->prepare(
    "INSERT IGNORE INTO notification_reads(user_id,notification_id)
     SELECT ?, id
     FROM notifications
     WHERE is_active=1 AND (publish_at IS NULL OR publish_at<=NOW())"
);
$stmt->execute([$userId]);

echo json_encode(['success'=>true]);
