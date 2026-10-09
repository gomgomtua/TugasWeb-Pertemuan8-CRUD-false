-- ============================================================
-- schema.sql — Database inventaris_db (Tugas Rutin 8)
-- DDL (struktur) + seed data. Aman di-import berulang kali:
-- tabel lama dihapus dulu lalu dibuat ulang.
-- ============================================================

CREATE DATABASE IF NOT EXISTS inventaris_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE inventaris_db;

-- Hapus tabel (urutan: anak dulu, baru induk)
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS suppliers;

-- ------------------------------------------------------------
-- 1) categories — tabel induk (sisi "one")
-- ------------------------------------------------------------
CREATE TABLE categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2) suppliers — tabel induk (sisi "one")
-- ------------------------------------------------------------
CREATE TABLE suppliers (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(100) NOT NULL,
    phone VARCHAR(20)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3) products — tabel anak (sisi "many"), menyimpan 2 Foreign Key
--    Relasi One-to-Many: 1 kategori -> banyak produk,
--                        1 supplier -> banyak produk.
-- ------------------------------------------------------------
CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)  NOT NULL,
    category_id INT           NOT NULL,
    supplier_id INT           NOT NULL,
    price       DECIMAL(12,2) NOT NULL,
    stock       INT           NOT NULL DEFAULT 0,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_products_supplier
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4) activity_logs — BONUS (log aktivitas untuk transaction)
--    Sengaja TANPA foreign key ke products, supaya riwayat
--    tetap tersimpan walaupun produknya sudah dihapus.
-- ------------------------------------------------------------
CREATE TABLE activity_logs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    action       VARCHAR(20)  NOT NULL,          -- create | update | delete
    product_id   INT          NULL,
    product_name VARCHAR(150) NOT NULL,
    details      VARCHAR(255) NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA (minimal 5 baris per tabel)
-- ============================================================

INSERT INTO categories (name) VALUES
    ('Laptop'),
    ('Aksesoris'),
    ('Display'),
    ('Jaringan'),
    ('Penyimpanan');

INSERT INTO suppliers (name, phone) VALUES
    ('PT Nusantara Teknologi', '0812-3456-7801'),
    ('CV Maju Komputer',       '0813-2233-4402'),
    ('PT Digital Sentosa',     '0821-5566-7703'),
    ('Toko Jaya Elektronik',   '0852-1122-3304'),
    ('PT Global Perkasa',      '0878-9900-1105');

INSERT INTO products (name, category_id, supplier_id, price, stock) VALUES
    ('Laptop ASUS Vivobook 14',        1, 1, 8500000.00,  12),
    ('Laptop Lenovo IdeaPad Slim 3',   1, 1, 7900000.00,   8),
    ('Mouse Wireless Logitech M331',   2, 2,  185000.00,  40),
    ('Keyboard Mechanical Rexus',      2, 2,  450000.00,  25),
    ('Monitor LED 24 inci Samsung',    3, 3, 1750000.00,  15),
    ('Monitor LG 27 inci IPS',         3, 3, 2650000.00,   6),
    ('Router TP-Link Archer C6',       4, 4,  520000.00,  18),
    ('Access Point UniFi U6 Lite',     4, 4, 1650000.00,   4),
    ('SSD Kingston NV2 500GB',         5, 5,  650000.00,  30),
    ('Flashdisk SanDisk 64GB',         5, 5,   85000.00, 100);

INSERT INTO activity_logs (action, product_id, product_name, details) VALUES
    ('create', 1, 'Laptop ASUS Vivobook 14',      'Data awal (seed)'),
    ('create', 2, 'Laptop Lenovo IdeaPad Slim 3', 'Data awal (seed)'),
    ('create', 3, 'Mouse Wireless Logitech M331', 'Data awal (seed)'),
    ('create', 4, 'Keyboard Mechanical Rexus',    'Data awal (seed)'),
    ('create', 5, 'Monitor LED 24 inci Samsung',  'Data awal (seed)');
