<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /inventory-app/public/login.php');
        exit;
    }
}

function requireRole(array $allowedRoles): void {
    requireLogin();
    if (!in_array($_SESSION['user']['role'], $allowedRoles, true)) {
        http_response_code(403);
        echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>
                <h1 style='color:#e53e3e;'>403 Forbidden</h1>
                <p>Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.</p>
                <a href='/inventory-app/public/dashboard.php'>Kembali ke Dashboard</a>
              </div>";
        exit;
    }
}