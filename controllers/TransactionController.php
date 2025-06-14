<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'includes/helper.php';
require_once 'models/Transaction.php';

class TransactionController {
    private $transaction;
    private $auth;

    public function __construct() {
        $this->transaction = new Transaction();
        $this->auth = new Auth();
    }

    // Get stock in transactions
    public function getStockIn($page = 1, $limit = 20, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $transactions = $this->transaction->getStockIn(
            $limit, 
            $offset, 
            $filters['product_id'] ?? null,
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null
        );
        
        return $transactions;
    }

    // Get stock out transactions
    public function getStockOut($page = 1, $limit = 20, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $transactions = $this->transaction->getStockOut(
            $limit, 
            $offset, 
            $filters['product_id'] ?? null,
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null
        );
        
        return $transactions;
    }

    // Get transaction details
    public function getStockInDetails($id) {
        return $this->transaction->getStockInById($id);
    }

    public function getStockOutDetails($id) {
        return $this->transaction->getStockOutById($id);
    }

    // Get daily sales summary
    public function getDailySales($date_from = null, $date_to = null) {
        return $this->transaction->getDailySales($date_from, $date_to);
    }

    // Get sales statistics
    public function getSalesStatistics($period = 'today') {
        return $this->transaction->getSalesStatistics($period);
    }

    // Generate sales report
    public function generateSalesReport($date_from, $date_to) {
        $daily_sales = $this->getDailySales($date_from, $date_to);
        $statistics = $this->getSalesStatistics();
        
        $report = [
            'period' => [
                'from' => $date_from,
                'to' => $date_to
            ],
            'summary' => [
                'total_days' => count($daily_sales),
                'total_transactions' => array_sum(array_column($daily_sales, 'total_transactions')),
                'total_quantity' => array_sum(array_column($daily_sales, 'total_quantity')),
                'total_revenue' => array_sum(array_column($daily_sales, 'total_revenue')),
                'total_cost' => array_sum(array_column($daily_sales, 'total_cost')),
                'total_profit' => array_sum(array_column($daily_sales, 'total_profit')),
            ],
            'daily_breakdown' => $daily_sales,
            'current_statistics' => $statistics
        ];
        
        return $report;
    }
}
?>

