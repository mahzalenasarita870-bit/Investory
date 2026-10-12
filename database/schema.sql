DROP DATABASE IF EXISTS schema_db;
CREATE DATABASE schema_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE schema_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    minimum_stock INT NOT NULL DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE product_suppliers (
    product_id INT NOT NULL,
    supplier_id INT NOT NULL,
    supplier_price DECIMAL(12, 2),
    PRIMARY KEY (product_id, supplier_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('IN', 'OUT') NOT NULL,
    quantity INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

USE schema_db;

-- Reset ulang data akun admin dan staff
DELETE FROM users WHERE username IN ('admin', 'staff');

INSERT INTO users (id, username, password_hash, role) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff');

INSERT INTO user_profiles (user_id, full_name, email) VALUES
(1, 'Administrator Skincare', 'admin@skincare.local'),
(2, 'Staff Gudang Skincare', 'staff@skincare.local');

-- 2. Seed Kategori Skincare
INSERT INTO categories (id, name, description) VALUES
(1, 'Facial Wash & Cleanser', 'Pembersih wajah harian dan double cleansing'),
(2, 'Serum & Essence', 'Perawatan konsentrat untuk masalah kulit spesifik'),
(3, 'Moisturizer', 'Pelembap wajah untuk menjaga kelembapan kulit'),
(4, 'Sunscreen', 'Pelindung sinar UV matahari');

-- 3. Seed Produk Skincare
INSERT INTO products (id, category_id, sku, name, price, stock, minimum_stock) VALUES
(1, 1, 'SKN-001', 'Gentle Low pH Facial Wash 100ml', 55000.00, 20, 5),
(2, 2, 'SKN-002', 'Niacinamide 10% Brightening Serum 20ml', 89000.00, 3, 5),
(3, 3, 'SKN-003', 'Centella Calming Gel Moisturizer 50g', 115000.00, 15, 5),
(4, 4, 'SKN-004', 'UV Shield Physical Sunscreen SPF 50+ 30ml', 79000.00, 2, 8),
(5, 1, 'SKN-005', 'Micellar Water Deep Cleansing 250ml', 48000.00, 30, 10);

-- 4. Seed Supplier Skincare
INSERT INTO suppliers (id, name, phone, address) VALUES
(1, 'PT Beauty Cosmetica Indonesia', '021-8890123', 'Jakarta Selatan'),
(2, 'CV Glow Skin Botanica', '022-7760987', 'Bandung');

-- 5. Seed Relasi Produk & Supplier
INSERT INTO product_suppliers (product_id, supplier_id, supplier_price) VALUES
(1, 1, 40000.00),
(2, 1, 65000.00),
(3, 2, 85000.00),
(4, 2, 55000.00);

-- 6. Seed Transaksi Awal Stock Movements
INSERT INTO stock_movements (product_id, user_id, type, quantity, notes) VALUES
(1, 1, 'IN', 20, 'Stok awal facial wash dari supplier'),
(2, 1, 'IN', 3, 'Stok awal serum brightening');