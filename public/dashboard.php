<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/escape.php';
require_once __DIR__ . '/../helpers/validation.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_create'])) {
    $name       = sanitizeString($_POST['name'] ?? '');
    $categoryId = validateInt($_POST['category_id'] ?? null);
    $price      = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
    $stock      = validateInt($_POST['stock'] ?? 0, 0);
    $sku        = 'SKN-' . sprintf("%03d", rand(1, 999));

    if ($name && $categoryId && $price !== false) {
        $stmtInsert = $pdo->prepare('INSERT INTO products (sku, name, category_id, price, stock, minimum_stock) VALUES (:sku, :name, :cat_id, :price, :stock, 5)');
        $stmtInsert->execute(['sku' => $sku, 'name' => $name, 'cat_id' => $categoryId, 'price' => $price, 'stock' => $stock]);
        header('Location: dashboard.php');
        exit;
    }
}

$search     = sanitizeString($_GET['search'] ?? '');
$categoryId = validateInt($_GET['category_id'] ?? null);

$whereClauses = [];
$params       = [];

if (!empty($search)) {
    $whereClauses[] = 'p.name LIKE :search';
    $params['search'] = '%' . $search . '%';
}
if ($categoryId) {
    $whereClauses[] = 'p.category_id = :cat_id';
    $params['cat_id'] = $categoryId;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$totalValue  = $pdo->query('SELECT SUM(stock * price) FROM products')->fetchColumn() ?? 0;
$totalJenis  = $pdo->query('SELECT COUNT(id) FROM products')->fetchColumn() ?? 0;
$totalUnit   = $pdo->query('SELECT SUM(stock) FROM products')->fetchColumn() ?? 0;
$stokMenipis = $pdo->query('SELECT COUNT(id) FROM products WHERE stock <= 5')->fetchColumn() ?? 0;

$stmtData = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id $whereSql ORDER BY p.id DESC");
$stmtData->execute($params);
$products = $stmtData->fetchAll();

$categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MahzaGlow - Executive Dashboard</title>
    <link rel="stylesheet" href="/inventory-app/public/assets/style.css">
</head>
<body>

<div class="app-board">
    <div class="board-grid-sidebar">
        
        <!-- SIDEBAR KIRI ULTRA PREMIUM -->
        <div class="sidebar-left">
            <div>
                <div class="brand-wrapper">
                    <div class="brand-icon-glow">M</div>
                    <div>
                        <span class="brand-title-text">MahzaGlow</span>
                        <div class="brand-subtitle">Skincare Inventory System</div>
                    </div>
                </div>

                <nav class="side-nav-menu">
                    <a href="dashboard.php" class="nav-pill-side active">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>
                    <a href="index.php" class="nav-pill-side">
                        <span class="nav-icon">🧴</span> Katalog Skincare
                    </a>
                    <a href="stock/in.php" class="nav-pill-side">
                        <span class="nav-icon">📥</span> Barang Masuk
                    </a>
                    <a href="stock/out.php" class="nav-pill-side">
                        <span class="nav-icon">📤</span> Barang Keluar
                    </a>
                </nav>

                <div class="sidebar-promo-card">
                    <span style="font-size: 22px;">✨</span>
                    <h5 style="font-size: 12px; font-weight: 800; color: #1e40af; margin: 6px 0 2px 0;">Live Inventory System</h5>
                    <p style="font-size: 10px; color: #3b82f6; font-weight: 600;">Otomatisasi Stok Terpusat</p>
                </div>
            </div>

            <div class="user-profile-badge-side">
                <div class="user-avatar-circle"><?= strtoupper(substr($_SESSION['user']['username'], 0, 1)) ?></div>
                <div style="flex:1;">
                    <p style="font-size:12px; font-weight:800; color:#0f172a; margin:0; line-height:1.2;"><?= e($_SESSION['user']['username']) ?></p>
                    <span style="font-size:9px; color:#64748b; font-weight:700; text-transform:uppercase;"><?= e($_SESSION['user']['role']) ?></span>
                </div>
                <a href="logout.php" style="font-size:11px; color:#ef4444; font-weight:800; text-decoration:none;">Logout</a>
            </div>
        </div>

        <!-- KOLOM TENGAH -->
        <div>
            <div class="glowing-blue-card">
                <span style="font-size:11px; text-transform:uppercase; opacity:0.8; font-weight:700; letter-spacing:0.5px;">TOTAL NILAI INVENTARIS</span>
                <h2 style="font-size:28px; font-weight:800; margin:8px 0;"><?= formatRupiah($totalValue) ?></h2>
                <div style="font-size:11px; opacity:0.9; font-weight:600; display:flex; justify-content:space-between; align-items:center;">
                    <span>Sistem Sinkronisasi:</span>
                    <span style="background:rgba(255,255,255,0.15); padding:4px 10px; border-radius:20px; font-size:10px; font-weight:700;">● Real-time Engine</span>
                </div>
            </div>

            <div class="category-filter-bar">
                <a href="dashboard.php" class="filter-chip <?= empty($categoryId) ? 'active' : '' ?>">Semua Kategori</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="dashboard.php?category_id=<?= $cat['id'] ?>" class="filter-chip <?= $categoryId == $cat['id'] ? 'active' : '' ?>">
                        <?= e($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="products-cards-layout">
                <?php foreach ($products as $p): ?>
                    <div class="product-widget-card">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="background:#e0f2fe; color:#0284c7; font-size:10px; font-weight:800; padding:4px 8px; border-radius:8px; text-transform:uppercase;"><?= e($p['category_name']) ?></span>
                                <span style="font-size:11px; color:#16a34a; font-weight:700;">🟢 <?= $p['stock'] ?> unit</span>
                            </div>
                            <h4 style="font-size:14px; font-weight:800; color:#0f172a; margin:10px 0 2px 0;"><?= e($p['name']) ?></h4>
                            <div style="font-size:16px; font-weight:800; color:#0072ff; margin-bottom:14px;"><?= formatRupiah($p['price']) ?></div>
                        </div>

                        <div style="display:flex; gap:6px;">
                            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                                <a href="edit.php?id=<?= $p['id'] ?>" style="flex:1; text-align:center; padding:7px; background:#f1f5f9; color:#334155; border-radius:10px; font-size:11px; font-weight:700; text-decoration:none;">Edit</a>
                                <a href="delete.php?id=<?= $p['id'] ?>" onclick="return confirm('Hapus produk?')" style="flex:1; text-align:center; padding:7px; background:#fef2f2; color:#ef4444; border-radius:10px; font-size:11px; font-weight:700; text-decoration:none;">Hapus</a>
                            <?php else: ?>
                                <span style="font-size:10px; color:#94a3b8; text-align:center; width:100%;">View Only Mode</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- KOLOM KANAN -->
        <div style="display:flex; flex-direction:column; gap:18px;">
            <div class="soft-card">
                <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Ringkasan Inventaris</span>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
                    <div>
                        <p style="font-size:24px; font-weight:800; color:#0f172a; margin:0;"><?= $totalJenis ?></p>
                        <span style="font-size:11px; color:#64748b; font-weight:600;">Jenis Skincare</span>
                    </div>
                    <div style="text-align:right;">
                        <p style="font-size:24px; font-weight:800; color:#0072ff; margin:0;"><?= $totalUnit ?></p>
                        <span style="font-size:11px; color:#64748b; font-weight:600;">Total Unit Stok</span>
                    </div>
                </div>
            </div>

            <div class="soft-card" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Stok Menipis</span>
                    <h3 style="font-size:20px; font-weight:800; color:#ef4444; margin-top:2px;"><?= $stokMenipis ?> Produk</h3>
                </div>
                <div style="background:#fef2f2; padding:12px; border-radius:14px; color:#ef4444; font-size:20px;">⚠️</div>
            </div>

            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <div class="soft-card">
                    <h4 style="font-size:13px; font-weight:800; margin-bottom:14px; color:#0f172a;">+ Tambah Skincare Quick</h4>
                    <form method="POST" action="">
                        <input type="hidden" name="action_create" value="1">
                        <label>Nama Produk</label>
                        <input type="text" name="name" placeholder="Nama Produk Skincare" required>
                        <label>Kategori</label>
                        <select name="category_id" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Harga (Rp)</label>
                        <input type="number" name="price" placeholder="85000" required>
                        <label>Jumlah Stok</label>
                        <input type="number" name="stock" placeholder="0" min="0" required>
                        <button type="submit" class="btn-primary-blue" style="margin-top:6px;">Simpan Skincare</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>