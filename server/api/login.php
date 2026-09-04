<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/api_auth.php';

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$email = strtolower(trim((string)($input['email'] ?? '')));
$password = (string)($input['password'] ?? '');

$stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND is_active=1 LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password,$user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Invalid email or password']);
    exit;
}

$token = issue_user_token($pdo,(int)$user['id']);
echo json_encode([
    'success'=>true,
    'token'=>$token,
    'user'=>[
        'id'=>(int)$user['id'],
        'name'=>$user['name'],
        'email'=>$user['email'],
        'mobile'=>$user['mobile']
    ]
], JSON_UNESCAPED_UNICODE);
