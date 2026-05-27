<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pageTitle = 'Orders';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $allowed = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
    $status  = $_POST['status'];
    $oid     = (int)$_POST['order_id'];
    if (in_array($status, $allowed)) {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $oid]);
        setFlash('success', 'Status updated.');
    }
    // Stay on same view
    $back = isset($_GET['view']) ? '?view=' . (int)$_GET['view'] : '';
    redirect(SITE_URL . '/admin/orders.php' . $back);
}

if (isset($_GET['view'])) {
    $oid  = (int)$_GET['view'];
    $stmt = $pdo->prepare(
        "SELECT o.*, u.name AS customer, u.email
         FROM orders o JOIN users u ON o.user_id = u.id
         WHERE o.id = ?"
    );
    $stmt->execute([$oid]);
    $order = $stmt->fetch();

    if ($order) {
        $items = $pdo->prepare("SELECT * FROM order_details WHERE order_id = ?");
        $items->execute([$oid]);
        $details = $items->fetchAll();

        showFlash();
        ?>
        <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-outline-secondary btn-sm mb-4">← Back to Orders</a>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Order #<?= $order['id'] ?> — <?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></span>
                        <?= statusBadge($order['status']) ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Product</th>
                                    <th class="text-center">Qty</th>
                                    <th>Unit Price</th>
                                    <th class="text-end pe-3">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($details as $d): ?>
                                <tr>
                                    <td class="ps-3"><?= clean($d['product_name']) ?></td>
                                    <td class="text-center"><?= $d['quantity'] ?></td>
                                    <td>&#8369;<?= number_format($d['price'], 2) ?></td>
                                    <td class="text-end pe-3 fw-semibold">
                                        &#8369;<?= number_format($d['price'] * $d['quantity'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="border-top:2px solid #e5e7eb;">
                                <td colspan="3" class="text-end fw-bold ps-3">Order Total</td>
                                <td class="text-end pe-3 fw-bold">&#8369;<?= number_format($order['total'], 2) ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card p-3 mb-3">
                    <h6 class="fw-bold mb-2">Customer</h6>
                    <p class="mb-1 fw-semibold small"><?= clean($order['customer']) ?></p>
                    <p class="mb-1 text-muted small"><?= clean($order['email']) ?></p>
                    <p class="mb-0 text-muted small"><?= clean($order['phone']) ?></p>
                    <hr>
                    <h6 class="fw-bold mb-2">Delivery Address</h6>
                    <p class="small mb-0"><?= nl2br(clean($order['address'])) ?></p>
                </div>

                <div class="card p-3">
                    <h6 class="fw-bold mb-3">Update Status</h6>
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status" class="form-select mb-2">
                            <?php foreach (['Pending','Processing','Shipped','Completed','Cancelled'] as $s): ?>
                                <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
                                    <?= $s ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary w-100">Update</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        require_once 'includes/footer.php';
        exit;
    }
}

$filter = $_GET['status'] ?? '';
$sql    = "SELECT o.*, u.name AS customer FROM orders o JOIN users u ON o.user_id = u.id";
$params = [];
if ($filter) { $sql .= " WHERE o.status = ?"; $params[] = $filter; }
$sql .= " ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

showFlash();
?>

<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="orders.php" class="btn btn-sm <?= !$filter ? 'btn-dark' : 'btn-outline-secondary' ?>">All</a>
    <?php foreach (['Pending','Processing','Shipped','Completed','Cancelled'] as $s): ?>
        <a href="orders.php?status=<?= $s ?>"
           class="btn btn-sm <?= $filter === $s ? 'btn-dark' : 'btn-outline-secondary' ?>">
            <?= $s ?>
        </a>
    <?php endforeach; ?>
    <span class="ms-auto text-muted small align-self-center"><?= count($orders) ?> orders</span>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th class="ps-3">#</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No orders found.</td></tr>
            <?php endif; ?>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td class="ps-3">#<?= $o['id'] ?></td>
                    <td><?= clean($o['customer']) ?></td>
                    <td class="fw-semibold">&#8369;<?= number_format($o['total'], 2) ?></td>
                    <td><?= statusBadge($o['status']) ?></td>
                    <td class="text-muted small"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                    <td class="d-flex gap-2">
                        <a href="orders.php?view=<?= $o['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                        <!-- Quick status dropdown -->
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status" class="form-select form-select-sm" style="width:auto;"
                                    onchange="this.form.submit()">
                                <?php foreach (['Pending','Processing','Shipped','Completed','Cancelled'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>>
                                        <?= $s ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
