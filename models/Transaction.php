<?php
require_once 'database.php';

class Transaction {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Add stock in (purchase/restock)
    public function addStockIn($data) {
        $this->db->query('INSERT INTO stock_in (product_id, quantity, buy_price, supplier, invoice_number, notes, created_by) 
                         VALUES (:product_id, :quantity, :buy_price, :supplier, :invoice_number, :notes, :created_by)');
        $this->db->bind(':product_id', $data['product_id']);
        $this->db->bind(':quantity', $data['quantity']);
        $this->db->bind(':buy_price', $data['buy_price']);
        $this->db->bind(':supplier', $data['supplier']);
        $this->db->bind(':invoice_number', $data['invoice_number']);
        $this->db->bind(':notes', $data['notes']);
        $this->db->bind(':created_by', $data['created_by']);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Add stock out (sale)
    public function addStockOut($data) {
        $this->db->beginTransaction();
        
        try {
            // Check if enough stock available
            $this->db->query('SELECT quantity FROM stock WHERE product_id = :product_id');
            $this->db->bind(':product_id', $data['product_id']);
            $stock = $this->db->single();
            
            if (!$stock || $stock['quantity'] < $data['quantity']) {
                throw new Exception('Insufficient stock');
            }
            
            // Add stock out record
            $this->db->query('INSERT INTO stock_out (product_id, quantity, sell_price, customer_name, customer_phone, payment_method, notes, created_by) 
                             VALUES (:product_id, :quantity, :sell_price, :customer_name, :customer_phone, :payment_method, :notes, :created_by)');
            $this->db->bind(':product_id', $data['product_id']);
            $this->db->bind(':quantity', $data['quantity']);
            $this->db->bind(':sell_price', $data['sell_price']);
            $this->db->bind(':customer_name', $data['customer_name']);
            $this->db->bind(':customer_phone', $data['customer_phone']);
            $this->db->bind(':payment_method', $data['payment_method']);
            $this->db->bind(':notes', $data['notes']);
            $this->db->bind(':created_by', $data['created_by']);
            
            $this->db->execute();
            $transaction_id = $this->db->lastInsertId();
            
            $this->db->endTransaction();
            return $transaction_id;
        } catch (Exception $e) {
            $this->db->cancelTransaction();
            return false;
        }
    }

    // Get stock in transactions
    public function getStockIn($limit = null, $offset = null, $product_id = null, $date_from = null, $date_to = null) {
        $query = 'SELECT si.*, p.name as product_name, p.code as product_code, pr.name as provider_name
                  FROM stock_in si
                  JOIN products p ON si.product_id = p.id
                  JOIN providers pr ON p.provider_id = pr.id
                  WHERE 1=1';
        
        if ($product_id) {
            $query .= ' AND si.product_id = :product_id';
        }
        
        if ($date_from) {
            $query .= ' AND DATE(si.transaction_date) >= :date_from';
        }
        
        if ($date_to) {
            $query .= ' AND DATE(si.transaction_date) <= :date_to';
        }
        
        $query .= ' ORDER BY si.transaction_date DESC';
        
        if ($limit) {
            $query .= ' LIMIT :limit';
            if ($offset) {
                $query .= ' OFFSET :offset';
            }
        }
        
        $this->db->query($query);
        
        if ($product_id) {
            $this->db->bind(':product_id', $product_id);
        }
        
        if ($date_from) {
            $this->db->bind(':date_from', $date_from);
        }
        
        if ($date_to) {
            $this->db->bind(':date_to', $date_to);
        }
        
        if ($limit) {
            $this->db->bind(':limit', $limit);
            if ($offset) {
                $this->db->bind(':offset', $offset);
            }
        }
        
        return $this->db->resultset();
    }

    // Get stock out transactions
    public function getStockOut($limit = null, $offset = null, $product_id = null, $date_from = null, $date_to = null) {
        $query = 'SELECT so.*, p.name as product_name, p.code as product_code, pr.name as provider_name
                  FROM stock_out so
                  JOIN products p ON so.product_id = p.id
                  JOIN providers pr ON p.provider_id = pr.id
                  WHERE 1=1';
        
        if ($product_id) {
            $query .= ' AND so.product_id = :product_id';
        }
        
        if ($date_from) {
            $query .= ' AND DATE(so.transaction_date) >= :date_from';
        }
        
        if ($date_to) {
            $query .= ' AND DATE(so.transaction_date) <= :date_to';
        }
        
        $query .= ' ORDER BY so.transaction_date DESC';
        
        if ($limit) {
            $query .= ' LIMIT :limit';
            if ($offset) {
                $query .= ' OFFSET :offset';
            }
        }
        
        $this->db->query($query);
        
        if ($product_id) {
            $this->db->bind(':product_id', $product_id);
        }
        
        if ($date_from) {
            $this->db->bind(':date_from', $date_from);
        }
        
        if ($date_to) {
            $this->db->bind(':date_to', $date_to);
        }
        
        if ($limit) {
            $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
            if ($offset) {
                $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
            }
        }
        
        return $this->db->resultset();
    }

    // Get transaction by ID
    public function getStockInById($id) {
        $this->db->query('SELECT si.*, p.name as product_name, p.code as product_code
                         FROM stock_in si
                         JOIN products p ON si.product_id = p.id
                         WHERE si.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getStockOutById($id) {
        $this->db->query('SELECT so.*, p.name as product_name, p.code as product_code
                         FROM stock_out so
                         JOIN products p ON so.product_id = p.id
                         WHERE so.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Get daily sales summary
    public function getDailySales($date_from = null, $date_to = null) {
        $query = 'SELECT * FROM v_daily_sales WHERE 1=1';
        
        if ($date_from) {
            $query .= ' AND sale_date >= :date_from';
        }
        
        if ($date_to) {
            $query .= ' AND sale_date <= :date_to';
        }
        
        $query .= ' ORDER BY sale_date DESC';
        
        $this->db->query($query);
        
        if ($date_from) {
            $this->db->bind(':date_from', $date_from);
        }
        
        if ($date_to) {
            $this->db->bind(':date_to', $date_to);
        }
        
        return $this->db->resultset();
    }

    // Get sales statistics
    public function getSalesStatistics($period = 'today') {
        $stats = [];
        
        switch ($period) {
            case 'today':
                $date_condition = 'DATE(transaction_date) = CURDATE()';
                break;
            case 'week':
                $date_condition = 'YEARWEEK(transaction_date) = YEARWEEK(NOW())';
                break;
            case 'month':
                $date_condition = 'YEAR(transaction_date) = YEAR(NOW()) AND MONTH(transaction_date) = MONTH(NOW())';
                break;
            case 'year':
                $date_condition = 'YEAR(transaction_date) = YEAR(NOW())';
                break;
            default:
                $date_condition = '1=1';
        }
        
        // Total sales
        $this->db->query("SELECT COUNT(*) as total_transactions, 
                                 COALESCE(SUM(quantity), 0) as total_quantity,
                                 COALESCE(SUM(total_revenue), 0) as total_revenue
                         FROM stock_out WHERE $date_condition");
        $result = $this->db->single();
        $stats['sales'] = $result;
        
        // Total purchases
        $this->db->query("SELECT COUNT(*) as total_transactions, 
                                 COALESCE(SUM(quantity), 0) as total_quantity,
                                 COALESCE(SUM(total_cost), 0) as total_cost
                         FROM stock_in WHERE $date_condition");
        $result = $this->db->single();
        $stats['purchases'] = $result;
        
        // Calculate profit
        $stats['profit'] = $stats['sales']['total_revenue'] - $stats['purchases']['total_cost'];
        
        return $stats;
    }
}
?>

