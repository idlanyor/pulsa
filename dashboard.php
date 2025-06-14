<?php
session_destroy();
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$auth->requireLogin();
$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Konter Pulsa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
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
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="products.php" class="nav-link">
                        <i class="fas fa-box"></i>
                        Produk
                    </a>
                </div>
                <div class="nav-item">
                    <a href="stock.php" class="nav-link">
                        <i class="fas fa-warehouse"></i>
                        Stok
                    </a>
                </div>
                <div class="nav-item">
                    <a href="transactions.php" class="nav-link">
                        <i class="fas fa-exchange-alt"></i>
                        Transaksi
                    </a>
                </div>
                <div class="nav-item">
                    <a href="reports.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        Laporan
                    </a>
                </div>
                <?php if ($auth->isAdmin()): ?>
                <div class="nav-item">
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        Pengguna
                    </a>
                </div>
                <div class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Pengaturan
                    </a>
                </div>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="header-title">Dashboard</h1>
                </div>
                
                <div class="header-actions">
                    <button class="btn-icon" onclick="refreshData()" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    
                    <div class="user-menu" onclick="toggleUserMenu()">
                        <div class="user-avatar">
                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
                            <div class="user-role"><?= ucfirst($user['role']) ?></div>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    
                    <div class="user-dropdown" id="userDropdown">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            Profil
                        </a>
                        <a href="#" onclick="logout()" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Keluar
                        </a>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Statistics Cards -->
                <div class="stats-grid" id="statsGrid">
                    <!-- Stats will be loaded here -->
                </div>

                <!-- Charts and Tables Row -->
                <div class="dashboard-row">
                    <!-- Sales Chart -->
                    <div class="card chart-card">
                        <div class="card-header">
                            <h3 class="card-title">Penjualan Harian</h3>
                            <div class="card-actions">
                                <select id="chartPeriod" onchange="updateChart()">
                                    <option value="7">7 Hari Terakhir</option>
                                    <option value="30">30 Hari Terakhir</option>
                                    <option value="90">90 Hari Terakhir</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" height="300"></canvas>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="card alert-card">
                        <div class="card-header">
                            <h3 class="card-title">Stok Rendah</h3>
                            <span class="alert-count" id="lowStockCount">0</span>
                        </div>
                        <div class="card-body">
                            <div id="lowStockList" class="alert-list">
                                <!-- Low stock items will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Transaksi Terbaru</h3>
                        <a href="transactions.php" class="btn btn-primary">
                            Lihat Semua
                        </a>
                    </div>
                    <div class="card-body">
                        <div id="recentTransactions">
                            <!-- Recent transactions will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Memuat data...</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/auth.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script>
        let salesChart = null;

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
        });

        async function loadDashboardData() {
            showLoading();
            
            try {
                // Load statistics
                await loadStatistics();
                
                // Load low stock items
                await loadLowStock();
                
                // Load recent transactions
                await loadRecentTransactions();
                
                // Load sales chart
                await loadSalesChart();
                
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                showNotification('Gagal memuat data dashboard', 'error');
            } finally {
                hideLoading();
            }
        }

        async function loadStatistics() {
            try {
                const response = await api.getStockStatistics();
                if (response && response.success) {
                    const stats = response.data;
                    
                    const statsHtml = `
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="stat-value">${stats.total_products || 0}</div>
                            <div class="stat-label">Total Produk</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-value">${stats.low_stock_count || 0}</div>
                            <div class="stat-label">Stok Rendah</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-value">${formatCurrency(stats.total_stock_value || 0)}</div>
                            <div class="stat-label">Nilai Stok</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-value">${stats.out_of_stock_count || 0}</div>
                            <div class="stat-label">Stok Habis</div>
                        </div>
                    `;
                    
                    document.getElementById('statsGrid').innerHTML = statsHtml;
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        }

        async function loadLowStock() {
            try {
                const response = await api.getLowStock();
                if (response && response.success) {
                    const lowStockItems = response.data;
                    
                    document.getElementById('lowStockCount').textContent = lowStockItems.length;
                    
                    if (lowStockItems.length === 0) {
                        document.getElementById('lowStockList').innerHTML = 
                            '<div class="alert-item">Tidak ada stok rendah</div>';
                    } else {
                        const listHtml = lowStockItems.slice(0, 5).map(item => `
                            <div class="alert-item">
                                <div class="alert-info">
                                    <div class="alert-title">${item.product_name}</div>
                                    <div class="alert-subtitle">${item.provider_name}</div>
                                </div>
                                <div class="alert-value">
                                    <span class="stock-quantity">${item.quantity}</span>
                                    <span class="stock-min">/ ${item.min_stock}</span>
                                </div>
                            </div>
                        `).join('');
                        
                        document.getElementById('lowStockList').innerHTML = listHtml;
                    }
                }
            } catch (error) {
                console.error('Error loading low stock:', error);
            }
        }

        async function loadRecentTransactions() {
            try {
                const response = await api.getStockOutTransactions(1, 5);
                if (response && response.success) {
                    const transactions = response.data;
                    
                    if (transactions.length === 0) {
                        document.getElementById('recentTransactions').innerHTML = 
                            '<div class="no-data">Belum ada transaksi</div>';
                    } else {
                        const columns = [
                            { key: 'transaction_date', title: 'Tanggal', format: (value) => formatDate(value) },
                            { key: 'product_name', title: 'Produk' },
                            { key: 'quantity', title: 'Qty' },
                            { key: 'total_revenue', title: 'Total', format: (value) => formatCurrency(value) },
                            { key: 'customer_name', title: 'Pelanggan' }
                        ];
                        
                        createTable(transactions, columns, 'recentTransactions');
                    }
                }
            } catch (error) {
                console.error('Error loading recent transactions:', error);
            }
        }

        async function loadSalesChart() {
            try {
                const period = document.getElementById('chartPeriod').value;
                const endDate = new Date();
                const startDate = new Date();
                startDate.setDate(endDate.getDate() - period);
                
                const response = await api.getSalesReport(
                    startDate.toISOString().split('T')[0],
                    endDate.toISOString().split('T')[0]
                );
                
                if (response && response.success) {
                    const salesData = response.data.daily_breakdown;
                    
                    const labels = salesData.map(item => new Date(item.sale_date).toLocaleDateString('id-ID'));
                    const revenues = salesData.map(item => item.total_revenue);
                    const profits = salesData.map(item => item.total_profit);
                    
                    const ctx = document.getElementById('salesChart').getContext('2d');
                    
                    if (salesChart) {
                        salesChart.destroy();
                    }
                    
                    salesChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Pendapatan',
                                    data: revenues,
                                    borderColor: '#667eea',
                                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                                    tension: 0.4
                                },
                                {
                                    label: 'Keuntungan',
                                    data: profits,
                                    borderColor: '#feca57',
                                    backgroundColor: 'rgba(254, 202, 87, 0.1)',
                                    tension: 0.4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return formatCurrency(value);
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading sales chart:', error);
            }
        }

        function updateChart() {
            loadSalesChart();
        }

        function refreshData() {
            loadDashboardData();
            showNotification('Data berhasil diperbarui', 'success');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
        }

        // Close user menu when clicking outside
        document.addEventListener('click', function(e) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            
            if (!userMenu.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    </script>
</body>
</html>

