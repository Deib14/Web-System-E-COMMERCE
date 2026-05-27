<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pageTitle = 'Categories';
require_once __DIR__ . '/includes/header.php';

// Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name = trim($_POST['name'] ?? '');
    if (strlen($name) >= 1) {
        try {
            $pdo->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$name]);
            setFlash('success', 'Category added.');
        } catch (PDOException $e) {
            setFlash('danger', 'That category already exists.');
        }
    }
    redirect(SITE_URL . '/admin/categories.php');
}

// Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $name = trim($_POST['name'] ?? '');
    $cid  = (int)($_POST['id'] ?? 0);
    if ($name && $cid) {
        $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?")->execute([$name, $cid]);
        setFlash('success', 'Category updated.');
    }
    redirect(SITE_URL . '/admin/categories.php');
}

// Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([(int)$_GET['delete']]);
    setFlash('success', 'Category deleted.');
    redirect(SITE_URL . '/admin/categories.php');
}

$categories = $pdo->query(
    "SELECT c.*, COUNT(p.id) AS total
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id ORDER BY c.name"
)->fetchAll();

showFlash();
?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-4">
            <h6 class="fw-bold mb-3">Add Category</h6>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Electronics" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Add</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Name</th>
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No categories yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td class="ps-3 text-muted small"><?= $c['id'] ?></td>
                            <td>
                                <form method="POST" class="d-flex gap-2 align-items-center">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id"     value="<?= $c['id'] ?>">
                                    <input type="text" name="name" class="form-control form-control-sm"
                                           value="<?= clean($c['name']) ?>" style="max-width:160px;" required>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Save</button>
                                </form>
                            </td>
                            <td class="text-muted small"><?= $c['total'] ?></td>
                            <td>
                                <a href="categories.php?delete=<?= $c['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this category? Products will be uncategorized.')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
