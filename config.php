<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'kanata');
define('DB_PASS', 'ngeteh789');
define('DB_NAME', 'pulsa_dashboard');

// Konfigurasi Aplikasi
define('APP_NAME', 'Dashboard Konter Pulsa');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'https://backend.antidonasi.web.id');

// Konfigurasi Session
define('SESSION_TIMEOUT', 3600); // 1 jam

// Konfigurasi Timezone
date_default_timezone_set('Asia/Jakarta');

// Error Reporting (untuk development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autostart session
session_start();
?>

