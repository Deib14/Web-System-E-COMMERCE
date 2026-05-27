<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

if (isLoggedIn()) redirect(SITE_URL . '/index.php');

$pageTitle = 'Register';
require_once 'includes/header.php';

$errors = [];
$name   = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']       ?? '';
    $confirm  = $_POST['confirm']        ?? '';

    // Validation
    if (strlen($name) < 2)                        $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 6)                    $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)                   $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'That email is already registered.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            setFlash('success', 'Account created. You can now log in.');
            redirect(SITE_URL . '/login.php');
        }
    }
}
?>

<div class="row justify-content-center">
<div class="col-md-5 col-lg-4">

<h5 class="page-title">Create Account</h5>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card p-4">
    <form method="POST" novalidate>
        <div class="mb-3">
            <label class="form-label fw-semibold">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= clean($name) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" value="<?= clean($email) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" required>
            <div class="form-text">Minimum 6 characters.</div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Confirm Password</label>
            <input type="password" name="confirm" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
</div>

<p class="text-center mt-3 text-muted small">
    Already have an account? <a href="login.php">Login</a>
</p>

</div>
</div>

<?php require_once 'includes/footer.php'; ?>
