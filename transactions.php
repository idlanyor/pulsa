<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'includes/helper.php';
require_once 'models/Transaction.php';
require_once 'models/Product.php';

$auth = new Auth();
$db = new Database();
$transactionModel = new Transaction($db->getConnection());
$productModel = new Product($db->getConnection());

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$user = $auth->getCurrentUser();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_transaction_in'])) {
        $data = [
            'product_id' => $_POST['product_id'],
            'quantity' => $_POST['quantity'],
            'buy_price' => $_POST['buy_price'],
            'supplier' => $_POST['supplier'],
            'invoice_number' => $_POST['invoice_number'],
            'notes' => $_POST['notes']
        ];
        if ($transactionModel->addStockIn($data)) {
            $message = 'Transaksi masuk berhasil ditambahkan.';
        } else {
            $message = 'Gagal menambahkan transaksi masuk.';
        }
    } elseif (isset($_POST['add_transaction_out'])) {
        $data = [
            'product_id' => $_POST['product_id'],
            'quantity' => $_POST['quantity'],
            'sell_price' => $_POST['sell_price'],
            'customer_name' => $_POST['customer_name'],
            'customer_phone' => $_POST['customer_phone'],
            'payment_method' => $_POST['payment_method'],
            'notes' => $_POST['notes']
        ];
        if ($transactionModel->addStockOut($data)) {
            $message = 'Transaksi keluar berhasil ditambahkan.';
        } else {
            $message = 'Gagal menambahkan transaksi keluar.';
        }
    } elseif (isset($_POST['delete_transaction'])) {
        $id = $_POST['id'];
        if ($transactionModel->deleteTransaction($id)) {
            $message = 'Transaksi berhasil dihapus.';
        } else {
            $message = 'Gagal menghapus transaksi.';
        }
    }
}

