<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$stats = getDashboardStats($pdo);
$low_stock = getLowStockProducts($pdo);
$recent_orders = getRecentOrders($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvenTrack — Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Dashboard</h2>
            <p class="text-muted small mb-0"><?= date('l, F j, Y') ?></p>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary-soft text-primary me-3">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= number_format($stats['total_products']) ?></div>
                            <div class="stat-label">Total Products</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success-soft text-success me-3">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div>
                            <div class="stat-value">$<?= number_format($stats['inventory_value'], 2) ?></div>
                            <div class="stat-label">Inventory Value</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning-soft text-warning me-3">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= number_format($stats['low_stock_count']) ?></div>
                            <div class="stat-label">Low Stock Alerts</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info-soft text-info me-3">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= number_format($stats['total_suppliers']) ?></div>
                            <div class="stat-label">Suppliers</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Low Stock Alerts -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-semibold mb-0"><i class="bi bi-exclamation-circle text-warning me-2"></i>Low Stock Alerts</h6>
                        <a href="products.php?filter=low_stock" class="btn btn-sm btn-outline-warning">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($low_stock)): ?>
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-check-circle fs-2 text-success d-block mb-2"></i>
                            All products are well-stocked!
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Product</th><th>Stock</th><th>Threshold</th><th>Action</th></tr></thead>
                            <tbody>
                            <?php foreach ($low_stock as $p): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($p['name']) ?></td>
                                <td><span class="badge bg-danger"><?= $p['quantity'] ?></span></td>
                                <td class="text-muted"><?= $p['low_stock_threshold'] ?></td>
                                <td><a href="products.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-xs btn-outline-primary">Restock</a></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-semibold mb-0"><i class="bi bi-receipt text-primary me-2"></i>Recent Orders</h6>
                        <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No orders yet.
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Order #</th><th>Type</th><th>Total</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($recent_orders as $o): ?>
                            <tr>
                                <td class="fw-medium">#<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><span class="badge <?= $o['type'] === 'purchase' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' ?>"><?= ucfirst($o['type']) ?></span></td>
                                <td>$<?= number_format($o['total_amount'], 2) ?></td>
                                <td><span class="badge <?= statusBadge($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>