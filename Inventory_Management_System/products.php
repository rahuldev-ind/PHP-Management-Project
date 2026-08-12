<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = $_POST['category_id'] ? (int)$_POST['category_id'] : null;
    $supplier_id = $_POST['supplier_id'] ? (int)$_POST['supplier_id'] : null;
    $quantity = (int)($_POST['quantity'] ?? 0);
    $unit_price = (float)($_POST['unit_price'] ?? 0);
    $cost_price = (float)($_POST['cost_price'] ?? 0);
    $low_stock_threshold = (int)($_POST['low_stock_threshold'] ?? 10);
    $unit = trim($_POST['unit'] ?? 'pcs');

    if (empty($name) || empty($sku)) {
        flash('error', 'Name and SKU are required.');
        redirect('products.php?action=' . ($id ? 'edit&id=' . $id : 'add'));
    }

    if ($_POST['form_action'] === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (sku, name, description, category_id, supplier_id, quantity, unit_price, cost_price, low_stock_threshold, unit) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$sku, $name, $description, $category_id, $supplier_id, $quantity, $unit_price, $cost_price, $low_stock_threshold, $unit]);
            $newId = $pdo->lastInsertId();
            if ($quantity > 0) {
                $pdo->prepare("INSERT INTO stock_movements (product_id, type, quantity, notes) VALUES (?,?,?,?)")->execute([$newId, 'in', $quantity, 'Initial stock']);
            }
            flash('success', 'Product added successfully!');
            redirect('products.php');
        } catch (PDOException $e) {
            flash('error', 'SKU already exists or error: ' . $e->getMessage());
            redirect('products.php?action=add');
        }
    } elseif ($_POST['form_action'] === 'edit') {
        $pid = (int)$_POST['product_id'];
        $old = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
        $old->execute([$pid]);
        $oldQty = (int)$old->fetchColumn();
        try {
            $stmt = $pdo->prepare("UPDATE products SET sku=?, name=?, description=?, category_id=?, supplier_id=?, quantity=?, unit_price=?, cost_price=?, low_stock_threshold=?, unit=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
            $stmt->execute([$sku, $name, $description, $category_id, $supplier_id, $quantity, $unit_price, $cost_price, $low_stock_threshold, $unit, $pid]);
            $diff = $quantity - $oldQty;
            if ($diff !== 0) {
                $pdo->prepare("INSERT INTO stock_movements (product_id, type, quantity, notes) VALUES (?,?,?,?)")->execute([$pid, $diff > 0 ? 'in' : 'out', abs($diff), 'Manual adjustment']);
            }
            flash('success', 'Product updated successfully!');
            redirect('products.php');
        } catch (PDOException $e) {
            flash('error', 'Error: ' . $e->getMessage());
            redirect('products.php?action=edit&id=' . $pid);
        }
    }
}

// Handle delete
if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    flash('success', 'Product deleted.');
    redirect('products.php');
}

// Fetch categories and suppliers for form
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();

// Edit: load product
$product = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) { flash('error', 'Product not found.'); redirect('products.php'); }
}

