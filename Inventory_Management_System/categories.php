<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        flash('error', 'Category name is required.');
        redirect('categories.php?action=' . ($id ? 'edit&id='.$id : 'add'));
    }

    if ($_POST['form_action'] === 'add') {
        try {
            $pdo->prepare("INSERT INTO categories (name, description) VALUES (?,?)")->execute([$name, $description]);
            flash('success', 'Category added!');
        } catch (PDOException $e) {
            flash('error', 'Category name already exists.');
        }
        redirect('categories.php');
    } elseif ($_POST['form_action'] === 'edit') {
        $cid = (int)$_POST['category_id'];
        try {
            $pdo->prepare("UPDATE categories SET name=?, description=? WHERE id=?")->execute([$name, $description, $cid]);
            flash('success', 'Category updated!');
        } catch (PDOException $e) {
            flash('error', 'Category name already exists.');
        }
        redirect('categories.php');
    }
}

if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    flash('success', 'Category deleted.');
    redirect('categories.php');
}

$category = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
}

$categories = $pdo->query("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories — InvenTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container-fluid py-4">
<?php if ($action === 'add' || $action === 'edit'): ?>
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="fw-semibold mb-0"><?= $action === 'add' ? 'Add Category' : 'Edit Category' ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="categories.php">
                        <input type="hidden" name="form_action" value="<?= $action ?>">
                        <?php if ($action === 'edit'): ?><input type="hidden" name="category_id" value="<?= $category['id'] ?>"><?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Category Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?= sanitize($category['name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= sanitize($category['description'] ?? '') ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $action === 'add' ? 'Add' : 'Save' ?></button>
                            <a href="categories.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h2 class="fw-bold mb-0">Categories</h2><p class="text-muted small mb-0"><?= count($categories) ?> categories</p></div>
        <a href="categories.php?action=add" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Category</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Name</th><th>Description</th><th>Products</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                <?php if (empty($categories)): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No categories yet.</td></tr>
                <?php else: ?>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td class="fw-medium"><i class="bi bi-tag me-1 text-muted"></i><?= sanitize($c['name']) ?></td>
                    <td class="text-muted"><?= sanitize($c['description'] ?: '—') ?></td>
                    <td><span class="badge bg-secondary-subtle text-secondary"><?= $c['product_count'] ?></span></td>
                    <td class="text-end">
                        <a href="categories.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $c['id'] ?>, '<?= addslashes($c['name']) ?>')"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0"><h6 class="modal-title fw-semibold">Confirm Delete</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">Delete category <strong id="deleteName"></strong>? Products in this category will be uncategorized.</div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="deleteBtn" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(id, name) {
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteBtn').href = 'categories.php?action=delete&id=' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
</body>
</html>