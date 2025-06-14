<?php
require_once 'database.php';

class Category {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Get all categories
    public function getAll() {
        $this->db->query('SELECT * FROM categories ORDER BY name ASC');
        return $this->db->resultset();
    }

    // Get active categories
    public function getActive() {
        $this->db->query('SELECT * FROM categories WHERE status = "active" ORDER BY name ASC');
        return $this->db->resultset();
    }

    // Get category by ID
    public function getById($id) {
        $this->db->query('SELECT * FROM categories WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Create new category
    public function create($data) {
        $this->db->query('INSERT INTO categories (name, description, status) VALUES (:name, :description, :status)');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':status', $data['status'] ?? 'active');
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Update category
    public function update($id, $data) {
        $this->db->query('UPDATE categories SET name = :name, description = :description, status = :status WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':status', $data['status']);
        
        return $this->db->execute();
    }

    // Delete category
    public function delete($id) {
        $this->db->query('DELETE FROM categories WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>

