<?php
require_once 'database.php';

class Category {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Get all categories
    public function getAll() {
        try {
            $this->db->query('SELECT * FROM categories ORDER BY name ASC');
            return $this->db->resultset();
        } catch (Exception $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return false;
        }
    }

    // Get active categories
    public function getActive() {
        try {
            $this->db->query('SELECT * FROM categories WHERE status = "active" ORDER BY name ASC');
            return $this->db->resultset();
        } catch (Exception $e) {
            error_log("Error getting active categories: " . $e->getMessage());
            return false;
        }
    }

    // Get category by ID
    public function getById($id) {
        try {
            $this->db->query('SELECT * FROM categories WHERE id = :id');
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (Exception $e) {
            error_log("Error getting category by ID: " . $e->getMessage());
            return false;
        }
    }

    // Create new category
    public function create($name, $description = '', $status = 'active') {
        try {
            // Validate input
            if (empty($name)) {
                throw new Exception("Category name is required");
            }

            $this->db->query('INSERT INTO categories (name, description, status) VALUES (:name, :description, :status)');
            $this->db->bind(':name', $name);
            $this->db->bind(':description', $description);
            $this->db->bind(':status', $status);
            
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error creating category: " . $e->getMessage());
            return false;
        }
    }

    // Update category
    public function update($id, $name, $description = '', $status = 'active') {
        try {
            // Validate input
            if (empty($name)) {
                throw new Exception("Category name is required");
            }

            $this->db->query('UPDATE categories SET name = :name, description = :description, status = :status WHERE id = :id');
            $this->db->bind(':id', $id);
            $this->db->bind(':name', $name);
            $this->db->bind(':description', $description);
            $this->db->bind(':status', $status);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error updating category: " . $e->getMessage());
            return false;
        }
    }

    // Delete category
    public function delete($id) {
        try {
            // Check if category is in use
            $this->db->query('SELECT COUNT(*) as count FROM products WHERE category_id = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();
            $result = $this->db->single();
            $count = $result['count'];

            if ($count > 0) {
                throw new Exception("Cannot delete category that is in use by products");
            }

            $this->db->query('DELETE FROM categories WHERE id = :id');
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error deleting category: " . $e->getMessage());
            return false;
        }
    }
}
?>

