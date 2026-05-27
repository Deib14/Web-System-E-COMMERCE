<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pageTitle = 'Edit Product';
require_once __DIR__ . '/includes/header.php';

$id   = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) redirect(SITE_URL . '/admin/products.php');

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = $_POST['price']              ?? '';
    $stock       = $_POST['stock']              ?? 0;
    $category_id = (int)($_POST['category_id'] ?? 0) ?: null;

    if (strlen($name) < 2)                          $errors[] = 'Product name is required.';
    if (!is_numeric($price) || $price <= 0)          $errors[] = 'Enter a valid price greater than 0.';
    if (!is_numeric($stock) || $stock < 0)           $errors[] = 'Enter a valid stock quantity (0 or more).';

    $image = $p['image'];
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowed)) {
            $errors[] = 'Image must be JPG, PNG, GIF, or WEBP.';
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Image must be under 2MB.';
        } else {
            // Delete old image
            if ($image !== 'no-image.png') @unlink(dirname(__DIR__) . '/uploads/' . $image);
            $ext   = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $image = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], dirname(__DIR__) . '/uploads/' . $image)) {
                $errors[] = 'Failed to upload image.';
                $image    = $p['image']; // revert
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, image=?
             WHERE id=?"
        );
        $stmt->execute([$category_id, $name, $description, $price, $stock, $image, $id]);
        setFlash('success', 'Product updated.');
        redirect(SITE_URL . '/admin/products.php');
    }

    // Repopulate form
    $p = array_merge($p, compact('name', 'description', 'price', 'stock', 'category_id'));
}

$imgFile = dirname(__DIR__) . '/uploads/' . $p['image'];
$hasImg  = $p['image'] !== 'no-image.png' && file_exists($imgFile);
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
                   value="<?= clean($p['name']) ?>" required>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Price (₱) *</label>
                <input type="number" name="price" class="form-control"
                       step="0.01" min="0" value="<?= clean($p['price']) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Stock *</label>
                <input type="number" name="stock" class="form-control"
                       min="0" value="<?= (int)$p['stock'] ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">None</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $p['category_id'] == $c['id'] ? 'selected' : '' ?>>
                            <?= clean($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= clean($p['description']) ?></textarea>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Product Image</label>
            <?php if ($hasImg): ?>
                <div class="mb-2">
                    <img src="<?= SITE_URL ?>/uploads/<?= clean($p['image']) ?>"
                         height="70" style="border:1px solid #e5e7eb;object-fit:cover;">
                    <small class="text-muted d-block mt-1">Upload a new file to replace the current image.</small>
                </div>
            <?php endif; ?>
            <input type="file" name="image" class="form-control" accept="image/*">
            <div class="form-text">JPG, PNG, WEBP — max 2MB.</div>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
