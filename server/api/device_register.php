<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/api_auth.php';

$user = api_user($pdo);
$input = json_decode(file_get_contents('php://input'), true) ?: [];

$deviceId = trim((string)($input['device_id'] ?? ''));
$pushToken = trim((string)($input['push_token'] ?? ''));

if ($deviceId === '' || strlen($deviceId) > 190) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid device id']);
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO device_tokens(user_id,device_id,platform,push_token,is_active)
     VALUES(?,?,'android',?,1)
     ON DUPLICATE KEY UPDATE
       user_id=VALUES(user_id),
       push_token=VALUES(push_token),
       is_active=1,
       last_seen_at=CURRENT_TIMESTAMP"
);
$stmt->execute([
    $user ? (int)$user['id'] : null,
    $deviceId,
    $pushToken ?: null
]);

echo json_encode(['success'=>true]);
