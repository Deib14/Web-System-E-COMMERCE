<?php
$pageTitle = 'Shop';
require_once 'includes/header.php';


$search = trim($_GET['search']   ?? '');
$catId  = (int)($_GET['category'] ?? 0);
$sort   = $_GET['sort']           ?? 'newest';

$where  = ['1'];
$params = [];

if ($search) {
    $where[]  = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($catId) {
    $where[]  = 'p.category_id = ?';
    $params[] = $catId;
}

$orderBy = match($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'name'       => 'p.name ASC',
    default      => 'p.created_at DESC',
};

$sql  = "SELECT p.*, c.name AS cat_name
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         WHERE " . implode(' AND ', $where) . "
         ORDER BY $orderBy";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<?php showFlash(); ?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 pb-3 border-bottom">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <a href="index.php" class="btn btn-sm <?= !$catId ? 'btn-dark' : 'btn-outline-secondary' ?>">All</a>
        <?php foreach ($categories as $c): ?>
            <a href="index.php?category=<?= $c['id'] ?>&sort=<?= clean($sort) ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
               class="btn btn-sm <?= $catId == $c['id'] ? 'btn-dark' : 'btn-outline-secondary' ?>">
                <?= clean($c['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <?php if ($catId):   ?><input type="hidden" name="category" value="<?= $catId ?>"><?php endif; ?>
        <?php if ($search):  ?><input type="hidden" name="search"   value="<?= clean($search) ?>"><?php endif; ?>
        <label class="text-muted small mb-0">Sort:</label>
        <select name="sort" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
            <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Newest</option>
            <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Price ↑</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price ↓</option>
            <option value="name"       <?= $sort === 'name'       ? 'selected' : '' ?>>Name A–Z</option>
        </select>
    </form>
</div>

<?php if ($search): ?>
    <p class="text-muted small mb-3">
        <?= count($products) ?> result<?= count($products) != 1 ? 's' : '' ?> for
        "<strong><?= clean($search) ?></strong>"
        <a href="index.php" class="ms-2 text-muted">✕ Clear</a>
    </p>
<?php endif; ?>

<?php if (empty($products)): ?>
    <div class="text-center py-5 text-muted">
        <p class="mb-3">No products found.</p>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">View all products</a>
    </div>
<?php else: ?>
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
        <?php foreach ($products as $p):
            $imgFile = dirname(__FILE__) . '/uploads/' . $p['image'];
            $hasImg  = $p['image'] && $p['image'] !== 'no-image.png' && file_exists($imgFile);
        ?>
        <div class="col">
            <div class="card product-card h-100">
                <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none">
                    <?php if ($hasImg): ?>
                        <img src="<?= SITE_URL ?>/uploads/<?= clean($p['image']) ?>"
                             class="card-img-top" alt="<?= clean($p['name']) ?>">
                    <?php else: ?>
                        <div class="img-placeholder"><i class="bi bi-image"></i></div>
                    <?php endif; ?>
                </a>
                <div class="card-body d-flex flex-column p-3">
                    <?php if ($p['cat_name']): ?>
                        <small class="text-muted mb-1"><?= clean($p['cat_name']) ?></small>
                    <?php endif; ?>
                    <p class="mb-1 fw-semibold lh-sm">
                        <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark">
                            <?= clean($p['name']) ?>
                        </a>
                    </p>
                    <p class="text-price mb-2"><?= price($p['price']) ?></p>
                    <?php if ($p['stock'] == 0): ?>
                        <small class="text-danger mb-2">Out of stock</small>
                    <?php elseif ($p['stock'] <= 5): ?>
                        <small class="text-warning mb-2">Only <?= $p['stock'] ?> left</small>
                    <?php endif; ?>
                    <div class="mt-auto">
                        <?php if ($p['stock'] > 0): ?>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="action"     value="add">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm w-100">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary btn-sm w-100" disabled>Unavailable</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
