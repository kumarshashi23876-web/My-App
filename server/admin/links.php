<?php
require '_header.php';

$error = '';
$edit = [
    'id'=>'','category_id'=>'','title'=>'','description'=>'','url'=>'','logo_url'=>'',
    'open_mode'=>'in_app','is_featured'=>0,'sort_order'=>0,'is_active'=>1
];

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM links WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $row = $stmt->fetch();
    if ($row) $edit = $row;
}

function save_logo_upload(): ?string {
    if (empty($_FILES['logo_file']['tmp_name'])) return null;

    if ($_FILES['logo_file']['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Logo upload failed.');
    }

    if ($_FILES['logo_file']['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Logo file 2 MB से कम रखें।');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['logo_file']['tmp_name']);
    $allowed = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp'
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Logo PNG/JPG/WEBP होना चाहिए।');
    }

    $uploadDir = dirname(__DIR__) . '/uploads';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new RuntimeException('Upload directory create नहीं हो सका।');
    }

    $filename = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    $target = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($_FILES['logo_file']['tmp_name'], $target)) {
        throw new RuntimeException('Uploaded logo save नहीं हुआ।');
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $adminPath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $basePath = dirname($adminPath);
    return $scheme . '://' . $host . $basePath . '/uploads/' . rawurlencode($filename);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $action = $_POST['action'] ?? 'save';

        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM links WHERE id=?");
            $stmt->execute([(int)$_POST['id']]);
            redirect('links.php');
        }

        $id = (int)($_POST['id'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $url = trim((string)($_POST['url'] ?? ''));
        $logoUrl = trim((string)($_POST['logo_url'] ?? ''));
        $uploaded = save_logo_upload();
        if ($uploaded) $logoUrl = $uploaded;

        $openMode = ($_POST['open_mode'] ?? 'in_app') === 'external' ? 'external' : 'in_app';
        $featured = isset($_POST['is_featured']) ? 1 : 0;
        $sort = (int)($_POST['sort_order'] ?? 0);
        $active = isset($_POST['is_active']) ? 1 : 0;

        if ($categoryId <= 0 || $title === '') throw new RuntimeException('Category और title आवश्यक हैं।');
        if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url)) {
            throw new RuntimeException('Valid http/https URL डालें।');
        }
        if ($logoUrl !== '' && (!filter_var($logoUrl, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $logoUrl))) {
            throw new RuntimeException('Logo URL valid http/https URL होना चाहिए।');
        }

        if ($id > 0) {
            $stmt = $pdo->prepare(
                "UPDATE links
                 SET category_id=?,title=?,description=?,url=?,logo_url=?,open_mode=?,
                     is_featured=?,sort_order=?,is_active=?
                 WHERE id=?"
            );
            $stmt->execute([
                $categoryId,$title,$description,$url,$logoUrl ?: null,$openMode,
                $featured,$sort,$active,$id
            ]);
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO links(
                    category_id,title,description,url,logo_url,open_mode,is_featured,sort_order,is_active
                 ) VALUES(?,?,?,?,?,?,?,?,?)"
            );
            $stmt->execute([
                $categoryId,$title,$description,$url,$logoUrl ?: null,$openMode,
                $featured,$sort,$active
            ]);
        }
        redirect('links.php');

    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$categories = $pdo->query("SELECT id,name FROM categories ORDER BY sort_order,id")->fetchAll();
$rows = $pdo->query(
    "SELECT l.*, c.name AS category_name
     FROM links l JOIN categories c ON c.id=l.category_id
     ORDER BY c.sort_order,l.sort_order,l.id"
)->fetchAll();
?>
<h1>Links</h1>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>

<div class="card">
<h2><?= $edit['id'] ? 'Edit Link' : 'Add Link' ?></h2>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<input type="hidden" name="action" value="save">
<input type="hidden" name="id" value="<?= e((string)$edit['id']) ?>">

<div class="grid">
<div>
<label>Category</label>
<select name="category_id" required>
<option value="">Select</option>
<?php foreach($categories as $c): ?>
<option value="<?= (int)$c['id'] ?>" <?= (int)$edit['category_id']===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
<?php endforeach; ?>
</select>
</div>
<div><label>Title</label><input name="title" value="<?= e((string)$edit['title']) ?>" placeholder="Aaj Tak" required></div>
<div><label>Display Order</label><input name="sort_order" type="number" value="<?= e((string)$edit['sort_order']) ?>"></div>
<div>
<label>Open Mode</label>
<select name="open_mode">
<option value="in_app" <?= $edit['open_mode']==='in_app'?'selected':'' ?>>Inside My App</option>
<option value="external" <?= $edit['open_mode']==='external'?'selected':'' ?>>External app/browser</option>
</select>
</div>
</div>

<label>Short Description</label>
<input name="description" maxlength="255" value="<?= e((string)$edit['description']) ?>" placeholder="Hindi news channel / official profile / education portal...">

<label>Website / Social URL</label>
<input name="url" type="url" value="<?= e((string)$edit['url']) ?>" placeholder="https://..." required>

<div class="grid">
<div>
<label>Logo Image URL (optional)</label>
<input name="logo_url" type="url" value="<?= e((string)$edit['logo_url']) ?>" placeholder="https://.../logo.png">
</div>
<div>
<label>या Logo Upload</label>
<input name="logo_file" type="file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
</div>
</div>

<label><input style="width:auto" type="checkbox" name="is_featured" <?= (int)$edit['is_featured'] ? 'checked':'' ?>> Featured</label>
<label><input style="width:auto" type="checkbox" name="is_active" <?= (int)$edit['is_active'] ? 'checked':'' ?>> Active</label>

<button type="submit"><?= $edit['id'] ? 'Update Link' : 'Add Link' ?></button>
</form>
</div>

<div class="card">
<table>
<tr><th>Category</th><th>Title</th><th>Mode</th><th>Clicks</th><th>Status</th><th>Actions</th></tr>
<?php foreach($rows as $r): ?>
<tr>
<td><?= e($r['category_name']) ?></td>
<td>
<strong><?= e($r['title']) ?></strong>
<?php if($r['is_featured']): ?> <span class="badge on">Featured</span><?php endif; ?>
<div class="small"><?= e($r['description']) ?></div>
<div class="small"><?= e($r['url']) ?></div>
</td>
<td><?= $r['open_mode']==='external'?'External':'In App' ?></td>
<td><?= (int)$r['click_count'] ?></td>
<td><span class="badge <?= $r['is_active']?'on':'off' ?>"><?= $r['is_active']?'Active':'Inactive' ?></span></td>
<td class="actions">
<a class="btn btn-secondary" href="?edit=<?= (int)$r['id'] ?>">Edit</a>
<form method="post" onsubmit="return confirm('Delete this link?')">
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
