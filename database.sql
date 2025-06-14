-- Database untuk Dashboard Konter Pulsa
-- Sistem Inventory Voucher Data

CREATE DATABASE IF NOT EXISTS pulsa_dashboard;
USE pulsa_dashboard;

-- Tabel Provider
CREATE TABLE providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    code VARCHAR(10) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Kategori Produk
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Produk Voucher
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    quota VARCHAR(50), -- contoh: "1GB", "5GB", "Unlimited"
    validity_days INT, -- masa aktif dalam hari
    buy_price DECIMAL(10,2) NOT NULL,
    sell_price DECIMAL(10,2) NOT NULL,
    profit DECIMAL(10,2) GENERATED ALWAYS AS (sell_price - buy_price) STORED,
    profit_percentage DECIMAL(5,2) GENERATED ALWAYS AS ((sell_price - buy_price) / buy_price * 100) STORED,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Tabel Stok
CREATE TABLE stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    min_stock INT DEFAULT 5, -- minimum stok untuk alert
    max_stock INT DEFAULT 100, -- maksimum stok
    last_restock_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_stock (product_id)
);

-- Tabel Transaksi Masuk (Pembelian/Restock)
CREATE TABLE stock_in (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    buy_price DECIMAL(10,2) NOT NULL,
    total_cost DECIMAL(10,2) GENERATED ALWAYS AS (quantity * buy_price) STORED,
    supplier VARCHAR(200),
    invoice_number VARCHAR(100),
    notes TEXT,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tabel Transaksi Keluar (Penjualan)
CREATE TABLE stock_out (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    sell_price DECIMAL(10,2) NOT NULL,
    total_revenue DECIMAL(10,2) GENERATED ALWAYS AS (quantity * sell_price) STORED,
    customer_name VARCHAR(200),
    customer_phone VARCHAR(20),
    payment_method ENUM('cash', 'transfer', 'ewallet', 'credit') DEFAULT 'cash',
    notes TEXT,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tabel Users (untuk sistem login sederhana)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'operator') DEFAULT 'operator',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Settings
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert data awal
INSERT INTO providers (name, code) VALUES 
('Telkomsel', 'TSEL'),
('XL Axiata', 'XL'),
('Indosat Ooredoo', 'ISAT'),
('Three', '3'),
('Axis', 'AXIS');

INSERT INTO categories (name, description) VALUES 
('Data Harian', 'Paket data dengan masa aktif harian'),
('Data Mingguan', 'Paket data dengan masa aktif mingguan'),
('Data Bulanan', 'Paket data dengan masa aktif bulanan'),
('Data Unlimited', 'Paket data unlimited'),
('Combo', 'Paket kombinasi data, SMS, dan telepon');

-- Insert user admin default
INSERT INTO users (username, password, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');
-- Password default: password

-- Insert settings default
INSERT INTO settings (setting_key, setting_value, description) VALUES 
('shop_name', 'Konter Pulsa ABC', 'Nama toko/konter'),
('shop_address', 'Jl. Contoh No. 123', 'Alamat toko'),
('shop_phone', '081234567890', 'Nomor telepon toko'),
('currency', 'IDR', 'Mata uang'),
('low_stock_alert', '5', 'Batas minimum stok untuk alert'),
('backup_frequency', 'daily', 'Frekuensi backup database');

-- Views untuk laporan
CREATE VIEW v_stock_summary AS
SELECT 
    p.id,
    p.name as product_name,
    p.code as product_code,
    pr.name as provider_name,
    c.name as category_name,
    s.quantity,
    s.min_stock,
    s.max_stock,
    p.buy_price,
    p.sell_price,
    p.profit,
    p.profit_percentage,
    (s.quantity * p.buy_price) as stock_value,
    CASE 
        WHEN s.quantity <= s.min_stock THEN 'Low Stock'
        WHEN s.quantity >= s.max_stock THEN 'Overstock'
        ELSE 'Normal'
    END as stock_status
FROM products p
LEFT JOIN stock s ON p.id = s.product_id
LEFT JOIN providers pr ON p.provider_id = pr.id
LEFT JOIN categories c ON p.category_id = c.id
WHERE p.status = 'active';

CREATE VIEW v_daily_sales AS
SELECT 
    DATE(so.transaction_date) as sale_date,
    COUNT(*) as total_transactions,
    SUM(so.quantity) as total_quantity,
    SUM(so.total_revenue) as total_revenue,
    SUM(so.quantity * p.buy_price) as total_cost,
    SUM(so.total_revenue - (so.quantity * p.buy_price)) as total_profit
FROM stock_out so
JOIN products p ON so.product_id = p.id
GROUP BY DATE(so.transaction_date)
ORDER BY sale_date DESC;

-- Triggers untuk update stok otomatis
DELIMITER //

CREATE TRIGGER update_stock_after_stock_in
AFTER INSERT ON stock_in
FOR EACH ROW
BEGIN
    INSERT INTO stock (product_id, quantity, last_restock_date)
    VALUES (NEW.product_id, NEW.quantity, NEW.transaction_date)
    ON DUPLICATE KEY UPDATE 
        quantity = quantity + NEW.quantity,
        last_restock_date = NEW.transaction_date;
END//

CREATE TRIGGER update_stock_after_stock_out
AFTER INSERT ON stock_out
FOR EACH ROW
BEGIN
    UPDATE stock 
    SET quantity = quantity - NEW.quantity
    WHERE product_id = NEW.product_id;
END//

DELIMITER ;

