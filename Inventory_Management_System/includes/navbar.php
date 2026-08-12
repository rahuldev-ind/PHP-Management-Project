<?php
$current = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
            <i class="bi bi-boxes text-primary"></i> InvenTrack
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'index' ? 'active' : '' ?>" href="index.php">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'products' ? 'active' : '' ?>" href="products.php">
                        <i class="bi bi-box-seam me-1"></i>Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'suppliers' ? 'active' : '' ?>" href="suppliers.php">
                        <i class="bi bi-truck me-1"></i>Suppliers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'orders' ? 'active' : '' ?>" href="orders.php">
                        <i class="bi bi-receipt me-1"></i>Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'categories' ? 'active' : '' ?>" href="categories.php">
                        <i class="bi bi-tags me-1"></i>Categories
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php $flash = getFlash(); if ($flash): ?>
<div class="container-fluid mt-3">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>