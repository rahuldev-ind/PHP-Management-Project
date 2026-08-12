<?php

function getDashboardStats(PDO $pdo): array {
    $stats = [];
    $stats['total_products'] = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $stats['inventory_value'] = $pdo->query("SELECT COALESCE(SUM(quantity * unit_price),0) FROM products")->fetchColumn();
    $stats['low_stock_count'] = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity <= low_stock_threshold")->fetchColumn();
    $stats['total_suppliers'] = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
    return $stats;
}

function getLowStockProducts(PDO $pdo, int $limit = 5): array {
    return $pdo->query("SELECT * FROM products WHERE quantity <= low_stock_threshold ORDER BY quantity ASC LIMIT $limit")->fetchAll();
}

function getRecentOrders(PDO $pdo, int $limit = 5): array {
    return $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT $limit")->fetchAll();
}

function statusBadge(string $status): string {
    return match($status) {
        'completed' => 'bg-success',
        'pending'   => 'bg-warning text-dark',
        'cancelled' => 'bg-danger',
        default     => 'bg-secondary',
    };
}

function generateSKU(PDO $pdo): string {
    do {
        $sku = 'SKU-' . strtoupper(substr(uniqid(), -6));
        $exists = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = ?");
        $exists->execute([$sku]);
    } while ($exists->fetchColumn() > 0);
    return $sku;
}

function flash(string $type, string $message): void {
    if (!isset($_SESSION)) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (!isset($_SESSION)) session_start();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function sanitize(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}