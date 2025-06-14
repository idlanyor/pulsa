<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'includes/helper.php';
require_once 'models/Product.php';
require_once 'models/Stock.php';

$auth = new Auth();
$db = new Database();
$productModel = new Product($db->getConnection());
$stockModel = new Stock($db->getConnection());

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$user = $auth->getCurrentUser();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['stock_in'])) {
        $data = [
            'product_id' => $_POST['product_id'],
            'quantity' => $_POST['quantity'],
            'buy_price' => $_POST['buy_price'],
            'supplier' => $_POST['supplier'],
            'invoice_number' => $_POST['invoice_number'],
            'notes' => $_POST['notes']
        ];
        if ($stockModel->stockIn($data)) {
            $message = 'Stok berhasil ditambahkan.';
        } else {
            $message = 'Gagal menambahkan stok.';
        }
    } elseif (isset($_POST['stock_out'])) {
        $data = [
            'product_id' => $_POST['product_id'],
            'quantity' => $_POST['quantity'],
            'sell_price' => $_POST['sell_price'],
            'customer_name' => $_POST['customer_name'],
            'customer_phone' => $_POST['customer_phone'],
            'payment_method' => $_POST['payment_method'],
            'notes' => $_POST['notes']
        ];
        if ($stockModel->stockOut($data)) {
            $message = 'Stok berhasil dikeluarkan.';
        } else {
            $message = 'Gagal mengeluarkan stok.';
        }
    } elseif (isset($_POST['adjust_stock'])) {
        $data = [
            'product_id' => $_POST['product_id'],
            'quantity' => $_POST['quantity'],
            'notes' => $_POST['notes']
        ];
        if ($stockModel->adjustStock($data)) {
            $message = 'Stok berhasil disesuaikan.';
        } else {
            $message = 'Gagal menyesuaikan stok.';
        }
    }
}

$stockSummary = $stockModel->getSummary();
$products = $productModel->getAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok - Dashboard Konter Pulsa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            <main>
                <div class="page-header">
                    <h1>Manajemen Stok</h1>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h2>Ringkasan Stok</h2>
                        <button class="btn btn-primary" onclick="openModal('stockInModal')">Stok Masuk</button>
                        <button class="btn btn-warning" onclick="openModal('stockOutModal')">Stok Keluar</button>
                        <button class="btn btn-info" onclick="openModal('adjustStockModal')">Sesuaikan Stok</button>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?= $message ?></div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Stok Saat Ini</th>
                                        <th>Harga Beli Rata-rata</th>
                                        <th>Harga Jual Rata-rata</th>
                                        <th>Nilai Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($stockSummary)): ?>
                                        <tr>
                                            <td colspan="5">Tidak ada data stok.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($stockSummary as $stock): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($stock['product_name']) ?></td>
                                                <td><?= htmlspecialchars($stock['current_stock']) ?></td>
                                                <td><?= htmlspecialchars($stock['average_buy_price']) ?></td>
                                                <td><?= htmlspecialchars($stock['average_sell_price']) ?></td>
                                                <td><?= htmlspecialchars($stock['stock_value']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Stock In Modal -->
    <div id="stockInModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('stockInModal')">&times;</span>
            <h2>Stok Masuk</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="stockInProductId">Produk:</label>
                    <select id="stockInProductId" name="product_id" required>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= htmlspecialchars($product['id']) ?>"><?= htmlspecialchars($product['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stockInQuantity">Jumlah:</label>
                    <input type="number" id="stockInQuantity" name="quantity" min="1" required>
                </div>
                <div class="form-group">
                    <label for="stockInBuyPrice">Harga Beli Satuan:</label>
                    <input type="number" id="stockInBuyPrice" name="buy_price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="stockInSupplier">Supplier:</label>
                    <input type="text" id="stockInSupplier" name="supplier">
                </div>
                <div class="form-group">
                    <label for="stockInInvoice">Nomor Invoice:</label>
                    <input type="text" id="stockInInvoice" name="invoice_number">
                </div>
                <div class="form-group">
                    <label for="stockInNotes">Catatan:</label>
                    <textarea id="stockInNotes" name="notes" rows="3"></textarea>
                </div>
                <button type="submit" name="stock_in" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Stock Out Modal -->
    <div id="stockOutModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('stockOutModal')">&times;</span>
            <h2>Stok Keluar</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="stockOutProductId">Produk:</label>
                    <select id="stockOutProductId" name="product_id" required>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= htmlspecialchars($product['id']) ?>"><?= htmlspecialchars($product['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stockOutQuantity">Jumlah:</label>
                    <input type="number" id="stockOutQuantity" name="quantity" min="1" required>
                </div>
                <div class="form-group">
                    <label for="stockOutSellPrice">Harga Jual Satuan:</label>
                    <input type="number" id="stockOutSellPrice" name="sell_price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="stockOutCustomerName">Nama Pelanggan:</label>
                    <input type="text" id="stockOutCustomerName" name="customer_name">
                </div>
                <div class="form-group">
                    <label for="stockOutCustomerPhone">Telepon Pelanggan:</label>
                    <input type="text" id="stockOutCustomerPhone" name="customer_phone">
                </div>
                <div class="form-group">
                    <label for="stockOutPaymentMethod">Metode Pembayaran:</label>
                    <select id="stockOutPaymentMethod" name="payment_method">
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="credit">Kredit</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stockOutNotes">Catatan:</label>
                    <textarea id="stockOutNotes" name="notes" rows="3"></textarea>
                </div>
                <button type="submit" name="stock_out" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Adjust Stock Modal -->
    <div id="adjustStockModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('adjustStockModal')">&times;</span>
            <h2>Sesuaikan Stok</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="adjustProductId">Produk:</label>
                    <select id="adjustProductId" name="product_id" required>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= htmlspecialchars($product['id']) ?>"><?= htmlspecialchars($product['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="adjustQuantity">Jumlah Stok Baru:</label>
                    <input type="number" id="adjustQuantity" name="quantity" min="0" required>
                </div>
                <div class="form-group">
                    <label for="adjustNotes">Catatan Penyesuaian:</label>
                    <textarea id="adjustNotes" name="notes" rows="3"></textarea>
                </div>
                <button type="submit" name="adjust_stock" class="btn btn-primary">Simpan Penyesuaian</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        };
    </script>
</body>
</html>