$stockInTransactions = $transactionModel->getStockIn();
$stockOutTransactions = $transactionModel->getStockOut();
$products = $productModel->getAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Dashboard Konter Pulsa</title>
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
                    <h1>Manajemen Transaksi</h1>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h2>Transaksi Masuk (Pembelian)</h2>
                        <button class="btn btn-primary" onclick="openModal('addTransactionInModal')">Tambah Transaksi Masuk</button>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?= $message ?></div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tanggal</th>
                                        <th>Produk</th>
                                        <th>Jumlah</th>
                                        <th>Harga Beli</th>
                                        <th>Supplier</th>
                                        <th>Invoice</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($stockInTransactions)): ?>
                                        <tr>
                                            <td colspan="8">Tidak ada transaksi masuk.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($stockInTransactions as $transaction): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($transaction['id']) ?></td>
                                                <td><?= htmlspecialchars($transaction['transaction_date']) ?></td>
                                                <td><?= htmlspecialchars($transaction['product_name']) ?></td>
                                                <td><?= htmlspecialchars($transaction['quantity']) ?></td>
                                                <td><?= htmlspecialchars($transaction['buy_price']) ?></td>
                                                <td><?= htmlspecialchars($transaction['supplier']) ?></td>
                                                <td><?= htmlspecialchars($transaction['invoice_number']) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger" onclick="openDeleteModal(<?= htmlspecialchars($transaction['id']) ?>)">Hapus</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mt-20">
                    <div class="card-header">
                        <h2>Transaksi Keluar (Penjualan)</h2>
                        <button class="btn btn-primary" onclick="openModal('addTransactionOutModal')">Tambah Transaksi Keluar</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tanggal</th>
                                        <th>Produk</th>
                                        <th>Jumlah</th>
                                        <th>Harga Jual</th>
                                        <th>Pelanggan</th>
                                        <th>Metode Pembayaran</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($stockOutTransactions)): ?>
                                        <tr>
                                            <td colspan="8">Tidak ada transaksi keluar.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($stockOutTransactions as $transaction): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($transaction['id']) ?></td>
                                                <td><?= htmlspecialchars($transaction['transaction_date']) ?></td>
                                                <td><?= htmlspecialchars($transaction['product_name']) ?></td>
                                                <td><?= htmlspecialchars($transaction['quantity']) ?></td>
                                                <td><?= htmlspecialchars($transaction['sell_price']) ?></td>
                                                <td><?= htmlspecialchars($transaction['customer_name']) ?></td>
                                                <td><?= htmlspecialchars($transaction['payment_method']) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger" onclick="openDeleteModal(<?= htmlspecialchars($transaction['id']) ?>)">Hapus</button>
                                                </td>
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

    <!-- Add Transaction In Modal -->
    <div id="addTransactionInModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('addTransactionInModal')">&times;</span>
            <h2>Tambah Transaksi Masuk Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="transactionInProductId">Produk:</label>
                    <select id="transactionInProductId" name="product_id" required>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= htmlspecialchars($product['id']) ?>"><?= htmlspecialchars($product['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="transactionInQuantity">Jumlah:</label>
                    <input type="number" id="transactionInQuantity" name="quantity" min="1" required>
                </div>
                <div class="form-group">
                    <label for="transactionInBuyPrice">Harga Beli Satuan:</label>
                    <input type="number" id="transactionInBuyPrice" name="buy_price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="transactionInSupplier">Supplier:</label>
                    <input type="text" id="transactionInSupplier" name="supplier">
                </div>
                <div class="form-group">
                    <label for="transactionInInvoice">Nomor Invoice:</label>
                    <input type="text" id="transactionInInvoice" name="invoice_number">
                </div>
                <div class="form-group">
                    <label for="transactionInNotes">Catatan:</label>
                    <textarea id="transactionInNotes" name="notes" rows="3"></textarea>
                </div>
                <button type="submit" name="add_transaction_in" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Add Transaction Out Modal -->
    <div id="addTransactionOutModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('addTransactionOutModal')">&times;</span>
            <h2>Tambah Transaksi Keluar Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="transactionOutProductId">Produk:</label>
                    <select id="transactionOutProductId" name="product_id" required>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= htmlspecialchars($product['id']) ?>"><?= htmlspecialchars($product['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="transactionOutQuantity">Jumlah:</label>
                    <input type="number" id="transactionOutQuantity" name="quantity" min="1" required>
                </div>
                <div class="form-group">
                    <label for="transactionOutSellPrice">Harga Jual Satuan:</label>
                    <input type="number" id="transactionOutSellPrice" name="sell_price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="transactionOutCustomerName">Nama Pelanggan:</label>
                    <input type="text" id="transactionOutCustomerName" name="customer_name">
                </div>
                <div class="form-group">
                    <label for="transactionOutCustomerPhone">Telepon Pelanggan:</label>
                    <input type="text" id="transactionOutCustomerPhone" name="customer_phone">
                </div>
                <div class="form-group">
                    <label for="transactionOutPaymentMethod">Metode Pembayaran:</label>
                    <select id="transactionOutPaymentMethod" name="payment_method">
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="credit">Kredit</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="transactionOutNotes">Catatan:</label>
                    <textarea id="transactionOutNotes" name="notes" rows="3"></textarea>
                </div>
                <button type="submit" name="add_transaction_out" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Delete Transaction Modal -->
    <div id="deleteTransactionModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('deleteTransactionModal')">&times;</span>
            <h2>Hapus Transaksi</h2>
            <p>Anda yakin ingin menghapus transaksi ini?</p>
            <form method="POST">
                <input type="hidden" id="deleteTransactionId" name="id">
                <button type="submit" name="delete_transaction" class="btn btn-danger">Hapus</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteTransactionModal')">Batal</button>
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

        function openDeleteModal(id) {
            document.getElementById('deleteTransactionId').value = id;
            openModal('deleteTransactionModal');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        };
    </script>
</body>
</html>

