<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/validation.php';

// Pastikan hanya role admin yang boleh menghapus data
requireRole(['admin']);

$productId = validateInt($_GET['id'] ?? null);

if (!$productId) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'ID produk tidak valid.'];
    header('Location: /inventory-app/public/index.php');
    exit;
}

try {
    // Cek keberadaan produk
    $stmtCheck = $pdo->prepare('SELECT name FROM products WHERE id = :id');
    $stmtCheck->execute(['id' => $productId]);
    $product = $stmtCheck->fetch();

    if (!$product) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Produk tidak ditemukan.'];
    } else {
        // Hapus produk dari database
        $stmtDelete = $pdo->prepare('DELETE FROM products WHERE id = :id');
        $stmtDelete->execute(['id' => $productId]);

        $_SESSION['flash'] = [
            'type' => 'success', 
            'msg'  => 'Produk skincare "' . $product['name'] . '" berhasil dihapus.'
        ];
    }
} catch (PDOException $e) {
    // Menangani error jika ada Foreign Key constraint di tabel lain (seperti stock_movements)
    $_SESSION['flash'] = [
        'type' => 'error', 
        'msg'  => 'Gagal menghapus produk: Data produk ini memiliki riwayat transaksi/stok.'
    ];
}

header('Location: /inventory-app/public/index.php');
exit;