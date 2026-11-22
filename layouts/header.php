<?php
$pageTitle = $pageTitle ?? 'Dashboard';
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$flash = get_flash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | POS Toko Maju Bersama</title>
    <link rel="stylesheet" href="/pos-toko-maju/assets/css/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="/pos-toko-maju/index.php">TOKO MAJU BERSAMA <span>POS</span></a>
    <div class="topbar-user">
        <span><?= e($_SESSION['name'] ?? '') ?></span>
        <small><?= e(ucfirst($_SESSION['role'] ?? '')) ?></small>
    </div>
</header>
<div class="app-shell">
    <aside class="sidebar">
        <p class="nav-label">MENU UTAMA</p>
        <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="/pos-toko-maju/index.php">Dashboard</a>
        <a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], '/products/') ? 'active' : '' ?>" href="/pos-toko-maju/products/index.php">Produk</a>
        <a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], '/transactions/') ? 'active' : '' ?>" href="/pos-toko-maju/transactions/index.php">Transaksi</a>
        <?php if (($_SESSION['role'] ?? '') === 'administrator'): ?>
            <p class="nav-label">ADMINISTRATOR</p>
            <a class="nav-link <?= $currentPage === 'register.php' ? 'active' : '' ?>" href="/pos-toko-maju/register.php">Registrasi Petugas</a>
        <?php endif; ?>
        <form action="/pos-toko-maju/logout.php" method="post" class="logout-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button class="nav-link logout-button" type="submit">Keluar</button>
        </form>
    </aside>
    <main class="main-content">
        <?php if ($flash): ?>
            <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
