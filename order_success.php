<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

requireLogin();
if (empty($_SESSION['last_order'])) redirect(SITE_URL . '/index.php');

$pageTitle = 'Order Placed';
require_once 'includes/header.php';

$orderId = (int)$_SESSION['last_order'];
unset($_SESSION['last_order']);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();
if (!$order) redirect(SITE_URL . '/index.php');

$items = $pdo->prepare("SELECT * FROM order_details WHERE order_id = ?");
$items->execute([$orderId]);
$details = $items->fetchAll();
?>

<div class="row justify-content-center">
<div class="col-md-6">

    <div class="text-center py-4 mb-4 border-bottom">
        <div style="font-size:3rem;">&#10003;</div>
        <h4 class="fw-bold mt-2">Order Placed Successfully</h4>
        <p class="text-muted">
            Order #<?= $orderId ?> &bull;
            <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?>
        </p>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Items Ordered</h6>
            <?php foreach ($details as $d): ?>
                <div class="d-flex justify-content-between small mb-2">
                    <span><?= clean($d['product_name']) ?> <span class="text-muted">x<?= $d['quantity'] ?></span></span>
                    <span><?= price($d['price'] * $d['quantity']) ?></span>
                </div>
            <?php endforeach; ?>
            <hr>
            <div class="d-flex justify-content-between fw-bold">
                <span>Total</span><span><?= price($order['total']) ?></span>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body small">
            <p class="fw-semibold mb-1">Delivering to:</p>
            <p class="mb-1"><?= clean($order['address']) ?></p>
            <p class="mb-0 text-muted"><?= clean($order['phone']) ?></p>
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-center">
        <a href="orders.php"  class="btn btn-primary">View My Orders</a>
        <a href="index.php"   class="btn btn-outline-secondary">Continue Shopping</a>
    </div>

</div>
</div>

<?php require_once 'includes/footer.php'; ?>
