<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'models/Setting.php';

$auth = new Auth();
$auth->requireAdmin(); // Only admin can access this page
$db = new Database();
$settingModel = new Setting($db->getConnection());
$user = $auth->getCurrentUser();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_general_settings'])) {
        $appName = $_POST['app_name'];
        $currencySymbol = $_POST['currency_symbol'];
        $minStockThreshold = $_POST['min_stock_threshold'];
        $maxStockThreshold = $_POST['max_stock_threshold'];

        $updated = true;
        if (!$settingModel->updateSetting('app_name', $appName)) $updated = false;
        if (!$settingModel->updateSetting('currency_symbol', $currencySymbol)) $updated = false;
        if (!$settingModel->updateSetting('min_stock_threshold', $minStockThreshold)) $updated = false;
        if (!$settingModel->updateSetting('max_stock_threshold', $maxStockThreshold)) $updated = false;

        if ($updated) {
            $message = 'Pengaturan umum berhasil diperbarui.';
        } else {
            $message = 'Gagal memperbarui pengaturan umum.';
        }
    } elseif (isset($_POST['update_database_settings'])) {
        $dbHost = $_POST['db_host'];
        $dbName = $_POST['db_name'];
        $dbUser = $_POST['db_user'];
        $dbPass = $_POST['db_pass'];

        $updated = true;
        if (!$settingModel->updateSetting('db_host', $dbHost)) $updated = false;
        if (!$settingModel->updateSetting('db_name', $dbName)) $updated = false;
        if (!$settingModel->updateSetting('db_user', $dbUser)) $updated = false;
        if (!empty($dbPass)) {
            if (!$settingModel->updateSetting('db_pass', $dbPass)) $updated = false;
        }

        if ($updated) {
            $message = 'Pengaturan database berhasil diperbarui.';
        } else {
            $message = 'Gagal memperbarui pengaturan database.';
        }
    }
}

$settings = $settingModel->getAllSettings();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Dashboard Konter Pulsa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/settings.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            <main class="settings-container">
                <div class="page-header">
                    <h1><i class="fas fa-cog"></i> Pengaturan Aplikasi</h1>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <div class="settings-grid">
                    <div class="settings-card">
                        <div class="card-header">
                            <h2><i class="fas fa-sliders-h"></i> Pengaturan Umum</h2>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="settings-form">
                                <div class="form-group">
                                    <label for="appName">
                                        <i class="fas fa-store"></i> Nama Aplikasi
                                    </label>
                                    <input type="text" id="appName" name="app_name" value="<?= htmlspecialchars($settings['app_name'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="currencySymbol">
                                        <i class="fas fa-coins"></i> Simbol Mata Uang
                                    </label>
                                    <input type="text" id="currencySymbol" name="currency_symbol" value="<?= htmlspecialchars($settings['currency_symbol'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="minStockThreshold">
                                        <i class="fas fa-exclamation-triangle"></i> Ambang Batas Stok Minimum
                                    </label>
                                    <input type="number" id="minStockThreshold" name="min_stock_threshold" value="<?= htmlspecialchars($settings['min_stock_threshold'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="maxStockThreshold">
                                        <i class="fas fa-boxes"></i> Ambang Batas Stok Maksimum
                                    </label>
                                    <input type="number" id="maxStockThreshold" name="max_stock_threshold" value="<?= htmlspecialchars($settings['max_stock_threshold'] ?? '') ?>" required>
                                </div>
                                <button type="submit" name="update_general_settings" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Pengaturan Umum
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-header">
                            <h2><i class="fas fa-database"></i> Pengaturan Database</h2>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="settings-form">
                                <div class="form-group">
                                    <label for="dbHost">
                                        <i class="fas fa-server"></i> Host Database
                                    </label>
                                    <input type="text" id="dbHost" name="db_host" value="<?= htmlspecialchars($settings['db_host'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="dbName">
                                        <i class="fas fa-database"></i> Nama Database
                                    </label>
                                    <input type="text" id="dbName" name="db_name" value="<?= htmlspecialchars($settings['db_name'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="dbUser">
                                        <i class="fas fa-user"></i> User Database
                                    </label>
                                    <input type="text" id="dbUser" name="db_user" value="<?= htmlspecialchars($settings['db_user'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="dbPass">
                                        <i class="fas fa-key"></i> Password Database
                                    </label>
                                    <input type="password" id="dbPass" name="db_pass">
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Kosongkan jika tidak ingin mengubah password.
                                    </small>
                                </div>
                                <button type="submit" name="update_database_settings" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Pengaturan Database
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

