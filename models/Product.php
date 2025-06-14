<?php
require_once 'database.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Get all products with provider and category info
    public function getAll($limit = null, $offset = null) {
        $query = 'SELECT p.*, pr.name as provider_name, pr.code as provider_code, 
                         c.name as category_name, s.quantity, s.min_stock, s.max_stock
                  FROM products p
                  LEFT JOIN providers pr ON p.provider_id = pr.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN stock s ON p.id = s.product_id
                  ORDER BY p.name ASC';
        
        if ($limit !== null) {
            $query .= ' LIMIT :limit';
            if ($offset !== null) {
                $query .= ' OFFSET :offset';
            }
        }
        
        $this->db->query($query);
        if ($limit !== null) {
            $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
            }
        }
        
        return $this->db->resultset();
    }

    // Get active products
    public function getActive() {
        $this->db->query('SELECT p.*, pr.name as provider_name, c.name as category_name, s.quantity
                         FROM products p
                         LEFT JOIN providers pr ON p.provider_id = pr.id
                         LEFT JOIN categories c ON p.category_id = c.id
                         LEFT JOIN stock s ON p.id = s.product_id
                         WHERE p.status = "active"
                         ORDER BY p.name ASC');
        return $this->db->resultset();
    }

    // Get product by ID
    public function getById($id) {
        $this->db->query('SELECT p.*, pr.name as provider_name, c.name as category_name, s.quantity, s.min_stock, s.max_stock
                         FROM products p
                         LEFT JOIN providers pr ON p.provider_id = pr.id
                         LEFT JOIN categories c ON p.category_id = c.id
                         LEFT JOIN stock s ON p.id = s.product_id
                         WHERE p.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get products by provider
    public function getByProvider($provider_id) {
        $this->db->query('SELECT p.*, c.name as category_name, s.quantity
                         FROM products p
                         LEFT JOIN categories c ON p.category_id = c.id
                         LEFT JOIN stock s ON p.id = s.product_id
                         WHERE p.provider_id = :provider_id AND p.status = "active"
                         ORDER BY p.name ASC');
        $this->db->bind(':provider_id', $provider_id);
        return $this->db->resultset();
    }

    // Get products by category
    public function getByCategory($category_id) {
        $this->db->query('SELECT p.*, pr.name as provider_name, s.quantity
                         FROM products p
                         LEFT JOIN providers pr ON p.provider_id = pr.id
                         LEFT JOIN stock s ON p.id = s.product_id
                         WHERE p.category_id = :category_id AND p.status = "active"
                         ORDER BY p.name ASC');
        $this->db->bind(':category_id', $category_id);
        return $this->db->resultset();
    }

    // Create new product
    public function create($data) {
        $this->db->beginTransaction();
        
        try {
            // Insert product
            $this->db->query('INSERT INTO products (provider_id, category_id, name, code, description, quota, validity_days, buy_price, sell_price, status) 
                             VALUES (:provider_id, :category_id, :name, :code, :description, :quota, :validity_days, :buy_price, :sell_price, :status)');
            $this->db->bind(':provider_id', $data['provider_id']);
            $this->db->bind(':category_id', $data['category_id']);
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':code', $data['code']);
            $this->db->bind(':description', $data['description']);
            $this->db->bind(':quota', $data['quota']);
            $this->db->bind(':validity_days', $data['validity_days']);
            $this->db->bind(':buy_price', $data['buy_price']);
            $this->db->bind(':sell_price', $data['sell_price']);
            $this->db->bind(':status', $data['status'] ?? 'active');
            
            $this->db->execute();
            $product_id = $this->db->lastInsertId();
            
            // Initialize stock
            $this->db->query('INSERT INTO stock (product_id, quantity, min_stock, max_stock) VALUES (:product_id, 0, :min_stock, :max_stock)');
            $this->db->bind(':product_id', $product_id);
            $this->db->bind(':min_stock', $data['min_stock'] ?? 5);
            $this->db->bind(':max_stock', $data['max_stock'] ?? 100);
            $this->db->execute();
            
            $this->db->endTransaction();
            return $product_id;
        } catch (Exception $e) {
            $this->db->cancelTransaction();
            return false;
        }
    }

    // Update product
    public function update($id, $data) {
        $this->db->query('UPDATE products SET provider_id = :provider_id, category_id = :category_id, name = :name, 
                         code = :code, description = :description, quota = :quota, validity_days = :validity_days, 
                         buy_price = :buy_price, sell_price = :sell_price, status = :status WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':provider_id', $data['provider_id']);
        $this->db->bind(':category_id', $data['category_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':code', $data['code']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':quota', $data['quota']);
        $this->db->bind(':validity_days', $data['validity_days']);
        $this->db->bind(':buy_price', $data['buy_price']);
        $this->db->bind(':sell_price', $data['sell_price']);
        $this->db->bind(':status', $data['status']);
        
        return $this->db->execute();
    }

    // Delete product
    public function delete($id) {
        $this->db->query('DELETE FROM products WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Check if product code exists
    public function codeExists($code, $exclude_id = null) {
        if ($exclude_id) {
            $this->db->query('SELECT id FROM products WHERE code = :code AND id != :exclude_id');
            $this->db->bind(':exclude_id', $exclude_id);
        } else {
            $this->db->query('SELECT id FROM products WHERE code = :code');
        }
        $this->db->bind(':code', $code);
        return $this->db->single() ? true : false;
    }

    // Get total products count
    public function getTotalCount() {
        $this->db->query('SELECT COUNT(*) as total FROM products');
        $result = $this->db->single();
        return $result['total'];
    }

    // Search products
    public function search($keyword, $provider_id = null, $category_id = null) {
        $query = 'SELECT p.*, pr.name as provider_name, c.name as category_name, s.quantity
                  FROM products p
                  LEFT JOIN providers pr ON p.provider_id = pr.id
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN stock s ON p.id = s.product_id
                  WHERE (p.name LIKE :keyword OR p.code LIKE :keyword OR p.description LIKE :keyword)
                  ORDER BY p.name ASC';
        
        if ($provider_id) {
            $query .= ' AND p.provider_id = :provider_id';
        }
        
        if ($category_id) {
            $query .= ' AND p.category_id = :category_id';
        }
        
        
        $this->db->query($query);
        $this->db->bind(':keyword', '%' . $keyword . '%');
        
        if ($provider_id) {
            $this->db->bind(':provider_id', $provider_id);
        }
        
        if ($category_id) {
            $this->db->bind(':category_id', $category_id);
        }
        
        return $this->db->resultset();
    }
}
?>

