<?php
declare(strict_types=1);
require dirname(__DIR__) . '/lib/bootstrap.php';

if (!empty($_SESSION['admin_id'])) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        redirect('index.php');
    }
    $error = 'Username या password गलत है।';
}
?>
<!doctype html>
<html lang="hi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My App Admin Login</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-bg">
<div class="login-card">
<h1>My App Admin</h1>
<p>Category और URLs manage करने के लिए login करें।</p>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<form method="post">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label>Username</label><input name="username" required>
<label>Password</label><input name="password" type="password" required>
<button type="submit">Login</button>
</form>
</div>
</body>
</html>
