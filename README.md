# Dashboard Konter Pulsa - Dokumentasi

## Deskripsi
Aplikasi web dashboard untuk konter pulsa yang menyediakan sistem inventory management lengkap untuk voucher data dari provider XL, Indosat, Three, Axis, dan Telkomsel.

## Fitur Utama
- **Dashboard Statistik**: Menampilkan ringkasan stok, penjualan, dan keuntungan
- **Manajemen Produk**: CRUD produk voucher data dengan berbagai provider
- **Manajemen Stok**: Pencatatan stok masuk, keluar, dan penyesuaian
- **Transaksi**: Pencatatan penjualan dan pembelian
- **Laporan**: Laporan penjualan dan analisis bisnis
- **Manajemen User**: Sistem user dengan role admin dan operator
- **Authentication**: Sistem login dengan session management

## Teknologi yang Digunakan
- **Backend**: PHP Native dengan PDO
- **Database**: MySQL
- **Frontend**: HTML5, CSS3 (Glassmorphism), JavaScript ES6
- **Charts**: Chart.js
- **Icons**: Font Awesome 6
- **Design**: Modern glassmorphism dengan gradients

## Struktur Database

### Tabel Utama
1. **providers** - Data provider (Telkomsel, XL, Indosat, Three, Axis)
2. **categories** - Kategori produk (Data Harian, Mingguan, Bulanan, dll)
3. **products** - Produk voucher data
4. **stock** - Data stok produk
5. **stock_in** - Transaksi stok masuk (pembelian)
6. **stock_out** - Transaksi stok keluar (penjualan)
7. **users** - Data pengguna sistem
8. **settings** - Pengaturan aplikasi

### Views
- **v_stock_summary** - Ringkasan stok dengan status
- **v_daily_sales** - Ringkasan penjualan harian

### Triggers
- **update_stock_after_stock_in** - Update stok otomatis setelah stok masuk
- **update_stock_after_stock_out** - Update stok otomatis setelah stok keluar

## API Endpoints

### Authentication
- `POST /api.php/auth/login` - Login user
- `POST /api.php/auth/logout` - Logout user
- `GET /api.php/auth/profile` - Get user profile

### Products
- `GET /api.php/products` - Get all products
- `GET /api.php/products/{id}` - Get product by ID
- `POST /api.php/products` - Create new product
- `PUT /api.php/products/{id}` - Update product
- `DELETE /api.php/products/{id}` - Delete product

### Stock
- `GET /api.php/stock/summary` - Get stock summary
- `GET /api.php/stock/low` - Get low stock items
- `GET /api.php/stock/statistics` - Get stock statistics
- `POST /api.php/stock/in` - Add stock in
- `POST /api.php/stock/out` - Add stock out
- `POST /api.php/stock/adjust` - Adjust stock manually
- `PUT /api.php/stock/settings` - Update stock settings

### Transactions
- `GET /api.php/transactions/in` - Get stock in transactions
- `GET /api.php/transactions/out` - Get stock out transactions
- `GET /api.php/transactions/sales` - Get daily sales
- `GET /api.php/transactions/statistics` - Get sales statistics
- `GET /api.php/transactions/report` - Generate sales report

### Users
- `GET /api.php/users` - Get all users (admin only)
- `GET /api.php/users/{id}` - Get user by ID
- `POST /api.php/users` - Create new user (admin only)
- `PUT /api.php/users/{id}` - Update user
- `DELETE /api.php/users/{id}` - Delete user (admin only)

## Instalasi

### Requirements
- PHP 8.1+
- MySQL 5.7+
- Apache/Nginx
- Extensions: PDO, PDO_MySQL, JSON, MBString

### Langkah Instalasi
1. Clone atau download project
2. Import database dari file `database.sql`
3. Konfigurasi database di `config.php`
4. Set permissions untuk direktori
5. Akses aplikasi melalui web browser

### Konfigurasi Database
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pulsa_dashboard');
```

### Default Login
- Username: `admin`
- Password: `password`

## Struktur File
```
pulsa-dashboard/
├── config.php                 # Konfigurasi aplikasi
├── database.php               # Class database connection
├── database.sql               # SQL schema dan data awal
├── api.php                    # API endpoints
├── index.php                  # Halaman login
├── dashboard.php              # Dashboard utama
├── .htaccess                  # URL rewriting dan security
├── assets/
│   ├── css/
│   │   └── style.css          # Stylesheet utama
│   ├── js/
│   │   ├── auth.js            # Authentication functions
│   │   └── dashboard.js       # Dashboard functions
│   └── images/                # Gambar dan assets
├── includes/
│   ├── auth.php               # Class authentication
│   └── helper.php             # Helper functions
├── models/
│   ├── Provider.php           # Model provider
│   ├── Category.php           # Model kategori
│   ├── Product.php            # Model produk
│   ├── Stock.php              # Model stok
│   ├── Transaction.php        # Model transaksi
│   └── User.php               # Model user
├── controllers/
│   ├── ProductController.php  # Controller produk
│   ├── StockController.php    # Controller stok
│   ├── TransactionController.php # Controller transaksi
│   └── UserController.php     # Controller user
└── views/                     # Halaman-halaman lainnya
```

## Fitur Keamanan
- Password hashing dengan bcrypt
- SQL injection protection dengan prepared statements
- XSS protection dengan input sanitization
- Session timeout management
- Role-based access control
- CSRF protection headers

## Responsive Design
- Mobile-first approach
- Glassmorphism design dengan backdrop-filter
- Modern gradients dan animations
- Touch-friendly interface
- Collapsible sidebar untuk mobile

## Browser Support
- Chrome 88+
- Firefox 84+
- Safari 14+
- Edge 88+

## Pengembangan Selanjutnya
- [ ] Backup dan restore database
- [ ] Export laporan ke PDF/Excel
- [ ] Notifikasi real-time
- [ ] Multi-language support
- [ ] Dark mode theme
- [ ] Mobile app (PWA)
- [ ] Integration dengan payment gateway
- [ ] Barcode scanner untuk produk

## Lisensi
Copyright © 2024 Dashboard Konter Pulsa. All rights reserved.

## Support
Untuk bantuan dan support, silakan hubungi developer.

