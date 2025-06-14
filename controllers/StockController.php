<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'includes/helper.php';
require_once 'models/Stock.php';
require_once 'models/Transaction.php';
require_once 'models/Product.php';

class StockController {
    private $stock;
    private $transaction;
    private $product;
    private $auth;

    public function __construct() {
        $this->stock = new Stock();
        $this->transaction = new Transaction();
        $this->product = new Product();
        $this->auth = new Auth();
    }

    // Get stock summary
    public function getSummary() {
        return $this->stock->getSummary();
    }

    // Get low stock items
    public function getLowStock() {
        return $this->stock->getLowStock();
    }

    // Get stock statistics
    public function getStatistics() {
        return $this->stock->getStatistics();
    }

    // Stock in (purchase/restock)
    public function stockIn($data) {
        // Validate required fields
        $required = ['product_id', 'quantity', 'buy_price'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['error' => "Field $field is required"];
            }
        }

        // Validate values
        if ($data['quantity'] <= 0) {
            return ['error' => 'Quantity must be greater than 0'];
        }

        if ($data['buy_price'] <= 0) {
            return ['error' => 'Buy price must be greater than 0'];
        }

        // Check if product exists
        $product = $this->product->getById($data['product_id']);
        if (!$product) {
            return ['error' => 'Product not found'];
        }

        // Add created_by
        $data['created_by'] = $_SESSION['username'] ?? 'system';

        $transaction_id = $this->transaction->addStockIn($data);
        if ($transaction_id) {
            return ['success' => true, 'transaction_id' => $transaction_id];
        }
        
        return ['error' => 'Failed to add stock'];
    }

    // Stock out (sale)
    public function stockOut($data) {
        // Validate required fields
        $required = ['product_id', 'quantity', 'sell_price'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['error' => "Field $field is required"];
            }
        }

        // Validate values
        if ($data['quantity'] <= 0) {
            return ['error' => 'Quantity must be greater than 0'];
        }

        if ($data['sell_price'] <= 0) {
            return ['error' => 'Sell price must be greater than 0'];
        }

        // Check if product exists
        $product = $this->product->getById($data['product_id']);
        if (!$product) {
            return ['error' => 'Product not found'];
        }

        // Check stock availability
        if ($product['quantity'] < $data['quantity']) {
            return ['error' => 'Insufficient stock. Available: ' . $product['quantity']];
        }

        // Add created_by
        $data['created_by'] = $_SESSION['username'] ?? 'system';

        $transaction_id = $this->transaction->addStockOut($data);
        if ($transaction_id) {
            return ['success' => true, 'transaction_id' => $transaction_id];
        }
        
        return ['error' => 'Failed to process sale'];
    }

    // Manual stock adjustment
    public function adjustStock($product_id, $new_quantity, $notes = '') {
        // Validate values
        if ($new_quantity < 0) {
            return ['error' => 'Quantity cannot be negative'];
        }

        // Check if product exists
        $product = $this->product->getById($product_id);
        if (!$product) {
            return ['error' => 'Product not found'];
        }

        if ($this->stock->adjustStock($product_id, $new_quantity, $notes)) {
            return ['success' => true];
        }
        
        return ['error' => 'Failed to adjust stock'];
    }

    // Update stock settings
    public function updateSettings($product_id, $min_stock, $max_stock) {
        // Validate values
        if ($min_stock < 0 || $max_stock < 0) {
            return ['error' => 'Stock values cannot be negative'];
        }

        if ($max_stock <= $min_stock) {
            return ['error' => 'Maximum stock must be greater than minimum stock'];
        }

        // Check if product exists
        $product = $this->product->getById($product_id);
        if (!$product) {
            return ['error' => 'Product not found'];
        }

        if ($this->stock->updateSettings($product_id, $min_stock, $max_stock)) {
            return ['success' => true];
        }
        
        return ['error' => 'Failed to update stock settings'];
    }

    // Get stock movements
    public function getMovements($product_id, $limit = 50) {
        return $this->stock->getMovements($product_id, $limit);
    }
}
?>

