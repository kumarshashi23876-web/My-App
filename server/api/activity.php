<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/api_auth.php';

$user = api_user($pdo);
$input = json_decode(file_get_contents('php://input'), true) ?: [];

$event = trim((string)($input['event_type'] ?? ''));
$referenceId = isset($input['reference_id']) ? (int)$input['reference_id'] : null;
$meta = isset($input['meta']) && is_array($input['meta'])
    ? json_encode($input['meta'], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
    : null;

$allowed = ['app_open','link_open','favorite_add','favorite_remove','notification_open','ad_open','login','logout'];

if (!in_array($event,$allowed,true)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid event']);
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO user_activity(user_id,event_type,reference_id,meta_json)
     VALUES(?,?,?,?)"
);
$stmt->execute([
    $user ? (int)$user['id'] : null,
    $event,
    $referenceId,
    $meta
]);

echo json_encode(['success'=>true]);
