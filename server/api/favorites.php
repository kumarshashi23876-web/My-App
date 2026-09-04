<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/api_auth.php';

$user = require_api_user($pdo);
$userId = (int)$user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare(
        "SELECT l.id,l.title,l.description,l.url,COALESCE(l.logo_url,'') logo_url,
                l.open_mode,l.is_featured,c.id category_id,c.name category_name
         FROM user_favorites f
         JOIN links l ON l.id=f.link_id
         JOIN categories c ON c.id=l.category_id
         WHERE f.user_id=? AND l.is_active=1
         ORDER BY f.created_at DESC"
    );
    $stmt->execute([$userId]);
    echo json_encode(['success'=>true,'favorites'=>$stmt->fetchAll()],
        JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$linkId = (int)($input['link_id'] ?? 0);
$action = (string)($input['action'] ?? 'add');

if ($linkId <= 0) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid link']);
    exit;
}

if ($action === 'remove') {
    $stmt = $pdo->prepare("DELETE FROM user_favorites WHERE user_id=? AND link_id=?");
    $stmt->execute([$userId,$linkId]);
} else {
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_favorites(user_id,link_id) VALUES(?,?)");
    $stmt->execute([$userId,$linkId]);
}

echo json_encode(['success'=>true]);
