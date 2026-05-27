<?php

require_once 'config/db.php';

$email    = 'admin@ecommerce.com';
$password = 'admin123';
$name     = 'Admin';

$check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$check->execute([$email]);

$hash = password_hash($password, PASSWORD_DEFAULT);

if ($check->fetch()) {
    $pdo->prepare("UPDATE users SET password=?, role='admin', name=? WHERE email=?")
        ->execute([$hash, $name, $email]);
    $msg = 'Admin account updated.';
} else {
    $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,'admin')")
        ->execute([$name, $email, $hash]);
    $msg = 'Admin account created.';
}

if (!is_dir('uploads')) mkdir('uploads', 0755, true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>body{background:#111;} .box{background:#fff;padding:2rem;max-width:400px;width:100%;}</style>
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh;">
<div class="box">
    <h5 class="fw-bold mb-3">Setup Complete</h5>
    <div class="alert alert-success py-2"><?= $msg ?></div>
    <p class="mb-1 small"><strong>Email:</strong> <?= $email ?></p>
    <p class="mb-3 small"><strong>Password:</strong> <?= $password ?></p>
    <div class="alert alert-warning py-2 small">Delete <code>setup.php</code> after setup!</div>
    <a href="admin/login.php" class="btn btn-dark me-2">Admin Login</a>
    <a href="index.php" class="btn btn-outline-secondary">View Store</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
