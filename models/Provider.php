<?php
require_once 'database.php';

class Provider {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get all providers
    public function getAll() {
        try {
            $this->db->query('SELECT * FROM providers ORDER BY name ASC');
            return $this->db->resultset();
        } catch (Exception $e) {
            error_log("Error getting providers: " . $e->getMessage());
            return false;
        }
    }

    // Get active providers
    public function getActive() {
        try {
            $this->db->query('SELECT * FROM providers WHERE status = "active" ORDER BY name ASC');
            return $this->db->resultset();
        } catch (Exception $e) {
            error_log("Error getting active providers: " . $e->getMessage());
            return false;
        }
    }

    // Get provider by ID
    public function getById($id) {
        try {
            $this->db->query('SELECT * FROM providers WHERE id = :id');
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (Exception $e) {
            error_log("Error getting provider by ID: " . $e->getMessage());
            return false;
        }
    }

    // Create new provider
    public function create($name, $code, $status = 'active') {
        try {
            // Validate input
            if (empty($name)) {
                throw new Exception("Provider name is required");
            }
            if (empty($code)) {
                throw new Exception("Provider code is required");
            }

            // Check if code already exists
            if ($this->codeExists($code)) {
                throw new Exception("Provider code already exists");
            }

            $this->db->query('INSERT INTO providers (name, code, status) VALUES (:name, :code, :status)');
            $this->db->bind(':name', $name);
            $this->db->bind(':code', $code);
            $this->db->bind(':status', $status);
            
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error creating provider: " . $e->getMessage());
            throw $e;
        }
    }

    // Update provider
    public function update($id, $name, $code, $status = 'active') {
        try {
            // Validate input
            if (empty($name)) {
                throw new Exception("Provider name is required");
            }
            if (empty($code)) {
                throw new Exception("Provider code is required");
            }

            // Check if code already exists (excluding current provider)
            if ($this->codeExists($code, $id)) {
                throw new Exception("Provider code already exists");
            }

            $this->db->query('UPDATE providers SET name = :name, code = :code, status = :status WHERE id = :id');
            $this->db->bind(':id', $id);
            $this->db->bind(':name', $name);
            $this->db->bind(':code', $code);
            $this->db->bind(':status', $status);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error updating provider: " . $e->getMessage());
            throw $e;
        }
    }

    // Delete provider
    public function delete($id) {
        try {
            // Check if provider is in use
            $this->db->query('SELECT COUNT(*) as count FROM products WHERE provider_id = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();
            $result = $this->db->single();
            $count = $result['count'];

            if ($count > 0) {
                throw new Exception("Cannot delete provider that is in use by products");
            }

            $this->db->query('DELETE FROM providers WHERE id = :id');
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error deleting provider: " . $e->getMessage());
            throw $e;
        }
    }

    // Check if provider code exists
    public function codeExists($code, $exclude_id = null) {
        try {
            if ($exclude_id) {
                $this->db->query('SELECT id FROM providers WHERE code = :code AND id != :exclude_id');
                $this->db->bind(':exclude_id', $exclude_id);
            } else {
                $this->db->query('SELECT id FROM providers WHERE code = :code');
            }
            $this->db->bind(':code', $code);
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error checking provider code: " . $e->getMessage());
            return false;
        }
    }
}
?>

