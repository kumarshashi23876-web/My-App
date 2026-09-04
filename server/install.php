<?php
declare(strict_types=1);

require __DIR__ . '/lib/bootstrap.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();

        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (strlen($username) < 3 || strlen($password) < 10) {
            throw new RuntimeException('Username कम-से-कम 3 और password कम-से-कम 10 characters रखें।');
        }

        $sql = file_get_contents(__DIR__ . '/database.sql');
        foreach (array_filter(array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', $sql))) as $statement) {
            $pdo->exec($statement);
        }

        $count = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        if ($count === 0) {
            $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            $message = 'Installation complete. अब admin/login.php से login करें। Security के लिए install.php delete/rename कर दें।';
        } else {
            $message = 'Tables मौजूद हैं और admin पहले से बना हुआ है।';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="hi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My App Setup</title>
<link rel="stylesheet" href="admin/assets/style.css">
</head>
<body class="login-bg">
<div class="login-card">
    <h1>My App Setup</h1>
    <p>पहला admin account बनाएँ।</p>
    <?php if ($message): ?><div class="success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label>Admin Username</label>
        <input name="username" required minlength="3">
        <label>Admin Password</label>
        <input name="password" type="password" required minlength="10">
        <button type="submit">Install / Create Admin</button>
    </form>
</div>
</body>
</html>
