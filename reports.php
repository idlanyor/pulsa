<?php
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
    <title>Laporan - Dashboard Konter Pulsa</title>
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
                    <a href="dashboard.php" class="nav-link">
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
                    <a href="reports.php" class="nav-link active">
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
                    <h1 class="header-title">Laporan</h1>
                </div>
                
                <div class="header-actions">
                    <div class="user-menu" onclick="toggleUserMenu()">
                        <div class="user-avatar">
                            <?= strtoupper(substr($user["full_name"], 0, 1)) ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?= htmlspecialchars($user["full_name"]) ?></div>
                            <div class="user-role"><?= ucfirst($user["role"]) ?></div>
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

            <!-- Reports Content -->
            <div class="reports-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Laporan Penjualan Harian</h3>
                        <div class="card-actions">
                            <input type="date" id="reportDateFrom" class="form-control">
                            <input type="date" id="reportDateTo" class="form-control">
                            <button class="btn btn-primary" onclick="generateSalesReport()">Generate Laporan</button>
                            <button class="btn btn-secondary" onclick="exportSalesReport()">Export CSV</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="salesReportSummary">
                            <!-- Sales report summary will be loaded here -->
                        </div>
                        <div id="salesReportChart" style="height: 300px; margin-top: 20px;">
                            <canvas id="dailySalesChart"></canvas>
                        </div>
                        <div id="salesReportTable" class="mt-20">
                            <!-- Sales report table will be loaded here -->
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
        let dailySalesChart = null;

        document.addEventListener("DOMContentLoaded", function() {
            // Set default dates for report
            const today = new Date();
            const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            document.getElementById("reportDateFrom").value = firstDayOfMonth.toISOString().split('T')[0];
            document.getElementById("reportDateTo").value = today.toISOString().split('T')[0];
            
            generateSalesReport();
        });

        async function generateSalesReport() {
            showLoading();
            try {
                const dateFrom = document.getElementById("reportDateFrom").value;
                const dateTo = document.getElementById("reportDateTo").value;

                const response = await api.getSalesReport(dateFrom, dateTo);
                if (response && response.success) {
                    renderSalesReport(response.data);
                } else {
                    showNotification("Gagal membuat laporan penjualan", "error");
                }
            } catch (error) {
                console.error("Error generating sales report:", error);
                showNotification("Terjadi kesalahan saat membuat laporan penjualan", "error");
            } finally {
                hideLoading();
            }
        }

        function renderSalesReport(reportData) {
            const summaryDiv = document.getElementById("salesReportSummary");
            const tableDiv = document.getElementById("salesReportTable");

            // Render Summary
            summaryDiv.innerHTML = `
                <div class="card-body">
                    <p><strong>Periode:</strong> ${formatDate(reportData.period.from, 'DD MMMM YYYY')} - ${formatDate(reportData.period.to, 'DD MMMM YYYY')}</p>
                    <p><strong>Total Transaksi:</strong> ${reportData.summary.total_transactions}</p>
                    <p><strong>Total Kuantitas Terjual:</strong> ${reportData.summary.total_quantity}</p>
                    <p><strong>Total Pendapatan:</strong> ${formatCurrency(reportData.summary.total_revenue)}</p>
                    <p><strong>Total Biaya Produk:</strong> ${formatCurrency(reportData.summary.total_cost)}</p>
                    <p><strong>Total Keuntungan:</strong> ${formatCurrency(reportData.summary.total_profit)}</p>
                </div>
            `;

            // Render Chart
            const salesData = reportData.daily_breakdown;
            const labels = salesData.map(item => new Date(item.sale_date).toLocaleDateString('id-ID'));
            const revenues = salesData.map(item => item.total_revenue);
            const profits = salesData.map(item => item.total_profit);

            const ctx = document.getElementById('dailySalesChart').getContext('2d');
            if (dailySalesChart) {
                dailySalesChart.destroy();
            }
            dailySalesChart = new Chart(ctx, {
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

            // Render Table
            if (salesData.length === 0) {
                tableDiv.innerHTML = "<div class=\"no-data\">Tidak ada data penjualan untuk periode ini.</div>";
                return;
            }

            const columns = [
                { title: "Tanggal", key: "sale_date", format: (value) => formatDate(value, 'DD MMMM YYYY') },
                { title: "Transaksi", key: "total_transactions" },
                { title: "Kuantitas", key: "total_quantity" },
                { title: "Pendapatan", key: "total_revenue", format: (value) => formatCurrency(value) },
                { title: "Biaya Produk", key: "total_cost", format: (value) => formatCurrency(value) },
                { title: "Keuntungan", key: "total_profit", format: (value) => formatCurrency(value) }
            ];
            createTable(salesData, columns, "salesReportTable");
        }

        function exportSalesReport() {
            const table = document.getElementById("salesReportTable").querySelector("table");
            if (!table) {
                showNotification("Tidak ada data untuk diexport", "warning");
                return;
            }
            
            const rows = Array.from(table.querySelectorAll("tr"));
            const csvContent = rows.map(row => {
                const cols = Array.from(row.querySelectorAll("th, td"));
                return cols.map(col => `"${col.innerText.replace(/\n/g, ' ').replace(/"/g, '""')}"`).join(",");
            }).join("\n");

            const filename = `laporan_penjualan_${document.getElementById("reportDateFrom").value}_${document.getElementById("reportDateTo").value}.csv`;
            exportToCSV(csvContent, filename);
            showNotification("Laporan berhasil diexport ke CSV", "success");
        }

        // Sidebar toggle for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // User menu toggle
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
        }

        // Close user menu when clicking outside
        document.addEventListener('click', function(e) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('userDropdown');
            
            if (!userMenu.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    </script>
</body>
</html>

