<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? clean($pageTitle) . ' — Admin' : 'Admin' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background:#f9f9f9; font-family:'Segoe UI',system-ui,sans-serif; font-size:.93rem; }
        .navbar { background:#111 !important; border-bottom:1px solid #222; }
        .navbar-brand { color:#fff !important; font-weight:700; letter-spacing:1px; text-transform:uppercase; font-size:.95rem; }
        .nav-link { color:rgba(255,255,255,.7) !important; font-size:.85rem; }
        .nav-link:hover, .nav-link.active { color:#fff !important; }
        .navbar-toggler { border-color:rgba(255,255,255,.3); }
        .navbar-toggler-icon { filter:invert(1); }

        .card { border:1px solid #e5e7eb; border-radius:2px; box-shadow:none; }
        .card-header { background:#f9f9f9; border-bottom:1px solid #e5e7eb; font-weight:600; }

        .table th { font-size:.75rem; font-weight:600; text-transform:uppercase;
                    letter-spacing:.05em; color:#6b7280; border-bottom:2px solid #e5e7eb; }
        .table td { vertical-align:middle; }

        .btn-primary { background:#111; border-color:#111; border-radius:2px; }
        .btn-primary:hover { background:#333; border-color:#333; }
        .btn-outline-primary { border-color:#111; color:#111; border-radius:2px; }
        .btn-outline-primary:hover { background:#111; color:#fff; }
        .btn-outline-secondary { border-color:#aaa; color:#555; border-radius:2px; }
        .btn-outline-secondary:hover { background:#f5f5f5; color:#111; border-color:#111; }
        .btn-danger { background:#c00; border-color:#c00; border-radius:2px; }
        .btn-outline-danger { border-color:#c00; color:#c00; border-radius:2px; }
        .btn-outline-danger:hover { background:#c00; color:#fff; }

        .form-control, .form-select { border-radius:2px; border-color:#d1d5db; font-size:.9rem; }
        .form-control:focus, .form-select:focus { border-color:#111; box-shadow:none; }

        .alert { border-radius:2px; font-size:.9rem; }
        .badge { border-radius:2px; font-size:.72rem; font-weight:500; }
        .page-title { font-size:1.2rem; font-weight:700; margin-bottom:1.5rem; }

        .stat-card { background:#fff; border:1px solid #e5e7eb; padding:1.25rem; text-align:center; }
        .stat-card h2 { font-size:2rem; font-weight:700; margin-bottom:.1rem; }
        .stat-card p  { color:#6b7280; margin-bottom:0; font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="<?= SITE_URL ?>/admin/index.php">
            <?= SITE_NAME ?> &mdash; Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#anav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="anav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>"
                       href="<?= SITE_URL ?>/admin/index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'product') !== false ? 'active' : '' ?>"
                       href="<?= SITE_URL ?>/admin/products.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'categor') !== false ? 'active' : '' ?>"
                       href="<?= SITE_URL ?>/admin/categories.php">Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'order') !== false ? 'active' : '' ?>"
                       href="<?= SITE_URL ?>/admin/orders.php">Orders</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?= SITE_URL ?>/index.php" target="_blank">View Store</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="<?= SITE_URL ?>/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <?php if (isset($pageTitle)): ?>
        <h5 class="page-title"><?= clean($pageTitle) ?></h5>
    <?php endif; ?>
