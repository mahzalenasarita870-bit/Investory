<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/escape.php';
require_once __DIR__ . '/../helpers/validation.php';

requireLogin();

$search     = sanitizeString($_GET['search'] ?? '');
$categoryId = validateInt($_GET['category_id'] ?? null);

$whereClauses = [];
$params       = [];

if (!empty($search)) {
    $whereClauses[] = '(p.name LIKE :search1 OR p.sku LIKE :search2)';
    $params['search1'] = '%' . $search . '%';
    $params['search2'] = '%' . $search . '%';
}

if ($categoryId) {
    $whereClauses[] = 'p.category_id = :cat_id';
    $params['cat_id'] = $categoryId;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$stmtData = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id $whereSql ORDER BY p.name ASC");
$stmtData->execute($params);
$products = $stmtData->fetchAll();

$categoriesList = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();
$totalProduk = count($products);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MahzaGlow - Katalog Skincare</title>
    <link rel="stylesheet" href="/inventory-app/public/assets/style.css">
</head>
<body>

<div class="app-board">
    <div class="board-grid-sidebar">
        
        <!-- SIDEBAR KIRI -->
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
                    <a href="dashboard.php" class="nav-pill-side">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>
                    <a href="index.php" class="nav-pill-side active">
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
                    <h5 style="font-size: 12px; font-weight: 800; color: #1e40af; margin: 6px 0 2px 0;">Katalog Terintegrasi</h5>
                    <p style="font-size: 10px; color: #3b82f6; font-weight: 600;">Menampilkan seluruh varian skincare</p>
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

        <!-- KOLOM TENGAH: KATALOG UTAMA -->
        <div style="grid-column: span 2;">
            <div class="soft-card" style="margin-bottom: 20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <div>
                        <h2 style="font-size:20px; font-weight:800; color:#0f172a; margin:0;">Katalog Produk Skincare</h2>
                        <span style="font-size:12px; color:#64748b; font-weight:600;">Total <?= $totalProduk ?> varian terdaftar</span>
                    </div>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <a href="create.php" class="btn-primary-blue" style="width:auto; padding:10px 18px;">+ Tambah Produk Baru</a>
                    <?php endif; ?>
                </div>

                <!-- Form Filter & Pencarian -->
                <form method="GET" action="" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                    <div style="flex:2; min-width:220px;">
                        <input type="text" name="search" style="margin:0;" placeholder="Cari nama atau SKU skincare..." value="<?= e($search) ?>">
                    </div>
                    <div style="flex:1; min-width:160px;">
                        <select name="category_id" style="margin:0;">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categoriesList as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary-blue" style="width:auto; padding:11px 22px;">Cari</button>
                    <a href="index.php" class="filter-chip" style="padding:11px 18px; text-decoration:none;">Reset</a>
                </form>
            </div>

            <!-- Product Cards Layout -->
            <div class="products-cards-layout">
                <?php if (empty($products)): ?>
                    <div class="soft-card" style="grid-column: span 2; text-align:center; padding:40px 20px;">
                        <p style="color:#64748b; font-size:14px; font-weight:600;">Data produk skincare tidak ditemukan.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <div class="product-widget-card">
                            <div>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span style="background:#e0f2fe; color:#0284c7; font-size:10px; font-weight:800; padding:4px 8px; border-radius:8px; text-transform:uppercase;"><?= e($p['category_name']) ?></span>
                                    <span style="font-size:11px; color:#16a34a; font-weight:700;">🟢 <?= $p['stock'] ?> unit</span>
                                </div>
                                <h4 style="font-size:14px; font-weight:800; color:#0f172a; margin:10px 0 2px 0;"><?= e($p['name']) ?></h4>
                                <span style="font-size:10px; color:#94a3b8; font-weight:700; display:block; margin-bottom:6px;"><?= e($p['sku']) ?></span>
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
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

</body>
</html>