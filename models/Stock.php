<?php
require_once 'database.php';

class Stock {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Get stock summary
    public function getSummary() {
        $this->db->query('SELECT * FROM v_stock_summary ORDER BY product_name ASC');
        return $this->db->resultset();
    }

    // Get low stock items
    public function getLowStock() {
        $this->db->query('SELECT * FROM v_stock_summary WHERE stock_status = "Low Stock" ORDER BY quantity ASC');
        return $this->db->resultset();
    }

    // Get stock by product ID
    public function getByProductId($product_id) {
        $this->db->query('SELECT * FROM stock WHERE product_id = :product_id');
        $this->db->bind(':product_id', $product_id);
        return $this->db->single();
    }

    // Update stock settings
    public function updateSettings($product_id, $min_stock, $max_stock) {
        $this->db->query('UPDATE stock SET min_stock = :min_stock, max_stock = :max_stock WHERE product_id = :product_id');
        $this->db->bind(':product_id', $product_id);
        $this->db->bind(':min_stock', $min_stock);
        $this->db->bind(':max_stock', $max_stock);
        return $this->db->execute();
    }

    // Manual stock adjustment
    public function adjustStock($product_id, $new_quantity, $notes = '') {
        $this->db->beginTransaction();
        
        try {
            // Get current stock
            $current_stock = $this->getByProductId($product_id);
            $old_quantity = $current_stock['quantity'];
            $difference = $new_quantity - $old_quantity;
            
            // Update stock
            $this->db->query('UPDATE stock SET quantity = :quantity WHERE product_id = :product_id');
            $this->db->bind(':product_id', $product_id);
            $this->db->bind(':quantity', $new_quantity);
            $this->db->execute();
            
            // Log the adjustment
            if ($difference > 0) {
                // Stock increase - log as stock in
                $this->db->query('INSERT INTO stock_in (product_id, quantity, buy_price, supplier, notes, created_by) 
                                 SELECT :product_id, :quantity, buy_price, "Manual Adjustment", :notes, :created_by 
                                 FROM products WHERE id = :product_id2');
                $this->db->bind(':product_id', $product_id);
                $this->db->bind(':product_id2', $product_id);
                $this->db->bind(':quantity', $difference);
                $this->db->bind(':notes', $notes);
                $this->db->bind(':created_by', $_SESSION['username'] ?? 'system');
                $this->db->execute();
            } elseif ($difference < 0) {
                // Stock decrease - log as stock out
                $this->db->query('INSERT INTO stock_out (product_id, quantity, sell_price, customer_name, notes, created_by) 
                                 SELECT :product_id, :quantity, sell_price, "Manual Adjustment", :notes, :created_by 
                                 FROM products WHERE id = :product_id2');
                $this->db->bind(':product_id', $product_id);
                $this->db->bind(':product_id2', $product_id);
                $this->db->bind(':quantity', abs($difference));
                $this->db->bind(':notes', $notes);
                $this->db->bind(':created_by', $_SESSION['username'] ?? 'system');
                $this->db->execute();
            }
            
            $this->db->endTransaction();
            return true;
        } catch (Exception $e) {
            $this->db->cancelTransaction();
            return false;
        }
    }

    // Get stock movements (in/out) for a product
    public function getMovements($product_id, $limit = 50) {
        $this->db->query('
            (SELECT "in" as type, quantity, buy_price as price, total_cost as total, 
                    supplier as party, invoice_number as reference, notes, transaction_date, created_by
             FROM stock_in WHERE product_id = :product_id1)
            UNION ALL
            (SELECT "out" as type, quantity, sell_price as price, total_revenue as total, 
                    customer_name as party, payment_method as reference, notes, transaction_date, created_by
             FROM stock_out WHERE product_id = :product_id2)
            ORDER BY transaction_date DESC
            LIMIT :limit
        ');
        $this->db->bind(':product_id1', $product_id);
        $this->db->bind(':product_id2', $product_id);
        $this->db->bind(':limit', $limit);
        return $this->db->resultset();
    }

    // Get stock statistics
    public function getStatistics() {
        $stats = [];
        
        // Total products
        $this->db->query('SELECT COUNT(*) as total FROM products WHERE status = "active"');
        $result = $this->db->single();
        $stats['total_products'] = $result['total'];
        
        // Low stock count
        $this->db->query('SELECT COUNT(*) as total FROM v_stock_summary WHERE stock_status = "Low Stock"');
        $result = $this->db->single();
        $stats['low_stock_count'] = $result['total'];
        
        // Total stock value
        $this->db->query('SELECT SUM(stock_value) as total FROM v_stock_summary');
        $result = $this->db->single();
        $stats['total_stock_value'] = $result['total'] ?? 0;
        
        // Out of stock count
        $this->db->query('SELECT COUNT(*) as total FROM v_stock_summary WHERE quantity = 0');
        $result = $this->db->single();
        $stats['out_of_stock_count'] = $result['total'];
        
        return $stats;
    }
}
?>

