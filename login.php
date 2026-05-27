<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

if (isLoggedIn()) redirect(SITE_URL . '/index.php');

$pageTitle = 'Login';
require_once 'includes/header.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']       ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];

        redirect($user['role'] === 'admin'
            ? SITE_URL . '/admin/index.php'
            : SITE_URL . '/index.php');
    } else {
        $error = 'Invalid email or password.';
    }
}
?>

<?php showFlash(); ?>

<div class="row justify-content-center">
<div class="col-md-5 col-lg-4">

<h5 class="page-title">Login</h5>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= clean($error) ?></div>
<?php endif; ?>

<div class="card p-4">
    <form method="POST" novalidate>
        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= clean($email) ?>" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>

<p class="text-center mt-3 text-muted small">
    No account? <a href="register.php">Register</a>
</p>

</div>
</div>

<?php require_once 'includes/footer.php'; ?>
