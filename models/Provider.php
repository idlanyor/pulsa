<?php
require_once 'database.php';

class Provider {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Get all providers
    public function getAll() {
        $this->db->query('SELECT * FROM providers ORDER BY name ASC');
        return $this->db->resultset();
    }

    // Get active providers
    public function getActive() {
        $this->db->query('SELECT * FROM providers WHERE status = "active" ORDER BY name ASC');
        return $this->db->resultset();
    }

    // Get provider by ID
    public function getById($id) {
        $this->db->query('SELECT * FROM providers WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Create new provider
    public function create($data) {
        $this->db->query('INSERT INTO providers (name, code, status) VALUES (:name, :code, :status)');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':status', $data['status'] ?? 'active');
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Update provider
    public function update($id, $data) {
        $this->db->query('UPDATE providers SET name = :name, code = :code, status = :status WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':status', $data['status']);
        
        return $this->db->execute();
    }

    // Delete provider
    public function delete($id) {
        $this->db->query('DELETE FROM providers WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Check if provider code exists
    public function codeExists($code, $exclude_id = null) {
        if ($exclude_id) {
            $this->db->query('SELECT id FROM providers WHERE code = :code AND id != :exclude_id');
            $this->db->bind(':exclude_id', $exclude_id);
        } else {
            $this->db->query('SELECT id FROM providers WHERE code = :code');
        }
        $this->db->bind(':code', $code);
        return $this->db->single() ? true : false;
    }
}
?>

