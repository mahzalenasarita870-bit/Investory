# **MahzaGlow - Skincare Inventory Management System**

**MahzaGlow** adalah sistem manajemen inventaris produk *skincare* berbasis web yang dirancang dengan antarmuka **Fintech & SaaS Premium**. Aplikasi ini memudahkan pengelolaan katalog produk, pemantauan stok secara *real-time*, serta pencatatan transaksi barang masuk (*stock in*) dan barang keluar (*stock out*).

---

## **Tampilan Antarmuka Aplikasi**

### **1. Halaman Login**
![Halaman Login](img/login.jpg)

### **2. Dashboard Utama**
![Dashboard Utama](img/dasboard.jpg)

### **3. Katalog Skincare**
![Katalog Skincare](img/katalog.jpg)

### **4. Pencatatan Barang Masuk (IN)**
![Barang Masuk](img/barangmasuk.jpg)

### **5. Pencatatan Barang Keluar (OUT)**
![Barang Keluar](img/barang%20keluar.jpg)

### **6. Formulir Edit Produk**
![Edit Produk](img/edit.jpg)

---

## **Fitur Utama & Spesifikasi Sistem**

| **Kategori** | **Detail Modul / Fitur** |
| :--- | :--- |
| **Dashboard Visual & Analytics** | - **Ringkasan total nilai aset inventaris secara real-time.**<br>- **Indikator varian produk, total stok unit, serta peringatan stok menipis (low stock alert).**<br>- **Filter cepat produk berdasarkan kategori.** |
| **Katalog Produk Terintegrasi** | - **Pencarian produk berdasarkan Nama atau Kode SKU.**<br>- **Tambah, Edit, dan Hapus data produk skincare (Khusus Akses Admin).**<br>- **Penanganan otomatis format harga Rupiah (contoh: `Rp 99.000`).** |
| **Pencatatan Transaksi Stok** | - **Barang Masuk (IN): Menambah stok otomatis saat terjadi restock dari supplier.**<br>- **Barang Keluar (OUT): Mengurangi stok otomatis saat terjadi penjualan.**<br>- **Validasi stok otomatis untuk mencegah pengeluaran melebihi sisa stok.** |
| **Keamanan & Autentikasi** | - **Akses berbasis peran (Role-Based Access: Admin & Staff).**<br>- **Fitur Auto-Recovery Login untuk akun admin.**<br>- **Perhitungan sanitasi input dari kerentanan XSS dan SQL Injection.** |
| **Teknologi yang Digunakan** | - **Backend: PHP 7.4+ (PDO Engine)**<br>- **Database: MySQL / MariaDB**<br>- **Frontend: HTML5, CSS3 Modern (Glassmorphism, Flexbox, CSS Grid)**<br>- **Tipografi: Plus Jakarta Sans (Google Fonts)** |

---

## **Panduan Instalasi & Penggunaan**

| **Langkah / Modul** | **Instruksi Lengkap & Kredensial** |
| :--- | :--- |
| **1. Persyaratan Sistem** | **Pastikan perangkat lokal Anda telah terinstal:**<br>- **Server Lokal: XAMPP / Laragon / WAMP**<br>- **PHP: Versi 7.4 atau yang lebih baru**<br>- **Database: MySQL / MariaDB** |
| **2. Langkah Setup** | **1. Salin folder `inventory-app` ke lokasi server lokal:**<br>&nbsp;&nbsp;&nbsp;&nbsp;`C:/xampp/htdocs/inventory-app/`<br><br>**2. Buka phpMyAdmin (`http://localhost/phpmyadmin/`) lalu buat database baru:**<br>&nbsp;&nbsp;&nbsp;&nbsp;Nama Database: `inventory_db`<br><br>**3. Buat tabel utama yang dibutuhkan (`users`, `categories`, `products`, `stock_movements`).**<br><br>**4. Jalankan modul Apache dan MySQL melalui Control Panel.** |
| **3. Login Pengujian** | **Navigasikan browser ke alamat:**<br>`http://localhost/inventory-app/public/login.php`<br><br>**Kredensial Akun Uji Coba:**<br>- **Username**: `admin`<br>- **Password**: `password`<br>- **Role**: `Admin` (Akses Penuh) |
| **Struktur Folder Proyek** | `inventory-app/`<br>├── `config/db.php` *(Konfigurasi koneksi database PDO)*<br>├── `helpers/` *(auth.php, escape.php, validation.php)*<br>├── `img/` *(Kumpulan gambar screenshot antarmuka UI)*<br>├── `public/` *(assets/style.css, stock/in.php, stock/out.php)*<br>├── `public/` *(create.php, dashboard.php, edit.php, index.php, login.php)*<br>└── `README.md` *(Dokumentasi terstruktur proyek)* |
| **Lisensi & Hak Cipta** | **Dikembangkan untuk MahzaGlow Skincare System. Bebas disesuaikan dan dikembangkan lebih lanjut untuk kebutuhan manajemen inventaris toko/brand skincare.** |
