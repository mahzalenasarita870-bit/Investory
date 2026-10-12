<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/escape.php';
require_once __DIR__ . '/../helpers/validation.php';

requireRole(['admin']);

$id = validateInt($_GET['id'] ?? null);

if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

$errors = [];
$sku          = $product['sku'];
$name         = $product['name'];
$categoryId   = $product['category_id'];
$price        = $product['price'];
$stock        = $product['stock'];
$minimumStock = $product['minimum_stock'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku          = sanitizeString($_POST['sku'] ?? '');
    $name         = sanitizeString($_POST['name'] ?? '');
    $categoryId   = validateInt($_POST['category_id'] ?? null);
    $price        = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
    $stock        = validateInt($_POST['stock'] ?? 0, 0);
    $minimumStock = validateInt($_POST['minimum_stock'] ?? 0, 0);

    if (empty($sku)) {
        $errors[] = 'SKU wajib diisi.';
    } else {
        $stmtCheck = $pdo->prepare('SELECT COUNT(*) FROM products WHERE sku = :sku AND id != :id');
        $stmtCheck->execute(['sku' => $sku, 'id' => $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            $errors[] = 'SKU sudah digunakan oleh produk lain.';
        }
    }

    if (empty($name)) {
        $errors[] = 'Nama produk wajib diisi.';
    }

    if (!$categoryId) {
        $errors[] = 'Pilih kategori yang valid.';
    }

    if ($price === false || $price < 0) {
        $errors[] = 'Harga produk tidak valid.';
    }

    if (empty($errors)) {
        $stmtUpdate = $pdo->prepare('
            UPDATE products 
            SET sku = :sku, name = :name, category_id = :cat_id, price = :price, stock = :stock, minimum_stock = :min_stock 
            WHERE id = :id
        ');
        $stmtUpdate->execute([
            'sku'       => $sku,
            'name'      => $name,
            'cat_id'    => $categoryId,
            'price'     => $price,
            'stock'     => $stock,
            'min_stock' => $minimumStock,
            'id'        => $id
        ]);

        header('Location: index.php');
        exit;
    }
}

$categoriesList = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MahzaGlow - Edit Skincare</title>
    <link rel="stylesheet" href="assets/style.css">
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
                    <span style="font-size: 22px;">✏️</span>
                    <h5 style="font-size: 12px; font-weight: 800; color: #38bdf8; margin: 6px 0 2px 0;">Mode Perubahan</h5>
                    <p style="font-size: 10px; color: #94a3b8; font-weight: 600;">Perbarui Detail Produk</p>
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

        <!-- FORM EDIT UTAMA -->
        <div class="soft-card">
            <div class="header-badge-banner">
                <div>
                    <span style="font-size:10px; font-weight:800; color:#0284c7; text-transform:uppercase; letter-spacing:0.5px;">EDIT KATALOG</span>
                    <h2 style="font-size:20px; font-weight:800; color:#0f172a; margin:2px 0 0 0;">✏️ Edit Produk Skincare</h2>
                </div>
                <a href="index.php" class="filter-chip" style="text-decoration:none; background:#ffffff;">← Kembali ke Katalog</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div style="background:#fef2f2; border:1px solid #fee2e2; color:#ef4444; padding:12px; border-radius:14px; margin-bottom:20px; font-size:12px; font-weight:600;">
                    <?php foreach ($errors as $err): ?>
                        <p style="margin:2px 0;">⚠️ <?= e($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <label>SKU (Kode Produk)</label>
                <input type="text" name="sku" value="<?= e($sku) ?>" required>

                <label>Nama Produk Skincare</label>
                <input type="text" name="name" value="<?= e($name) ?>" required>

                <label>Kategori</label>
                <select name="category_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categoriesList as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Harga (Rp)</label>
                <input type="number" step="0.01" name="price" value="<?= e($price) ?>" required>

                <label>Jumlah Stok</label>
                <input type="number" name="stock" value="<?= e($stock) ?>" min="0" required>

                <label>Batas Stok Minimal</label>
                <input type="number" name="minimum_stock" value="<?= e($minimumStock) ?>" min="0" required>

                <div style="display:flex; gap:12px; margin-top:10px;">
                    <button type="submit" class="btn-primary-blue" style="flex:2;">Simpan Perubahan</button>
                    <a href="index.php" class="filter-chip" style="flex:1; text-align:center; padding:13px; text-decoration:none; display:inline-block;">Batal</a>
                </div>
            </form>
        </div>

        <!-- PANEL KANAN -->
        <div style="display:flex; flex-direction:column; gap:18px;">
            <div class="soft-card" style="border-left:4px solid #0072ff;">
                <span style="font-size:11px; color:#0072ff; font-weight:800; text-transform:uppercase;">ℹ️ Informasi Produk</span>
                <p style="font-size:12px; color:#475569; margin-top:10px; line-height:1.6; font-weight:600;">
                    Perubahan nama, harga, atau kategori akan berdampak langsung pada tampilan katalog dan perhitungan nilai total inventaris[cite: 16].
                </p>
            </div>
        </div>

    </div>
</div>

</body>
</html>