<?php
class Setting {
    private $db;
    private $table_name = "settings";

    public function __construct($db) {
        $this->db = $db;
    }

    public function getSetting($key) {
        $query = "SELECT value FROM " . $this->table_name . " WHERE `key` = :key LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row["value"] : null;
    }

    public function updateSetting($key, $value) {
        $query = "INSERT INTO " . $this->table_name . " (`key`, `value`) VALUES (:key, :value) ON DUPLICATE KEY UPDATE `value` = :value";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->bindParam(":value", $value);
        return $stmt->execute();
    }

    public function getAllSettings() {
        $query = "SELECT `key`, `value` FROM " . $this->table_name;
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row["key"]] = $row["value"];
        }
        return $settings;
    }
}
?>

