<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add' && isset($_POST['product_id'])) {
    $pid = (int)$_POST['product_id'];
    $qty = max(1, (int)($_POST['quantity'] ?? 1));

    $stmt = $pdo->prepare("SELECT id, stock FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    $prod = $stmt->fetch();

    if ($prod && $prod['stock'] > 0) {
        $current = $_SESSION['cart'][$pid] ?? 0;
        $_SESSION['cart'][$pid] = min($current + $qty, $prod['stock']);
        setFlash('success', 'Item added to cart.');
    } else {
        setFlash('danger', 'Product is not available.');
    }
    redirect(SITE_URL . '/cart.php');
}

if ($action === 'update' && isset($_POST['qty']) && is_array($_POST['qty'])) {
    foreach ($_POST['qty'] as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$pid]);
        } else {
            $_SESSION['cart'][$pid] = $qty;
        }
    }
    setFlash('success', 'Cart updated.');
    redirect(SITE_URL . '/cart.php');
}

if ($action === 'remove' && isset($_GET['id'])) {
    $pid = (int)$_GET['id'];
    unset($_SESSION['cart'][$pid]);
    setFlash('success', 'Item removed from cart.');
    redirect(SITE_URL . '/cart.php');
}

// CLEAR entire cart
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    setFlash('success', 'Cart cleared.');
    redirect(SITE_URL . '/cart.php');
}

$cartItems = [];
$subtotal  = 0;

if (!empty($_SESSION['cart'])) {
    $ids  = array_map('intval', array_keys($_SESSION['cart']));
    $in   = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll(); 

    $productMap = [];
    foreach ($rows as $row) {
        $productMap[$row['id']] = $row;
    }


    foreach ($_SESSION['cart'] as $pid => $qty) {
        $pid = (int)$pid;
        if (!isset($productMap[$pid])) continue; 

        $item              = $productMap[$pid];
        $item['qty']       = $qty;
        $item['line_total']= $item['price'] * $qty;
        $subtotal         += $item['line_total'];
        $cartItems[]       = $item;
    }
}

 $shipping = $subtotal > 0 ? 99.00 : 0.00;
 $total    = $subtotal + $shipping;
 
 $pageTitle = 'Cart';
 require_once 'includes/header.php';
?>

<?php showFlash(); ?>

<h5 class="page-title">
    Shopping Cart
    <span class="text-muted fw-normal fs-6">(<?= cartCount() ?> item<?= cartCount() != 1 ? 's' : '' ?>)</span>
</h5>

<?php if (empty($cartItems)): ?>

    <div class="text-center py-5 border" style="color:#9ca3af;">
        <p class="mb-1" style="font-size:2rem;">&#128722;</p>
        <p class="mb-3">Your cart is empty.</p>
        <a href="index.php" class="btn btn-primary btn-sm">Continue Shopping</a>
    </div>

<?php else: ?>
    <div class="row g-4">


        <div class="col-lg-8">
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($cartItems as $item):
                                $imgFile = __DIR__ . '/uploads/' . $item['image'];
                                $hasImg  = $item['image'] !== 'no-image.png' && file_exists($imgFile);
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($hasImg): ?>
                                            <img src="<?= SITE_URL ?>/uploads/<?= clean($item['image']) ?>"
                                                 width="50" height="50" style="object-fit:cover;border:1px solid #e5e7eb;">
                                        <?php else: ?>
                                            <div style="width:50px;height:50px;background:#f3f4f6;border:1px solid #e5e7eb;
                                                        display:flex;align-items:center;justify-content:center;color:#9ca3af;">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        <a href="product.php?id=<?= $item['id'] ?>"
                                           class="text-decoration-none fw-semibold text-dark">
                                            <?= clean($item['name']) ?>
                                        </a>
                                    </div>
                                </td>
                                <td><?= price($item['price']) ?></td>
                                <td>
                                    <input type="number"
                                           name="qty[<?= $item['id'] ?>]"
                                           value="<?= $item['qty'] ?>"
                                           min="0"
                                           max="<?= $item['stock'] ?>"
                                           class="form-control form-control-sm"
                                           style="width:70px;">
                                </td>
                                <td class="fw-semibold"><?= price($item['line_total']) ?></td>
                                <td>
                                    <a href="cart.php?action=remove&id=<?= $item['id'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Remove this item?')">
                                        &#215;
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-2 d-flex justify-content-between">
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">&#8592; Continue Shopping</a>
                    <div class="d-flex gap-2">
                        <a href="cart.php?action=clear"
                           class="btn btn-outline-danger btn-sm"
                           onclick="return confirm('Clear entire cart?')">Clear Cart</a>
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Update Cart</button>
                    </div>
                </div>
            </form>
        </div>


        <div class="col-lg-4">
            <div class="card p-4">
                <h6 class="fw-bold mb-3">Order Summary</h6>
                <div class="d-flex justify-content-between mb-2 text-muted small">
                    <span>Subtotal</span><span><?= price($subtotal) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3 text-muted small">
                    <span>Shipping</span><span><?= price($shipping) ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold mb-4">
                    <span>Total</span><span><?= price($total) ?></span>
                </div>
                <?php if (isLoggedIn()): ?>
                    <a href="checkout.php" class="btn btn-primary w-100">Proceed to Checkout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary w-100">Login to Checkout</a>
                <?php endif; ?>
            </div>
        </div>

    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
