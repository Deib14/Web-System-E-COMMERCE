<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? clean($pageTitle) . ' — ' . SITE_NAME : SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>

        *, *::before, *::after { box-sizing: border-box; }
        body {
            background: #fff;
            color: #111;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 0.95rem;
        }

        /* ── Navbar ── */
        .navbar {
            background: #111 !important;
            border-bottom: 1px solid #222;
            padding: 0.65rem 0;
        }
        .navbar-brand {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #fff !important;
        }
        .nav-link { color: rgba(255,255,255,0.75) !important; font-size: 0.88rem; }
        .nav-link:hover, .nav-link.active { color: #fff !important; }
        .navbar-toggler { border-color: rgba(255,255,255,0.3); }
        .navbar-toggler-icon { filter: invert(1); }

        .search-input {
            border: 1px solid #444;
            background: #222;
            color: #fff;
            border-radius: 2px;
            font-size: 0.85rem;
        }
        .search-input::placeholder { color: #888; }
        .search-input:focus { background: #333; color: #fff; border-color: #fff; box-shadow: none; }
        .btn-search {
            background: #fff;
            color: #111;
            border: none;
            border-radius: 2px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .btn-search:hover { background: #e5e7eb; color: #111; }

        .btn-primary   { background: #111; border-color: #111; color: #fff; border-radius: 2px; }
        .btn-primary:hover { background: #333; border-color: #333; color: #fff; }
        .btn-outline-primary { border-color: #111; color: #111; border-radius: 2px; }
        .btn-outline-primary:hover { background: #111; color: #fff; }
        .btn-secondary { background: #555; border-color: #555; border-radius: 2px; }
        .btn-outline-secondary { border-color: #aaa; color: #555; border-radius: 2px; }
        .btn-outline-secondary:hover { background: #f5f5f5; color: #111; border-color: #111; }
        .btn-danger    { background: #c00; border-color: #c00; border-radius: 2px; }
        .btn-outline-danger { border-color: #c00; color: #c00; border-radius: 2px; }
        .btn-outline-danger:hover { background: #c00; color: #fff; }
        .btn-dark      { background: #111; border-color: #111; border-radius: 2px; }
        .btn-outline-dark { border-color: #111; color: #111; border-radius: 2px; }
        .btn-outline-dark:hover { background: #111; color: #fff; }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 2px;
            box-shadow: none;
        }
        .card-header {
            background: #f9f9f9;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
        }

        .product-card { transition: border-color 0.2s; }
        .product-card:hover { border-color: #111; }
        .product-card .card-img-top {
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #e5e7eb;
        }
        .product-card .img-placeholder {
            height: 200px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 2.5rem;
        }

        .table th {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
        }
        .table td { vertical-align: middle; }

        .form-control, .form-select {
            border-radius: 2px;
            border-color: #d1d5db;
            font-size: 0.9rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #111;
            box-shadow: none;
        }

        .alert { border-radius: 2px; font-size: 0.9rem; }
        .alert-success { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .alert-danger  { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

        .badge { font-weight: 500; font-size: 0.75rem; border-radius: 2px; }

        .page-title { font-size: 1.3rem; font-weight: 700; margin-bottom: 1.5rem; }
        .text-price  { font-weight: 700; color: #111; }
        a { color: #111; }
        a:hover { color: #555; }
        .breadcrumb-item a { color: #6b7280; text-decoration: none; }
        .breadcrumb-item a:hover { color: #111; }
        hr { border-color: #e5e7eb; }
    </style>
</head>
<body>


<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?= SITE_URL ?>"><?= SITE_NAME ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <!-- Search -->
            <form class="d-flex mx-auto" style="width:38%;" action="<?= SITE_URL ?>/index.php" method="GET">
                <input class="form-control search-input me-1" type="search" name="search"
                       placeholder="Search products..."
                       value="<?= isset($_GET['search']) ? clean($_GET['search']) : '' ?>">
                <button class="btn btn-search px-3" type="submit">Go</button>
            </form>
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                <li class="nav-item">
                    <a class="nav-link" href="<?= SITE_URL ?>/cart.php">
                        Cart
                        <?php if (cartCount() > 0): ?>
                            <span class="badge bg-white text-dark"><?= cartCount() ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <?= clean($_SESSION['name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="border-radius:2px;font-size:.88rem;">
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>/orders.php">My Orders</a></li>
                            <?php if (isAdmin()): ?>
                                <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/index.php">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
