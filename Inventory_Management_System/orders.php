<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Complete or cancel order
if ($action === 'complete' && $id) {
    $order = $pdo->prepare("SELECT * FROM orders WHERE id = ?")->execute([$id]) ? $pdo->prepare("SELECT * FROM orders WHERE id = ?") : null;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch();
    if ($order && $order['status'] === 'pending') {
        $items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $items->execute([$id]);
        $pdo->beginTransaction();
        try {
            foreach ($items->fetchAll() as $item) {
                if ($order['type'] === 'purchase') {
                    $pdo->prepare("UPDATE products SET quantity = quantity + ?, updated_at=CURRENT_TIMESTAMP WHERE id = ?")->execute([$item['quantity'], $item['product_id']]);
                    $pdo->prepare("INSERT INTO stock_movements (product_id, type, quantity, reference, notes) VALUES (?,?,?,?,?)")->execute([$item['product_id'], 'in', $item['quantity'], 'PO-'.str_pad($id,4,'0',STR_PAD_LEFT), 'Purchase order completed']);
                } else {
                    $pdo->prepare("UPDATE products SET quantity = quantity - ?, updated_at=CURRENT_TIMESTAMP WHERE id = ?")->execute([$item['quantity'], $item['product_id']]);
                    $pdo->prepare("INSERT INTO stock_movements (product_id, type, quantity, reference, notes) VALUES (?,?,?,?,?)")->execute([$item['product_id'], 'out', $item['quantity'], 'SO-'.str_pad($id,4,'0',STR_PAD_LEFT), 'Sale order completed']);
                }
            }
            $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?")->execute([$id]);
            $pdo->commit();
            flash('success', 'Order completed and stock updated.');
        } catch (Exception $e) {
            $pdo->rollBack();
            flash('error', 'Error completing order: ' . $e->getMessage());
        }
    }
    redirect('orders.php');
}

if ($action === 'cancel' && $id) {
    $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$id]);
    flash('success', 'Order cancelled.');
    redirect('orders.php');
}

if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$id]);
    flash('success', 'Order deleted.');
    redirect('orders.php');
}

