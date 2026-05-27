<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'My Orders';
require_once 'includes/header.php';

$stmt = $pdo->prepare(
    "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<h5 class="page-title">My Orders</h5>

<?php if (empty($orders)): ?>
    <div class="text-center py-5 border text-muted">
        <p class="mb-3">You have no orders yet.</p>
        <a href="index.php" class="btn btn-primary btn-sm">Start Shopping</a>
    </div>
<?php else: ?>
    <?php foreach ($orders as $order):
        $items = $pdo->prepare("SELECT * FROM order_details WHERE order_id = ?");
        $items->execute([$order['id']]);
        $details = $items->fetchAll();
    ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <span class="fw-semibold">Order #<?= $order['id'] ?></span>
                    <span class="text-muted small ms-2">
                        <?= date('M j, Y', strtotime($order['created_at'])) ?>
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <?= statusBadge($order['status']) ?>
                    <span class="fw-bold"><?= price($order['total']) ?></span>
                </div>
            </div>
            <div class="card-body py-3">
                <?php foreach ($details as $d): ?>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>
                            <?= clean($d['product_name']) ?>
                            <span class="text-muted">x<?= $d['quantity'] ?></span>
                        </span>
                        <span><?= price($d['price'] * $d['quantity']) ?></span>
                    </div>
                <?php endforeach; ?>
                <p class="text-muted small mt-2 mb-0">
                    <i class="bi bi-geo-alt me-1"></i><?= clean($order['address']) ?>
                </p>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
