<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-mobile-alt"></i>
        </div>
        <div class="sidebar-title">Konter Pulsa</div>
        <div class="sidebar-subtitle">Dashboard</div>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
        </div>
        <div class="nav-item">
            <a href="products.php" class="nav-link <?= $current_page === 'products.php' ? 'active' : '' ?>">
                <i class="fas fa-box"></i>
                Produk
            </a>
        </div>
        <div class="nav-item">
            <a href="stock.php" class="nav-link <?= $current_page === 'stock.php' ? 'active' : '' ?>">
                <i class="fas fa-warehouse"></i>
                Stok
            </a>
        </div>
        <div class="nav-item">
            <a href="transactions.php" class="nav-link <?= $current_page === 'transactions.php' ? 'active' : '' ?>">
                <i class="fas fa-exchange-alt"></i>
                Transaksi
            </a>
        </div>
        <div class="nav-item">
            <a href="reports.php" class="nav-link <?= $current_page === 'reports.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                Laporan
            </a>
        </div>
        <?php if ($auth->isAdmin()): ?>
        <div class="nav-item">
            <a href="users.php" class="nav-link <?= $current_page === 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                Pengguna
            </a>
        </div>
        <div class="nav-item">
            <a href="settings.php" class="nav-link <?= $current_page === 'settings.php' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                Pengaturan
            </a>
        </div>
        <?php endif; ?>
    </nav>
</aside> 