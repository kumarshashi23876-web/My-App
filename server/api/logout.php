<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/api_auth.php';

$token = bearer_token();
if ($token) {
    $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE token_hash=?");
    $stmt->execute([hash('sha256',$token)]);
}
echo json_encode(['success'=>true]);
