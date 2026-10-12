<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/escape.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skincare Inventory Manager</title>
    <link rel="stylesheet" href="/inventory-app/public/assets/style.css">
</head>
<body>

<?php if (isLoggedIn()): ?>
<div class="navbar">
    <div>
        <strong>SKINCARE MANAGER</strong> &nbsp;|&nbsp;
        <a href="/inventory-app/public/dashboard.php">Dashboard</a>
        <a href="/inventory-app/public/index.php">Daftar Produk</a>
        <a href="/inventory-app/public/stock/in.php">Barang Masuk</a>
        <a href="/inventory-app/public/stock/out.php">Barang Keluar</a>
    </div>
    <div>
        <span>Role: <strong><?= e($_SESSION['user']['role']) ?></strong> (<?= e($_SESSION['user']['username']) ?>)</span> | 
        <a href="/inventory-app/public/logout.php" style="color:#f87171;">Logout</a>
    </div>
</div>
<?php endif; ?>

<div class="container">
    <?php if (isset($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
            <?= e($flash['msg']) ?>
        </div>
    <?php endif; ?>