<?php

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        $val = floatval($angka);
        
        // Jika angka terlalu kecil (kurang dari 1000), kalikan 1000 agar otomatis menjadi nilai ribuan
        if ($val > 0 && $val < 1000) {
            $val = $val * 1000;
        }

        return 'Rp ' . number_format($val, 0, ',', '.');
    }
}