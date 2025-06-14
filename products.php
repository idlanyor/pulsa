<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'includes/helper.php';
require_once 'models/Product.php';
require_once 'models/Provider.php';
require_once 'models/Category.php';

$auth = new Auth();
$db = new Database();
$productModel = new Product($db);
$providerModel = new Provider($db);
$categoryModel = new Category($db);

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$user = $auth->getCurrentUser();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        try {
            $data = [
                'provider_id' => $_POST['provider_id'],
                'category_id' => $_POST['category_id'],
                'name' => $_POST['name'],
                'code' => $_POST['code'],
                'quota' => $_POST['quota'],
                'validity_days' => $_POST['validity_days'],
                'buy_price' => $_POST['buy_price'],
                'sell_price' => $_POST['sell_price'],
                'status' => $_POST['status'],
                'description' => $_POST['description']
            ];
            
            if ($productModel->create($data)) {
                $message = 'Produk berhasil ditambahkan.';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'Gagal menambahkan produk: ' . $e->getMessage();
            $messageType = 'danger';
        }
    } elseif (isset($_POST['edit_product'])) {
        try {
            $id = $_POST['id'];
            $data = [
                'provider_id' => $_POST['provider_id'],
                'category_id' => $_POST['category_id'],
                'name' => $_POST['name'],
                'code' => $_POST['code'],
                'quota' => $_POST['quota'],
                'validity_days' => $_POST['validity_days'],
                'buy_price' => $_POST['buy_price'],
                'sell_price' => $_POST['sell_price'],
                'status' => $_POST['status'],
                'description' => $_POST['description']
            ];
            
            if ($productModel->update($id, $data)) {
                $message = 'Produk berhasil diperbarui.';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'Gagal memperbarui produk: ' . $e->getMessage();
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete_product'])) {
        try {
            $id = $_POST['id'];
            if ($productModel->delete($id)) {
                $message = 'Produk berhasil dihapus.';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'Gagal menghapus produk: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

$products = $productModel->getAll();
$providers = $providerModel->getAll();
$categories = $categoryModel->getAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Dashboard Konter Pulsa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/products.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            <main class="products-container">
                <div class="page-header">
                    <h1><i class="fas fa-box"></i> Manajemen Produk</h1>
                    <button class="btn btn-primary" onclick="openAddProductModal()">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?>">
                        <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Produk</th>
                                <th>Provider</th>
                                <th>Kategori</th>
                                <th>Kuota</th>
                                <th>Harga Beli</th>
                                <th>Harga Jual</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada produk.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['id']) ?></td>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= htmlspecialchars($product['provider_name']) ?></td>
                                        <td><?= htmlspecialchars($product['category_name']) ?></td>
                                        <td><?= htmlspecialchars($product['quota']) ?></td>
                                        <td>Rp <?= number_format($product['buy_price'], 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($product['sell_price'], 0, ',', '.') ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $product['status'] ?>">
                                                <?= ucfirst($product['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="openEditProductModal(<?= htmlspecialchars($product['id']) ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="openDeleteModal(<?= htmlspecialchars($product['id']) ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('productModal')">&times;</span>
            <h2><i class="fas fa-box"></i> <span id="modalTitle">Tambah Produk Baru</span></h2>
            <form method="POST" id="productForm" class="settings-form">
                <input type="hidden" id="productId" name="id">
                <div class="form-group">
                    <label for="providerId">
                        <i class="fas fa-building"></i> Provider
                    </label>
                    <select id="providerId" name="provider_id" required>
                        <?php foreach ($providers as $provider): ?>
                            <option value="<?= htmlspecialchars($provider['id']) ?>"><?= htmlspecialchars($provider['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="categoryId">
                        <i class="fas fa-tags"></i> Kategori
                    </label>
                    <select id="categoryId" name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="productName">
                        <i class="fas fa-box"></i> Nama Produk
                    </label>
                    <input type="text" id="productName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="productCode">
                        <i class="fas fa-barcode"></i> Kode Produk (Opsional)
                    </label>
                    <input type="text" id="productCode" name="code">
                </div>
                <div class="form-group">
                    <label for="quota">
                        <i class="fas fa-database"></i> Kuota
                    </label>
                    <input type="text" id="quota" name="quota" placeholder="Contoh: 1GB, Unlimited" required>
                </div>
                <div class="form-group">
                    <label for="validityDays">
                        <i class="fas fa-calendar"></i> Masa Aktif (Hari)
                    </label>
                    <input type="number" id="validityDays" name="validity_days" min="1">
                </div>
                <div class="form-group">
                    <label for="buyPrice">
                        <i class="fas fa-shopping-cart"></i> Harga Beli
                    </label>
                    <input type="number" id="buyPrice" name="buy_price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="sellPrice">
                        <i class="fas fa-tag"></i> Harga Jual
                    </label>
                    <input type="number" id="sellPrice" name="sell_price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="status">
                        <i class="fas fa-toggle-on"></i> Status
                    </label>
                    <select id="status" name="status">
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">
                        <i class="fas fa-align-left"></i> Deskripsi
                    </label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" name="add_product" id="addProductBtn" class="btn btn-primary">
                        <i class="fas fa-save"></i> Tambah
                    </button>
                    <button type="submit" name="edit_product" id="editProductBtn" class="btn btn-primary" style="display:none;">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('productModal')">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Product Modal -->
    <div id="deleteProductModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('deleteProductModal')">&times;</span>
            <h2><i class="fas fa-trash"></i> Hapus Produk</h2>
            <p>Anda yakin ingin menghapus produk ini?</p>
            <form method="POST">
                <input type="hidden" id="deleteProductId" name="id">
                <div class="form-actions">
                    <button type="submit" name="delete_product" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteProductModal')">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
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

        function openAddProductModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Produk Baru';
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('addProductBtn').style.display = 'block';
            document.getElementById('editProductBtn').style.display = 'none';
            openModal('productModal');
        }

        function openEditProductModal(id) {
            document.getElementById('modalTitle').textContent = 'Edit Produk';
            document.getElementById('productId').value = id;
            document.getElementById('addProductBtn').style.display = 'none';
            document.getElementById('editProductBtn').style.display = 'block';
            
            // Fetch product data
            fetch(`api.php/products/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.data;
                        document.getElementById('providerId').value = product.provider_id;
                        document.getElementById('categoryId').value = product.category_id;
                        document.getElementById('productName').value = product.name;
                        document.getElementById('productCode').value = product.code;
                        document.getElementById('quota').value = product.quota;
                        document.getElementById('validityDays').value = product.validity_days;
                        document.getElementById('buyPrice').value = product.buy_price;
                        document.getElementById('sellPrice').value = product.sell_price;
                        document.getElementById('status').value = product.status;
                        document.getElementById('description').value = product.description;
                    } else {
                        alert('Gagal memuat data produk: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat data produk');
                });

            openModal('productModal');
        }

        function openDeleteModal(id) {
            document.getElementById('deleteProductId').value = id;
            openModal('deleteProductModal');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        };

        // Format currency inputs
        document.getElementById('buyPrice').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        document.getElementById('sellPrice').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>

