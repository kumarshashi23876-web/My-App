<?php
declare(strict_types=1);

function bearer_token(): ?string {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
        return trim($m[1]);
    }
    return null;
}

function api_user(PDO $pdo): ?array {
    $token = bearer_token();
    if (!$token) return null;

    $hash = hash('sha256', $token);
    $stmt = $pdo->prepare(
        "SELECT u.id,u.name,u.email,u.mobile,u.is_active
         FROM user_tokens t
         JOIN users u ON u.id=t.user_id
         WHERE t.token_hash=? AND t.expires_at>NOW() AND u.is_active=1
         LIMIT 1"
    );
    $stmt->execute([$hash]);
    return $stmt->fetch() ?: null;
}

function require_api_user(PDO $pdo): array {
    $user = api_user($pdo);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success'=>false,'message'=>'Unauthorized'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    return $user;
}

function issue_user_token(PDO $pdo, int $userId): string {
    $plain = bin2hex(random_bytes(32));
    $hash = hash('sha256', $plain);
    $stmt = $pdo->prepare(
        "INSERT INTO user_tokens(user_id,token_hash,expires_at)
         VALUES(?,?,DATE_ADD(NOW(), INTERVAL 30 DAY))"
    );
    $stmt->execute([$userId,$hash]);
    return $plain;
}
