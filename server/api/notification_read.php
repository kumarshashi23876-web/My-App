<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/api_auth.php';

$user = require_api_user($pdo);
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$id = (int)($input['notification_id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid notification']);
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO notification_reads(user_id,notification_id)
     VALUES(?,?)
     ON DUPLICATE KEY UPDATE read_at=CURRENT_TIMESTAMP"
);
$stmt->execute([(int)$user['id'],$id]);

echo json_encode(['success'=>true]);
