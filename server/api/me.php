<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require dirname(__DIR__) . '/lib/bootstrap.php';
require dirname(__DIR__) . '/lib/api_auth.php';

$user = require_api_user($pdo);
echo json_encode(['success'=>true,'user'=>$user], JSON_UNESCAPED_UNICODE);
