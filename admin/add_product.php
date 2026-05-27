<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pageTitle = 'Add Product';
require_once __DIR__ . '/includes/header.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors = [];
$f      = ['name' => '', 'description' => '', 'price' => '', 'stock' => 0, 'category_id' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f['name']        = trim($_POST['name']        ?? '');
    $f['description'] = trim($_POST['description'] ?? '');
    $f['price']       = $_POST['price']              ?? '';
    $f['stock']       = $_POST['stock']              ?? 0;
    $f['category_id'] = (int)($_POST['category_id'] ?? 0) ?: null;

    // Validate
    if (strlen($f['name']) < 2)                     $errors[] = 'Product name is required.';
    if (!is_numeric($f['price']) || $f['price'] <= 0) $errors[] = 'Enter a valid price greater than 0.';
    if (!is_numeric($f['stock']) || $f['stock'] < 0)  $errors[] = 'Enter a valid stock quantity (0 or more).';

    // Image upload
    $image = 'no-image.png';
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowed)) {
            $errors[] = 'Image must be JPG, PNG, GIF, or WEBP.';
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Image must be under 2MB.';
        } else {
            $ext   = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $image = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], dirname(__DIR__) . '/uploads/' . $image)) {
                $errors[] = 'Failed to upload image. Check folder permissions.';
                $image    = 'no-image.png';
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO products (category_id, name, description, price, stock, image)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$f['category_id'], $f['name'], $f['description'], $f['price'], $f['stock'], $image]);
        setFlash('success', 'Product added successfully.');
        redirect(SITE_URL . '/admin/products.php');
    }
}
?>

<a href="<?= SITE_URL ?>/admin/products.php" class="btn btn-outline-secondary btn-sm mb-4">← Back</a>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card p-4" style="max-width:680px;">
    <form method="POST" enctype="multipart/form-data" novalidate>
        <div class="mb-3">
            <label class="form-label fw-semibold">Product Name *</label>
            <input type="text" name="name" class="form-control"
                   value="<?= clean($f['name']) ?>" required>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Price (₱) *</label>
                <input type="number" name="price" class="form-control"
                       step="0.01" min="0" value="<?= clean($f['price']) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Stock *</label>
                <input type="number" name="stock" class="form-control"
                       min="0" value="<?= (int)$f['stock'] ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">None</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $f['category_id'] == $c['id'] ? 'selected' : '' ?>>
                            <?= clean($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= clean($f['description']) ?></textarea>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Product Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <div class="form-text">JPG, PNG, WEBP — max 2MB. Leave blank for no image.</div>
        </div>
        <button type="submit" class="btn btn-primary">Add Product</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
