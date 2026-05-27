<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'Cancelled'")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='Pending'")->fetchColumn();

$recent = $pdo->query(
    "SELECT o.*, u.name AS customer
     FROM orders o
     JOIN users u ON o.user_id = u.id
     ORDER BY o.created_at DESC LIMIT 8"
)->fetchAll();

showFlash();
?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <h2>&#8369;<?= number_format($totalRevenue, 0) ?></h2>
            <p>Revenue</p>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <h2><?= $totalOrders ?></h2>
            <p>Orders
                <?php if ($pendingOrders): ?>
                    <br><span style="color:#b45309;font-size:.7rem;"><?= $pendingOrders ?> pending</span>
                <?php endif; ?>
            </p>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <h2><?= $totalProducts ?></h2>
            <p>Products</p>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <h2><?= $totalUsers ?></h2>
            <p>Customers</p>
        </div>
    </div>
</div>

<!-- Recent orders -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Recent Orders</span>
        <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-sm btn-outline-secondary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th class="ps-3">#</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($recent)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No orders yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($recent as $o): ?>
                <tr>
                    <td class="ps-3">#<?= $o['id'] ?></td>
                    <td><?= clean($o['customer']) ?></td>
                    <td class="fw-semibold">&#8369;<?= number_format($o['total'], 2) ?></td>
                    <td><?= statusBadge($o['status']) ?></td>
                    <td class="text-muted"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                    <td>
                        <a href="<?= SITE_URL ?>/admin/orders.php?view=<?= $o['id'] ?>"
                           class="btn btn-sm btn-outline-secondary">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
