<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($name)) {
        flash('error', 'Supplier name is required.');
        redirect('suppliers.php?action=' . ($id ? 'edit&id=' . $id : 'add'));
    }

    if ($_POST['form_action'] === 'add') {
        $pdo->prepare("INSERT INTO suppliers (name, contact_person, email, phone, address, notes) VALUES (?,?,?,?,?,?)")
            ->execute([$name, $contact_person, $email, $phone, $address, $notes]);
        flash('success', 'Supplier added!');
        redirect('suppliers.php');
    } elseif ($_POST['form_action'] === 'edit') {
        $sid = (int)$_POST['supplier_id'];
        $pdo->prepare("UPDATE suppliers SET name=?, contact_person=?, email=?, phone=?, address=?, notes=? WHERE id=?")
            ->execute([$name, $contact_person, $email, $phone, $address, $notes, $sid]);
        flash('success', 'Supplier updated!');
        redirect('suppliers.php');
    }
}

if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM suppliers WHERE id = ?")->execute([$id]);
    flash('success', 'Supplier deleted.');
    redirect('suppliers.php');
}

$supplier = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);
    $supplier = $stmt->fetch();
    if (!$supplier) { flash('error', 'Supplier not found.'); redirect('suppliers.php'); }
}

$search = $_GET['search'] ?? '';
$query = "SELECT s.*, COUNT(p.id) AS product_count FROM suppliers s LEFT JOIN products p ON p.supplier_id = s.id WHERE 1=1";
$params = [];
if ($search) { $query .= " AND (s.name LIKE ? OR s.email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$query .= " GROUP BY s.id ORDER BY s.name";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$suppliers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers — InvenTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container-fluid py-4">
<?php if ($action === 'add' || $action === 'edit'): ?>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="fw-semibold mb-0"><?= $action === 'add' ? 'Add Supplier' : 'Edit Supplier' ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="suppliers.php">
                        <input type="hidden" name="form_action" value="<?= $action ?>">
                        <?php if ($action === 'edit'): ?><input type="hidden" name="supplier_id" value="<?= $supplier['id'] ?>"><?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Supplier Name *</label>
                                <input type="text" name="name" class="form-control" required value="<?= sanitize($supplier['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" value="<?= sanitize($supplier['contact_person'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= sanitize($supplier['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= sanitize($supplier['phone'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?= sanitize($supplier['address'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"><?= sanitize($supplier['notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $action === 'add' ? 'Add Supplier' : 'Save Changes' ?></button>
                            <a href="suppliers.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-0">Suppliers</h2>
            <p class="text-muted small mb-0"><?= count($suppliers) ?> supplier(s)</p>
        </div>
        <a href="suppliers.php?action=add" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Supplier</a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" style="max-width:250px" placeholder="Search name or email..." value="<?= sanitize($search) ?>">
                <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                <a href="suppliers.php" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Contact</th><th>Email</th><th>Phone</th><th>Products</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                <?php if (empty($suppliers)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No suppliers found. <a href="suppliers.php?action=add">Add one?</a></td></tr>
                <?php else: ?>
                <?php foreach ($suppliers as $s): ?>
                <tr>
                    <td class="fw-medium"><?= sanitize($s['name']) ?></td>
                    <td><?= sanitize($s['contact_person'] ?: '—') ?></td>
                    <td><?= $s['email'] ? '<a href="mailto:'.sanitize($s['email']).'">'.sanitize($s['email']).'</a>' : '—' ?></td>
                    <td><?= sanitize($s['phone'] ?: '—') ?></td>
                    <td><span class="badge bg-secondary-subtle text-secondary"><?= $s['product_count'] ?></span></td>
                    <td class="text-end">
                        <a href="suppliers.php?action=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $s['id'] ?>, '<?= addslashes($s['name']) ?>')"><i class="bi bi-trash"></i></button>
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
            <div class="modal-body">Delete supplier <strong id="deleteName"></strong>?</div>
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
    document.getElementById('deleteBtn').href = 'suppliers.php?action=delete&id=' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
</body>
</html>