// Handle create order POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action']) && $_POST['form_action'] === 'create_order') {
    $type = $_POST['order_type'] ?? 'purchase';
    $supplier_id = ($type === 'purchase' && !empty($_POST['supplier_id'])) ? (int)$_POST['supplier_id'] : null;
    $customer_name = trim($_POST['customer_name'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['qty'] ?? [];
    $prices = $_POST['price'] ?? [];

    $items = [];
    $total = 0;
    foreach ($product_ids as $i => $pid) {
        $pid = (int)$pid;
        $qty = (int)($quantities[$i] ?? 0);
        $price = (float)($prices[$i] ?? 0);
        if ($pid > 0 && $qty > 0) {
            $items[] = ['product_id' => $pid, 'quantity' => $qty, 'unit_price' => $price, 'subtotal' => $qty * $price];
            $total += $qty * $price;
        }
    }

    if (empty($items)) {
        flash('error', 'Please add at least one item.');
        redirect('orders.php?action=add');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO orders (type, supplier_id, customer_name, notes, total_amount) VALUES (?,?,?,?,?)");
        $stmt->execute([$type, $supplier_id, $customer_name, $notes, $total]);
        $oid = $pdo->lastInsertId();
        foreach ($items as $item) {
            $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (?,?,?,?,?)")
                ->execute([$oid, $item['product_id'], $item['quantity'], $item['unit_price'], $item['subtotal']]);
        }
        $pdo->commit();
        flash('success', 'Order #'.str_pad($oid,4,'0',STR_PAD_LEFT).' created! Mark as complete to update stock.');
        redirect('orders.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        flash('error', 'Error: ' . $e->getMessage());
        redirect('orders.php?action=add');
    }
}

// View single order
$viewOrder = null;
$viewItems = [];
if ($action === 'view' && $id) {
    $stmt = $pdo->prepare("SELECT o.*, s.name AS supplier_name FROM orders o LEFT JOIN suppliers s ON o.supplier_id=s.id WHERE o.id=?");
    $stmt->execute([$id]);
    $viewOrder = $stmt->fetch();
    if ($viewOrder) {
        $iStmt = $pdo->prepare("SELECT oi.*, p.name AS product_name, p.sku FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
        $iStmt->execute([$id]);
        $viewItems = $iStmt->fetchAll();
    }
}

// Fetch for list
$filterType = $_GET['type'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$q = "SELECT o.*, s.name AS supplier_name FROM orders o LEFT JOIN suppliers s ON o.supplier_id=s.id WHERE 1=1";
$params = [];
if ($filterType) { $q .= " AND o.type=?"; $params[] = $filterType; }
if ($filterStatus) { $q .= " AND o.status=?"; $params[] = $filterStatus; }
$q .= " ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($q);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$products = $pdo->query("SELECT * FROM products ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders — InvenTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container-fluid py-4">

<?php if ($action === 'add'): ?>
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="fw-semibold mb-0">Create New Order</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="orders.php" id="orderForm">
                        <input type="hidden" name="form_action" value="create_order">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Order Type *</label>
                                <select name="order_type" id="orderType" class="form-select" required>
                                    <option value="purchase">Purchase (Stock In)</option>
                                    <option value="sale">Sale (Stock Out)</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="supplierField">
                                <label class="form-label fw-medium">Supplier</label>
                                <select name="supplier_id" class="form-select">
                                    <option value="">— Select —</option>
                                    <?php foreach ($suppliers as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= sanitize($s['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4" id="customerField" style="display:none">
                                <label class="form-label fw-medium">Customer Name</label>
                                <input type="text" name="customer_name" class="form-control" placeholder="Customer name...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>

                        <h6 class="fw-semibold mb-3">Order Items</h6>
                        <div id="itemsContainer">
                            <div class="order-item row g-2 align-items-end mb-2">
                                <div class="col-md-5">
                                    <label class="form-label small fw-medium">Product</label>
                                    <select name="product_id[]" class="form-select product-select" required>
                                        <option value="">— Select product —</option>
                                        <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['id'] ?>" data-price="<?= $p['unit_price'] ?>" data-cost="<?= $p['cost_price'] ?>"><?= sanitize($p['name']) ?> (<?= $p['quantity'] ?> in stock)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-medium">Qty</label>
                                    <input type="number" name="qty[]" class="form-control item-qty" min="1" value="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-medium">Unit Price ($)</label>
                                    <input type="number" name="price[]" class="form-control item-price" step="0.01" min="0" value="0" required>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small fw-medium">Total</label>
                                    <div class="form-control-plaintext fw-medium item-total text-success">$0.00</div>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item" style="display:none"><i class="bi bi-x-lg"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button type="button" id="addItem" class="btn btn-sm btn-outline-secondary"><i class="bi bi-plus me-1"></i>Add Item</button>
                            <div class="fw-bold fs-5">Total: <span id="grandTotal" class="text-success">$0.00</span></div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Order</button>
                            <a href="orders.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($action === 'view' && $viewOrder): ?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-semibold mb-0">Order #<?= str_pad($viewOrder['id'],4,'0',STR_PAD_LEFT) ?></h5>
                    <span class="badge <?= statusBadge($viewOrder['status']) ?> fs-6"><?= ucfirst($viewOrder['status']) ?></span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4"><div class="text-muted small">Type</div><div class="fw-medium"><?= ucfirst($viewOrder['type']) ?> Order</div></div>
                        <div class="col-md-4"><div class="text-muted small"><?= $viewOrder['type'] === 'purchase' ? 'Supplier' : 'Customer' ?></div><div class="fw-medium"><?= sanitize($viewOrder['type'] === 'purchase' ? ($viewOrder['supplier_name'] ?: '—') : ($viewOrder['customer_name'] ?: '—')) ?></div></div>
                        <div class="col-md-4"><div class="text-muted small">Date</div><div class="fw-medium"><?= date('M j, Y', strtotime($viewOrder['created_at'])) ?></div></div>
                    </div>
                    <table class="table table-bordered align-middle">
                        <thead class="table-light"><tr><th>Product</th><th>SKU</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr></thead>
                        <tbody>
                        <?php foreach ($viewItems as $item): ?>
                        <tr>
                            <td class="fw-medium"><?= sanitize($item['product_name']) ?></td>
                            <td><code><?= sanitize($item['sku']) ?></code></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>$<?= number_format($item['unit_price'], 2) ?></td>
                            <td>$<?= number_format($item['subtotal'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot><tr><td colspan="4" class="text-end fw-bold">Total</td><td class="fw-bold text-success">$<?= number_format($viewOrder['total_amount'], 2) ?></td></tr></tfoot>
                    </table>
                    <?php if ($viewOrder['notes']): ?><p class="text-muted"><strong>Notes:</strong> <?= sanitize($viewOrder['notes']) ?></p><?php endif; ?>
                    <div class="d-flex gap-2 mt-3">
                        <?php if ($viewOrder['status'] === 'pending'): ?>
                        <a href="orders.php?action=complete&id=<?= $viewOrder['id'] ?>" class="btn btn-success" onclick="return confirm('Complete order and update stock?')"><i class="bi bi-check-circle me-1"></i>Complete & Update Stock</a>
                        <a href="orders.php?action=cancel&id=<?= $viewOrder['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Cancel this order?')">Cancel</a>
                        <?php endif; ?>
                        <a href="orders.php" class="btn btn-outline-secondary">← Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-0">Orders</h2>
            <p class="text-muted small mb-0"><?= count($orders) ?> order(s)</p>
        </div>
        <a href="orders.php?action=add" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Order</a>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex gap-2 flex-wrap">
                <select name="type" class="form-select form-select-sm" style="max-width:140px">
                    <option value="">All Types</option>
                    <option value="purchase" <?= $filterType === 'purchase' ? 'selected' : '' ?>>Purchase</option>
                    <option value="sale" <?= $filterType === 'sale' ? 'selected' : '' ?>>Sale</option>
                </select>
                <select name="status" class="form-select form-select-sm" style="max-width:140px">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-filter"></i> Filter</button>
                <a href="orders.php" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Order #</th><th>Type</th><th>Party</th><th>Total</th><th>Status</th><th>Date</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No orders yet. <a href="orders.php?action=add">Create one?</a></td></tr>
                <?php else: ?>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td class="fw-medium">#<?= str_pad($o['id'],4,'0',STR_PAD_LEFT) ?></td>
                    <td><span class="badge <?= $o['type'] === 'purchase' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' ?>"><?= ucfirst($o['type']) ?></span></td>
                    <td><?= sanitize($o['type'] === 'purchase' ? ($o['supplier_name'] ?: '—') : ($o['customer_name'] ?: '—')) ?></td>
                    <td class="fw-medium text-success">$<?= number_format($o['total_amount'], 2) ?></td>
                    <td><span class="badge <?= statusBadge($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td class="text-muted small"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                    <td class="text-end">
                        <a href="orders.php?action=view&id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-eye"></i></a>
                        <?php if ($o['status'] === 'pending'): ?>
                        <a href="orders.php?action=complete&id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-success me-1" onclick="return confirm('Complete and update stock?')"><i class="bi bi-check-circle"></i></a>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $o['id'] ?>)"><i class="bi bi-trash"></i></button>
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
            <div class="modal-body">Delete this order? This action cannot be undone.</div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="deleteBtn" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(id) {
    document.getElementById('deleteBtn').href = 'orders.php?action=delete&id=' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Order type toggle
const orderType = document.getElementById('orderType');
if (orderType) {
    orderType.addEventListener('change', function() {
        document.getElementById('supplierField').style.display = this.value === 'purchase' ? '' : 'none';
        document.getElementById('customerField').style.display = this.value === 'sale' ? '' : 'none';
    });
}

// Dynamic order items
function updateTotals() {
    let grand = 0;
    document.querySelectorAll('.order-item').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const total = qty * price;
        row.querySelector('.item-total').textContent = '$' + total.toFixed(2);
        grand += total;
    });
    const gt = document.getElementById('grandTotal');
    if (gt) gt.textContent = '$' + grand.toFixed(2);
}

function attachProductListener(row) {
    const select = row.querySelector('.product-select');
    const priceInput = row.querySelector('.item-price');
    const orderTypeEl = document.getElementById('orderType');
    select.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const isPurchase = orderTypeEl && orderTypeEl.value === 'purchase';
        const price = isPurchase ? (parseFloat(opt.dataset.cost) || 0) : (parseFloat(opt.dataset.price) || 0);
        priceInput.value = price.toFixed(2);
        updateTotals();
    });
    row.querySelector('.item-qty').addEventListener('input', updateTotals);
    row.querySelector('.item-price').addEventListener('input', updateTotals);
    row.querySelector('.remove-item').addEventListener('click', function() {
        row.remove();
        updateRemoveButtons();
        updateTotals();
    });
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.order-item');
    rows.forEach((row, idx) => {
        row.querySelector('.remove-item').style.display = rows.length > 1 ? '' : 'none';
    });
}

const addItem = document.getElementById('addItem');
if (addItem) {
    attachProductListener(document.querySelector('.order-item'));
    addItem.addEventListener('click', function() {
        const template = document.querySelector('.order-item').cloneNode(true);
        template.querySelector('.product-select').value = '';
        template.querySelector('.item-qty').value = 1;
        template.querySelector('.item-price').value = '0.00';
        template.querySelector('.item-total').textContent = '$0.00';
        document.getElementById('itemsContainer').appendChild(template);
        attachProductListener(template);
        updateRemoveButtons();
    });
}
</script>
</body>
</html>