// List with filter
$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';
$query = "SELECT p.*, c.name AS category_name, s.name AS supplier_name FROM products p LEFT JOIN categories c ON p.category_id=c.id LEFT JOIN suppliers s ON p.supplier_id=s.id WHERE 1=1";
$params = [];
if ($filter === 'low_stock') { $query .= " AND p.quantity <= p.low_stock_threshold"; }
if ($search) { $query .= " AND (p.name LIKE ? OR p.sku LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$query .= " ORDER BY p.name ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

$suggestedSKU = generateSKU($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products — InvenTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container-fluid py-4">

<?php if ($action === 'add' || $action === 'edit'): ?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="fw-semibold mb-0"><?= $action === 'add' ? 'Add New Product' : 'Edit Product' ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="products.php">
                        <input type="hidden" name="form_action" value="<?= $action ?>">
                        <?php if ($action === 'edit'): ?><input type="hidden" name="product_id" value="<?= $product['id'] ?>"><?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-medium">Product Name *</label>
                                <input type="text" name="name" class="form-control" required value="<?= sanitize($product['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">SKU *</label>
                                <input type="text" name="sku" class="form-control" required value="<?= sanitize($product['sku'] ?? $suggestedSKU) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Description</label>
                                <textarea name="description" class="form-control" rows="2"><?= sanitize($product['description'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">— None —</option>
                                    <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($product['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Supplier</label>
                                <select name="supplier_id" class="form-select">
                                    <option value="">— None —</option>
                                    <?php foreach ($suppliers as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= ($product['supplier_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= sanitize($s['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium">Quantity</label>
                                <input type="number" name="quantity" class="form-control" min="0" value="<?= $product['quantity'] ?? 0 ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-medium">Unit</label>
                                <input type="text" name="unit" class="form-control" value="<?= sanitize($product['unit'] ?? 'pcs') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium">Sale Price ($)</label>
                                <input type="number" name="unit_price" class="form-control" step="0.01" min="0" value="<?= $product['unit_price'] ?? 0 ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-medium">Cost Price ($)</label>
                                <input type="number" name="cost_price" class="form-control" step="0.01" min="0" value="<?= $product['cost_price'] ?? 0 ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-medium">Low Stock Alert</label>
                                <input type="number" name="low_stock_threshold" class="form-control" min="0" value="<?= $product['low_stock_threshold'] ?? 10 ?>">
                            </div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $action === 'add' ? 'Add Product' : 'Save Changes' ?></button>
                            <a href="products.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-0">Products</h2>
            <p class="text-muted small mb-0"><?= count($products) ?> product(s) found</p>
        </div>
        <a href="products.php?action=add" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Product</a>
    </div>

    <!-- Search & Filter -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                <input type="text" name="search" class="form-control form-control-sm" style="max-width:250px" placeholder="Search name or SKU..." value="<?= sanitize($search) ?>">
                <select name="filter" class="form-select form-select-sm" style="max-width:160px">
                    <option value="" <?= !$filter ? 'selected' : '' ?>>All Products</option>
                    <option value="low_stock" <?= $filter === 'low_stock' ? 'selected' : '' ?>>Low Stock Only</option>
                </select>
                <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-search"></i> Filter</button>
                <a href="products.php" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Stock</th>
                        <th>Sale Price</th>
                        <th>Cost Price</th>
                        <th>Value</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No products found. <a href="products.php?action=add">Add one?</a></td></tr>
                <?php else: ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><code><?= sanitize($p['sku']) ?></code></td>
                    <td class="fw-medium"><?= sanitize($p['name']) ?></td>
                    <td><?= sanitize($p['category_name'] ?? '—') ?></td>
                    <td><?= sanitize($p['supplier_name'] ?? '—') ?></td>
                    <td>
                        <?php if ($p['quantity'] <= $p['low_stock_threshold']): ?>
                            <span class="badge bg-danger"><i class="bi bi-exclamation-circle me-1"></i><?= $p['quantity'] ?> <?= sanitize($p['unit']) ?></span>
                        <?php else: ?>
                            <span class="badge bg-success-subtle text-success"><?= $p['quantity'] ?> <?= sanitize($p['unit']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>$<?= number_format($p['unit_price'], 2) ?></td>
                    <td>$<?= number_format($p['cost_price'], 2) ?></td>
                    <td>$<?= number_format($p['quantity'] * $p['unit_price'], 2) ?></td>
                    <td class="text-end">
                        <a href="products.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $p['id'] ?>, '<?= addslashes($p['name']) ?>')"><i class="bi bi-trash"></i></button>
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

<!-- Delete confirmation modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-semibold">Confirm Delete</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteProductName"></strong>? This cannot be undone.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="deleteConfirmBtn" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(id, name) {
    document.getElementById('deleteProductName').textContent = name;
    document.getElementById('deleteConfirmBtn').href = 'products.php?action=delete&id=' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
</body>
</html>