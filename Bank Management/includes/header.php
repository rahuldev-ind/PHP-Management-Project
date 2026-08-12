<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Banking Management System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/banking-system/index.php">🏦 Bank System</a>

        <div class="d-flex">
            <?php if(isset($_SESSION['customer_id']) || isset($_SESSION['admin_id'])): ?>
                <a href="/banking-system/logout.php" class="btn btn-danger btn-sm">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-4">