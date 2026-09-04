<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');
header('Access-Control-Allow-Origin: *');

try {
    require dirname(__DIR__) . '/lib/bootstrap.php';

    $settings = $pdo->query(
        "SELECT app_name, subtitle, primary_color FROM app_settings WHERE id = 1"
    )->fetch() ?: [
        'app_name' => 'My App',
        'subtitle' => 'सब कुछ, एक ही ऐप में',
        'primary_color' => '#111827'
    ];

    $catStmt = $pdo->query(
        "SELECT id, name, icon
         FROM categories
         WHERE is_active = 1
         ORDER BY sort_order ASC, id ASC"
    );

    $linkStmt = $pdo->prepare(
        "SELECT id, title, description, url, COALESCE(logo_url,'') AS logo_url,
                open_mode, is_featured
         FROM links
         WHERE category_id = ? AND is_active = 1
         ORDER BY is_featured DESC, sort_order ASC, id ASC"
    );

    $categories = [];
    foreach ($catStmt->fetchAll() as $cat) {
        $linkStmt->execute([$cat['id']]);
        $links = array_map(static function(array $row): array {
            return [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'url' => $row['url'],
                'logo_url' => $row['logo_url'],
                'open_mode' => $row['open_mode'],
                'is_featured' => (bool)$row['is_featured'],
            ];
        }, $linkStmt->fetchAll());

        $categories[] = [
            'id' => (int)$cat['id'],
            'name' => $cat['name'],
            'icon' => $cat['icon'],
            'links' => $links,
        ];
    }

    echo json_encode([
        'success' => true,
        'app' => [
            'name' => $settings['app_name'],
            'subtitle' => $settings['subtitle'],
            'primary_color' => $settings['primary_color'],
        ],
        'categories' => $categories,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error'], JSON_UNESCAPED_UNICODE);
}
