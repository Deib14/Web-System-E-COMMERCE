<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pageTitle = 'Products';
require_once __DIR__ . '/includes/header.php';

// DELETE
if (isset($_GET['delete'])) {
    $id   = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $prod = $stmt->fetch();
    if ($prod) {
        if ($prod['image'] !== 'no-image.png') {
            @unlink(dirname(__DIR__) . '/uploads/' . $prod['image']);
        }
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        setFlash('success', 'Product deleted.');
    }
    redirect(SITE_URL . '/admin/products.php');
}

$products = $pdo->query(
    "SELECT p.*, c.name AS cat_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     ORDER BY p.created_at DESC"
)->fetchAll();

showFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small"><?= count($products) ?> products</span>
    <a href="<?= SITE_URL ?>/admin/add_product.php" class="btn btn-primary btn-sm">+ Add Product</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th class="ps-3">Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No products yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($products as $p):
                $imgFile = dirname(__DIR__) . '/uploads/' . $p['image'];
                $hasImg  = $p['image'] !== 'no-image.png' && file_exists($imgFile);
            ?>
                <tr>
                    <td class="ps-3">
                        <?php if ($hasImg): ?>
                            <img src="<?= SITE_URL ?>/uploads/<?= clean($p['image']) ?>"
                                 width="46" height="46" style="object-fit:cover;border:1px solid #e5e7eb;">
                        <?php else: ?>
                            <div style="width:46px;height:46px;background:#f3f4f6;border:1px solid #e5e7eb;
                                        display:flex;align-items:center;justify-content:center;color:#9ca3af;">
                                <i class="bi bi-image small"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="fw-semibold"><?= clean($p['name']) ?></td>
                    <td class="text-muted small"><?= clean($p['cat_name'] ?? '—') ?></td>
                    <td>&#8369;<?= number_format($p['price'], 2) ?></td>
                    <td>
                        <?php if ($p['stock'] == 0): ?>
                            <span class="badge bg-danger">0</span>
                        <?php elseif ($p['stock'] <= 5): ?>
                            <span class="badge" style="background:#b45309;"><?= $p['stock'] ?></span>
                        <?php else: ?>
                            <span class="text-muted small"><?= $p['stock'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= SITE_URL ?>/admin/edit_product.php?id=<?= $p['id'] ?>"
                           class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                        <a href="<?= SITE_URL ?>/admin/products.php?delete=<?= $p['id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete this product permanently?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
