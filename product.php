<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$id   = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    "SELECT p.*, c.name AS cat_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.id = ?"
);
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) redirect(SITE_URL . '/index.php');

$pageTitle = $p['name'];
require_once 'includes/header.php';

$imgFile = __DIR__ . '/uploads/' . $p['image'];
$hasImg  = $p['image'] && $p['image'] !== 'no-image.png' && file_exists($imgFile);
?>

<?php showFlash(); ?>

<nav class="mb-4" style="font-size:.85rem;">
    <a href="index.php" class="text-muted text-decoration-none">Shop</a>
    <?php if ($p['cat_name']): ?>
        <span class="text-muted mx-1">/</span>
        <a href="index.php?category=<?= $p['category_id'] ?>" class="text-muted text-decoration-none">
            <?= clean($p['cat_name']) ?>
        </a>
    <?php endif; ?>
    <span class="text-muted mx-1">/</span>
    <span><?= clean($p['name']) ?></span>
</nav>

<div class="row g-5">
    <div class="col-md-5">
        <?php if ($hasImg): ?>
            <img src="<?= SITE_URL ?>/uploads/<?= clean($p['image']) ?>"
                 class="img-fluid w-100" style="object-fit:cover;border:1px solid #e5e7eb;"
                 alt="<?= clean($p['name']) ?>">
        <?php else: ?>
            <div class="d-flex align-items-center justify-content-center border"
                 style="height:380px;background:#f3f4f6;color:#9ca3af;font-size:3rem;">
                <i class="bi bi-image"></i>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-7">
        <?php if ($p['cat_name']): ?>
            <p class="text-muted small mb-1 text-uppercase" style="letter-spacing:.05em;">
                <?= clean($p['cat_name']) ?>
            </p>
        <?php endif; ?>

        <h2 class="fw-bold mb-2"><?= clean($p['name']) ?></h2>
        <p class="fs-3 fw-bold mb-3"><?= price($p['price']) ?></p>

        <p class="text-muted mb-3" style="line-height:1.7;">
            <?= nl2br(clean($p['description'])) ?>
        </p>

        <p class="mb-4">
            <?php if ($p['stock'] > 5): ?>
                <span class="text-success small fw-semibold">&#10003; In Stock (<?= $p['stock'] ?> available)</span>
            <?php elseif ($p['stock'] > 0): ?>
                <span class="text-warning small fw-semibold">&#9888; Only <?= $p['stock'] ?> left</span>
            <?php else: ?>
                <span class="text-danger small fw-semibold">Out of Stock</span>
            <?php endif; ?>
        </p>

        <?php if ($p['stock'] > 0): ?>
            <form method="POST" action="cart.php" class="d-flex gap-3 align-items-end">
                <input type="hidden" name="action"     value="add">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <div>
                    <label class="form-label fw-semibold small">Quantity</label>
                    <input type="number" name="quantity" value="1"
                           min="1" max="<?= $p['stock'] ?>"
                           class="form-control" style="width:80px;">
                </div>
                <button type="submit" class="btn btn-primary px-4">Add to Cart</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
