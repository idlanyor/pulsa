<?php
class Setting {
    private $db;
    private $table_name = "settings";

    public function __construct($db) {
        $this->db = $db;
    }

    public function getSetting($key) {
        $query = "SELECT setting_value FROM " . $this->table_name . " WHERE setting_key = :key LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row["setting_value"] : null;
    }

    public function updateSetting($key, $value) {
        $query = "INSERT INTO " . $this->table_name . " (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = :value";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->bindParam(":value", $value);
        return $stmt->execute();
    }

    public function getAllSettings() {
        $query = "SELECT setting_key, setting_value FROM " . $this->table_name;
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row["setting_key"]] = $row["setting_value"];
        }
        return $settings;
    }
}
?>

