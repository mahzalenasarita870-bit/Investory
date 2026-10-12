<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/escape.php';
require_once __DIR__ . '/../../helpers/validation.php';

requireLogin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = validateInt($_POST['product_id'] ?? null);
    $quantity  = validateInt($_POST['quantity'] ?? null, 1);
    $notes     = sanitizeString($_POST['notes'] ?? '');

    if (!$productId) {
        $errors[] = 'Pilih produk yang valid.';
    }
    if (!$quantity) {
        $errors[] = 'Jumlah barang keluar harus minimal 1.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmtCheck = $pdo->prepare('SELECT stock FROM products WHERE id = :id FOR UPDATE');
            $stmtCheck->execute(['id' => $productId]);
            $currentStock = $stmtCheck->fetchColumn();

            if ($currentStock === false || $currentStock < $quantity) {
                $errors[] = 'Stok tidak mencukupi! Stok saat ini: ' . ($currentStock ?: 0);
                $pdo->rollBack();
            } else {
                $stmtInsert = $pdo->prepare('INSERT INTO stock_movements (product_id, user_id, type, quantity, notes) VALUES (:product_id, :user_id, "OUT", :quantity, :notes)');
                $stmtInsert->execute([
                    'product_id' => $productId,
                    'user_id'    => $_SESSION['user']['id'],
                    'quantity'   => $quantity,
                    'notes'      => $notes
                ]);

                $stmtUpdate = $pdo->prepare('UPDATE products SET stock = stock - :qty WHERE id = :id');
                $stmtUpdate->execute(['qty' => $quantity, 'id' => $productId]);

                $pdo->commit();

                header('Location: ../dashboard.php');
                exit;
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}

$products = $pdo->query('SELECT id, name, sku, stock FROM products ORDER BY name ASC')->fetchAll();
$totalKeluarBulanIni = $pdo->query('SELECT SUM(quantity) FROM stock_movements WHERE type="OUT"')->fetchColumn() ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MahzaGlow - Barang Keluar</title>
    <link rel="stylesheet" href="../assets/style.css">
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
                    <a href="../dashboard.php" class="nav-pill-side">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>
                    <a href="../index.php" class="nav-pill-side">
                        <span class="nav-icon">🧴</span> Katalog Skincare
                    </a>
                    <a href="in.php" class="nav-pill-side">
                        <span class="nav-icon">📥</span> Barang Masuk
                    </a>
                    <a href="out.php" class="nav-pill-side active">
                        <span class="nav-icon">📤</span> Barang Keluar
                    </a>
                </nav>

                <div class="sidebar-promo-card">
                    <span style="font-size: 22px;">⚡</span>
                    <h5 style="font-size: 12px; font-weight: 800; color: #38bdf8; margin: 6px 0 2px 0;">Outflow Tracker</h5>
                    <p style="font-size: 10px; color: #94a3b8; font-weight: 600;">Pencatatan Penjualan Real-time</p>
                </div>
            </div>

            <div class="user-profile-badge-side">
                <div class="user-avatar-circle"><?= strtoupper(substr($_SESSION['user']['username'], 0, 1)) ?></div>
                <div style="flex:1;">
                    <p style="font-size:12px; font-weight:800; color:#0f172a; margin:0; line-height:1.2;"><?= e($_SESSION['user']['username']) ?></p>
                    <span style="font-size:9px; color:#64748b; font-weight:700; text-transform:uppercase;"><?= e($_SESSION['user']['role']) ?></span>
                </div>
                <a href="../logout.php" style="font-size:11px; color:#ef4444; font-weight:800; text-decoration:none;">Logout</a>
            </div>
        </div>

        <!-- FORM UTAMA -->
        <div class="soft-card">
            
            <div class="header-badge-banner">
                <div>
                    <span style="font-size:10px; font-weight:800; color:#0284c7; text-transform:uppercase; letter-spacing:0.5px;">FORMULIR PENGELUARAN</span>
                    <h2 style="font-size:20px; font-weight:800; color:#0f172a; margin:2px 0 0 0;">📤 Transaksi Barang Keluar (OUT)</h2>
                </div>
                <a href="../dashboard.php" class="filter-chip" style="text-decoration:none; background:#ffffff;">← Dashboard</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div style="background:#fef2f2; border:1px solid #fee2e2; color:#ef4444; padding:12px; border-radius:14px; margin-bottom:20px; font-size:12px; font-weight:600;">
                    <?php foreach ($errors as $err): ?>
                        <p style="margin:2px 0;">⚠️ <?= e($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div style="margin-bottom:18px;">
                    <label>Pilihan Produk Skincare</label>
                    <select name="product_id" required>
                        <option value="">-- Pilih Produk Skincare --</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (SKU: <?= e($p['sku']) ?> | Tersedia: <?= $p['stock'] ?> unit)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom:18px;">
                    <label>Jumlah Unit Keluar</label>
                    <input type="number" name="quantity" placeholder="Contoh: 5" min="1" required>
                </div>

                <div style="margin-bottom:22px;">
                    <label>Catatan / Nomor Pesanan / Tujuan</label>
                    <textarea name="notes" rows="3" placeholder="Contoh: Pesanan Shopee #12344 / Kirim Toko Cabang Surabaya"></textarea>
                </div>

                <div style="display:flex; gap:12px;">
                    <button type="submit" class="btn-primary-blue" style="flex:2;">Simpan Barang Keluar</button>
                    <a href="../dashboard.php" class="filter-chip" style="flex:1; text-align:center; padding:13px; text-decoration:none; display:inline-block;">Batal</a>
                </div>
            </form>
        </div>

        <!-- PANEL KANAN -->
        <div style="display:flex; flex-direction:column; gap:18px;">
            <div class="glowing-blue-card" style="padding:22px; margin-bottom:0;">
                <span style="font-size:10px; font-weight:800; opacity:0.8; text-transform:uppercase;">TOTAL BARANG KELUAR</span>
                <h3 style="font-size:26px; font-weight:800; margin:6px 0;"><?= $totalKeluarBulanIni ?> Unit</h3>
                <p style="font-size:11px; opacity:0.85;">Akumulasi seluruh transaksi keluar</p>
            </div>

            <div class="soft-card" style="border-left:4px solid #0072ff;">
                <span style="font-size:11px; color:#0072ff; font-weight:800; text-transform:uppercase;">📌 Aturan Validasi Stok</span>
                <ul style="font-size:12px; color:#475569; margin-top:10px; padding-left:16px; line-height:1.6; font-weight:600;">
                    <li>Sistem otomatis menolak transaksi jika stok kurang.</li>
                    <li>Stok produk berkurang secara otomatis.</li>
                    <li>Catat selalu nomor referensi pesanan.</li>
                </ul>
            </div>
        </div>

    </div>
</div>

</body>
</html>