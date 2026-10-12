<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/escape.php';
require_once __DIR__ . '/../helpers/validation.php';

requireRole(['admin']);

$errors       = [];
$sku          = '';
$name         = '';
$categoryId   = '';
$price        = '';
$stock        = '0';
$minimumStock = '5';

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
        $stmtCheck = $pdo->prepare('SELECT COUNT(*) FROM products WHERE sku = :sku');
        $stmtCheck->execute(['sku' => $sku]);
        if ($stmtCheck->fetchColumn() > 0) {
            $errors[] = 'SKU sudah terdaftar.';
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
        $stmtInsert = $pdo->prepare('
            INSERT INTO products (sku, name, category_id, price, stock, minimum_stock) 
            VALUES (:sku, :name, :cat_id, :price, :stock, :min_stock)
        ');
        $stmtInsert->execute([
            'sku'       => $sku,
            'name'      => $name,
            'cat_id'    => $categoryId,
            'price'     => $price,
            'stock'     => $stock,
            'min_stock' => $minimumStock
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Produk skincare berhasil ditambahkan!'];
        header('Location: /inventory-app/public/index.php');
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
    <title>MahzaGlow - Tambah Produk Baru</title>
    <link rel="stylesheet" href="/inventory-app/public/assets/style.css">
</head>
<body>

<div class="app-board">
    <!-- TOP NAVBAR -->
    <div class="top-navbar">
        <div class="brand-wrapper">
            <div class="brand-icon-glow">M</div>
            <span class="brand-title-text">MahzaGlow</span>
        </div>

        <a href="/inventory-app/public/index.php" class="btn-secondary">← Kembali ke Katalog</a>
    </div>

    <!-- FORM BOARD CARD -->
    <div class="form-single-board">
        <h2 style="font-size:20px; font-weight:800; margin-bottom:4px; color:#0f172a;">Tambah Produk Skincare Baru</h2>
        <p style="color:#64748b; font-size:12px; margin-bottom:20px;">Lengkapi formulir di bawah ini untuk menambahkan stok item baru.</p>

        <?php if (!empty($errors)): ?>
            <div style="background:#fef2f2; border:1px solid #fee2e2; color:#ef4444; padding:10px; border-radius:10px; margin-bottom:15px; font-size:12px;">
                <?php foreach ($errors as $err): ?>
                    <p style="margin:2px 0;"><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>SKU (Kode Produk)</label>
            <input type="text" name="sku" value="<?= e($sku) ?>" placeholder="Contoh: FW-001" required>

            <label>Nama Produk Skincare</label>
            <input type="text" name="name" value="<?= e($name) ?>" placeholder="Contoh: Gentle Facial Wash 100ml" required>

            <label>Kategori</label>
            <select name="category_id" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categoriesList as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Harga (Rp)</label>
            <input type="number" step="0.01" name="price" value="<?= e($price) ?>" placeholder="85000" required>

            <label>Stok Awal</label>
            <input type="number" name="stock" value="<?= e($stock) ?>" min="0" required>

            <label>Batas Stok Minimal</label>
            <input type="number" name="minimum_stock" value="<?= e($minimumStock) ?>" min="0" required>

            <div style="display:flex; gap:10px; margin-top:10px;">
                <button type="submit" class="btn-primary-blue">Simpan Produk</button>
                <a href="/inventory-app/public/index.php" class="btn-secondary" style="text-align:center;">Batal</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>