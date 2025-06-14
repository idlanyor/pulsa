<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'models/Setting.php';

class SettingController {
    private $settingModel;
    private $auth;
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
        $this->settingModel = new Setting($this->db);
        $this->auth = new Auth();
    }

    public function getSettings() {
        if (!$this->auth->isLoggedIn()) {
            return ['error' => 'Unauthorized'];
        }
        return ['success' => true, 'data' => $this->settingModel->getAllSettings()];
    }

    public function updateGeneralSettings($data) {
        if (!$this->auth->isAdmin()) {
            return ['error' => 'Access denied'];
        }

        $updated = true;
        if (isset($data['app_name'])) {
            if (!$this->settingModel->updateSetting('app_name', $data['app_name'])) $updated = false;
        }
        if (isset($data['currency_symbol'])) {
            if (!$this->settingModel->updateSetting('currency_symbol', $data['currency_symbol'])) $updated = false;
        }
        if (isset($data['min_stock_threshold'])) {
            if (!$this->settingModel->updateSetting('min_stock_threshold', $data['min_stock_threshold'])) $updated = false;
        }
        if (isset($data['max_stock_threshold'])) {
            if (!$this->settingModel->updateSetting('max_stock_threshold', $data['max_stock_threshold'])) $updated = false;
        }

        if ($updated) {
            return ['success' => true, 'message' => 'Pengaturan umum berhasil diperbarui.'];
        } else {
            return ['error' => 'Gagal memperbarui beberapa pengaturan umum.'];
        }
    }

    public function updateDatabaseSettings($data) {
        if (!$this->auth->isAdmin()) {
            return ['error' => 'Access denied'];
        }

        $updated = true;
        if (isset($data['db_host'])) {
            if (!$this->settingModel->updateSetting('db_host', $data['db_host'])) $updated = false;
        }
        if (isset($data['db_name'])) {
            if (!$this->settingModel->updateSetting('db_name', $data['db_name'])) $updated = false;
        }
        if (isset($data['db_user'])) {
            if (!$this->settingModel->updateSetting('db_user', $data['db_user'])) $updated = false;
        }
        if (isset($data['db_pass']) && !empty($data['db_pass'])) {
            if (!$this->settingModel->updateSetting('db_pass', $data['db_pass'])) $updated = false;
        }

        if ($updated) {
            return ['success' => true, 'message' => 'Pengaturan database berhasil diperbarui.'];
        } else {
            return ['error' => 'Gagal memperbarui beberapa pengaturan database.'];
        }
    }
}
?>

