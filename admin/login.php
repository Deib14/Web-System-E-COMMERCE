<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (isAdmin()) redirect(SITE_URL . '/admin/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']       ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];
        redirect(SITE_URL . '/admin/index.php');
    } else {
        $error = 'Invalid admin credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background:#111; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .login-box { background:#fff; padding:2rem; width:100%; max-width:360px; }
        .form-control { border-radius:2px; }
        .form-control:focus { border-color:#111; box-shadow:none; }
        .btn-dark { border-radius:2px; }
        label { font-size:.88rem; font-weight:600; }
    </style>
</head>
<body>
<div class="login-box">
    <h5 class="fw-bold mb-1" style="letter-spacing:1px;text-transform:uppercase;"><?= SITE_NAME ?></h5>
    <p class="text-muted small mb-4">Admin Panel</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small" style="border-radius:2px;"><?= clean($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-dark w-100">Login</button>
    </form>
    <div class="text-center mt-3">
        <a href="<?= SITE_URL ?>/index.php" class="text-muted small text-decoration-none">← Back to Store</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
