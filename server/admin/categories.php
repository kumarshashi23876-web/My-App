<?php
require '_header.php';

$error = '';
$edit = ['id'=>'','name'=>'','icon'=>'📁','sort_order'=>0,'is_active'=>1];

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $row = $stmt->fetch();
    if ($row) $edit = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $action = $_POST['action'] ?? 'save';

        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id=?");
            $stmt->execute([(int)$_POST['id']]);
            redirect('categories.php');
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $icon = trim((string)($_POST['icon'] ?? '📁'));
        $sort = (int)($_POST['sort_order'] ?? 0);
        $active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') throw new RuntimeException('Category name आवश्यक है।');

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE categories SET name=?, icon=?, sort_order=?, is_active=? WHERE id=?");
            $stmt->execute([$name,$icon,$sort,$active,$id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories(name,icon,sort_order,is_active) VALUES(?,?,?,?)");
            $stmt->execute([$name,$icon,$sort,$active]);
        }
        redirect('categories.php');
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$rows = $pdo->query("SELECT * FROM categories ORDER BY sort_order,id")->fetchAll();
?>
<h1>Categories</h1>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>

<div class="card">
<h2><?= $edit['id'] ? 'Edit Category' : 'Add Category' ?></h2>
<form method="post">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<input type="hidden" name="action" value="save">
<input type="hidden" name="id" value="<?= e((string)$edit['id']) ?>">
<div class="grid">
<div><label>Category Name</label><input name="name" value="<?= e((string)$edit['name']) ?>" placeholder="News" required></div>
<div><label>Icon / Emoji</label><input name="icon" value="<?= e((string)$edit['icon']) ?>" placeholder="📰"></div>
<div><label>Display Order</label><input name="sort_order" type="number" value="<?= e((string)$edit['sort_order']) ?>"></div>
</div>
<label><input style="width:auto" type="checkbox" name="is_active" <?= (int)$edit['is_active'] ? 'checked' : '' ?>> Active</label>
<button type="submit"><?= $edit['id'] ? 'Update' : 'Add Category' ?></button>
</form>
</div>

<div class="card">
<table>
<tr><th>Order</th><th>Category</th><th>Status</th><th>Actions</th></tr>
<?php foreach ($rows as $r): ?>
<tr>
<td><?= (int)$r['sort_order'] ?></td>
<td><?= e($r['icon']) ?> <?= e($r['name']) ?></td>
<td><span class="badge <?= $r['is_active'] ? 'on':'off' ?>"><?= $r['is_active'] ? 'Active':'Inactive' ?></span></td>
<td class="actions">
<a class="btn btn-secondary" href="?edit=<?= (int)$r['id'] ?>">Edit</a>
<form method="post" onsubmit="return confirm('Delete category और उसके सभी links?')">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<button class="btn-danger" type="submit">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php require '_footer.php'; ?>
