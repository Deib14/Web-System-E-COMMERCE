<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'Checkout';
require_once 'includes/header.php';
if (empty($_SESSION['cart'])) redirect(SITE_URL . '/cart.php');

$ids  = array_map('intval', array_keys($_SESSION['cart']));
$in   = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in) AND stock > 0");
$stmt->execute($ids);
$rows = $stmt->fetchAll();

$productMap = [];
foreach ($rows as $row) {
    $productMap[$row['id']] = $row;
}

$cartItems = [];
$subtotal  = 0;

foreach ($_SESSION['cart'] as $pid => $qty) {
    $pid = (int)$pid;
    if (!isset($productMap[$pid])) continue;

    $item               = $productMap[$pid];
    $item['qty']        = min((int)$qty, $item['stock']);
    $item['line_total'] = $item['price'] * $item['qty'];
    $subtotal          += $item['line_total'];
    $cartItems[]        = $item;
}

if (empty($cartItems)) {
    setFlash('danger', 'Your cart has no available items.');
    redirect(SITE_URL . '/cart.php');
}

$shipping = 99.00;
$total    = $subtotal + $shipping;

$errors  = [];
$address = $phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $phone   = trim($_POST['phone']   ?? '');

    if (strlen($address) < 5) $errors[] = 'Please enter a complete delivery address.';
    if (strlen($phone)   < 7) $errors[] = 'Please enter a valid phone number.';

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {

            $stmt = $pdo->prepare(
                "INSERT INTO orders (user_id, total, address, phone) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$_SESSION['user_id'], $total, $address, $phone]);
            $orderId = $pdo->lastInsertId();

            $insDetail = $pdo->prepare(
                "INSERT INTO order_details (order_id, product_id, product_name, quantity, price)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $deduct = $pdo->prepare(
                "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?"
            );

            foreach ($cartItems as $item) {
                $insDetail->execute([
                    $orderId, $item['id'], $item['name'], $item['qty'], $item['price']
                ]);
                $deduct->execute([$item['qty'], $item['id'], $item['qty']]);
            }

            $pdo->commit();
            $_SESSION['cart']       = [];
            $_SESSION['last_order'] = $orderId;
            redirect(SITE_URL . '/order_success.php');

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Something went wrong. Please try again.';
        }
    }
}
?>

<?php showFlash(); ?>
<h5 class="page-title">Checkout</h5>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card p-4">
            <h6 class="fw-bold mb-4">Delivery Information</h6>
            <form method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Name</label>
                    <input type="text" class="form-control" value="<?= clean($_SESSION['name']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= clean($phone) ?>" placeholder="e.g. 09XX-XXX-XXXX" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Delivery Address <span class="text-danger">*</span></label>
                    <textarea name="address" class="form-control" rows="3" required
                              placeholder="House/Unit No., Street, Barangay, City, Province, ZIP"><?= clean($address) ?></textarea>
                </div>
                <div class="alert alert-info border-0 py-2 small mb-4" style="background:#f0f9ff;color:#0369a1;">
                    Payment Method: <strong>Cash on Delivery (COD)</strong>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    Place Order — <?= price($total) ?>
                </button>
            </form>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card p-4">
            <h6 class="fw-bold mb-3">Order Summary</h6>
            <?php foreach ($cartItems as $item): ?>
                <div class="d-flex justify-content-between small mb-2">
                    <span><?= clean($item['name']) ?> <span class="text-muted">x<?= $item['qty'] ?></span></span>
                    <span><?= price($item['line_total']) ?></span>
                </div>
            <?php endforeach; ?>
            <hr>
            <div class="d-flex justify-content-between small mb-1">
                <span class="text-muted">Subtotal</span><span><?= price($subtotal) ?></span>
            </div>
            <div class="d-flex justify-content-between small mb-3">
                <span class="text-muted">Shipping</span><span><?= price($shipping) ?></span>
            </div>
            <div class="d-flex justify-content-between fw-bold">
                <span>Total</span><span><?= price($total) ?></span>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
