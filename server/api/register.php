<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/api_auth.php';

try {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $name = trim((string)($input['name'] ?? ''));
    $email = strtolower(trim((string)($input['email'] ?? '')));
    $mobile = trim((string)($input['mobile'] ?? ''));
    $password = (string)($input['password'] ?? '');

    if (mb_strlen($name) < 2) throw new RuntimeException('Name required');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Valid email required');
    if (strlen($password) < 8) throw new RuntimeException('Password must be at least 8 characters');

    $stmt = $pdo->prepare("INSERT INTO users(name,email,mobile,password_hash) VALUES(?,?,?,?)");
    $stmt->execute([$name,$email,$mobile ?: null,password_hash($password,PASSWORD_DEFAULT)]);
    $userId = (int)$pdo->lastInsertId();
    $token = issue_user_token($pdo,$userId);

    echo json_encode([
        'success'=>true,
        'token'=>$token,
        'user'=>['id'=>$userId,'name'=>$name,'email'=>$email,'mobile'=>$mobile]
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(409);
    echo json_encode(['success'=>false,'message'=>'Account already exists or data is invalid']);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
