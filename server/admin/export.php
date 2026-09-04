<?php
declare(strict_types=1);
require dirname(__DIR__) . '/lib/bootstrap.php';
require_admin();

$settings = $pdo->query("SELECT * FROM app_settings WHERE id=1")->fetch();
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order,id")->fetchAll();
$links = $pdo->query("SELECT * FROM links ORDER BY category_id,sort_order,id")->fetchAll();

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="myapp-export-' . date('Y-m-d-His') . '.json"');

echo json_encode([
    'exported_at' => date(DATE_ATOM),
    'settings' => $settings,
    'categories' => $categories,
    'links' => $links
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
