<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';
require_once 'includes/helper.php';
require_once 'models/Provider.php';

$auth = new Auth();
$db = new Database();
$providerModel = new Provider($db->getConnection());

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$user = $auth->getCurrentUser();

// Handle CRUD operations
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_provider'])) {
        $name = trim($_POST['name']);
        $code = trim($_POST['code']);
        $status = $_POST['status'];

        try {
            if ($providerModel->create($name, $code, $status)) {
                $message = 'Provider berhasil ditambahkan.';
            } else {
                $error = 'Gagal menambahkan provider.';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif (isset($_POST['edit_provider'])) {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $code = trim($_POST['code']);
        $status = $_POST['status'];

        try {
            if ($providerModel->update($id, $name, $code, $status)) {
                $message = 'Provider berhasil diperbarui.';
            } else {
                $error = 'Gagal memperbarui provider.';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif (isset($_POST['delete_provider'])) {
        $id = $_POST['id'];
        try {
            if ($providerModel->delete($id)) {
                $message = 'Provider berhasil dihapus.';
            } else {
                $error = 'Gagal menghapus provider.';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$providers = $providerModel->getAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Provider - Dashboard Konter Pulsa</title>
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
                    <h1>Manajemen Provider</h1>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h2>Daftar Provider</h2>
                        <button class="btn btn-primary" onclick="openModal('addProviderModal')">Tambah Provider</button>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Provider</th>
                                        <th>Kode</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($providers)): ?>
                                        <tr>
                                            <td colspan="5">Tidak ada provider.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($providers as $provider): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($provider['id']) ?></td>
                                                <td><?= htmlspecialchars($provider['name']) ?></td>
                                                <td><?= htmlspecialchars($provider['code']) ?></td>
                                                <td>
                                                    <span class="badge <?= $provider['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                                        <?= ucfirst($provider['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning" onclick="openEditModal(<?= htmlspecialchars(json_encode($provider)) ?>)">Edit</button>
                                                    <button class="btn btn-sm btn-danger" onclick="openDeleteModal(<?= htmlspecialchars($provider['id']) ?>)">Hapus</button>
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

    <!-- Add Provider Modal -->
    <div id="addProviderModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('addProviderModal')">&times;</span>
            <h2>Tambah Provider Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="providerName">Nama Provider:</label>
                    <input type="text" id="providerName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="providerCode">Kode Provider:</label>
                    <input type="text" id="providerCode" name="code" required>
                </div>
                <div class="form-group">
                    <label for="providerStatus">Status:</label>
                    <select id="providerStatus" name="status">
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                    </select>
                </div>
                <button type="submit" name="add_provider" class="btn btn-primary">Tambah</button>
            </form>
        </div>
    </div>

    <!-- Edit Provider Modal -->
    <div id="editProviderModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('editProviderModal')">&times;</span>
            <h2>Edit Provider</h2>
            <form method="POST">
                <input type="hidden" id="editProviderId" name="id">
                <div class="form-group">
                    <label for="editProviderName">Nama Provider:</label>
                    <input type="text" id="editProviderName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="editProviderCode">Kode Provider:</label>
                    <input type="text" id="editProviderCode" name="code" required>
                </div>
                <div class="form-group">
                    <label for="editProviderStatus">Status:</label>
                    <select id="editProviderStatus" name="status">
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                    </select>
                </div>
                <button type="submit" name="edit_provider" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <!-- Delete Provider Modal -->
    <div id="deleteProviderModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('deleteProviderModal')">&times;</span>
            <h2>Hapus Provider</h2>
            <p>Anda yakin ingin menghapus provider ini?</p>
            <form method="POST">
                <input type="hidden" id="deleteProviderId" name="id">
                <button type="submit" name="delete_provider" class="btn btn-danger">Hapus</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteProviderModal')">Batal</button>
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

        function openEditModal(provider) {
            document.getElementById('editProviderId').value = provider.id;
            document.getElementById('editProviderName').value = provider.name;
            document.getElementById('editProviderCode').value = provider.code;
            document.getElementById('editProviderStatus').value = provider.status;
            openModal('editProviderModal');
        }

        function openDeleteModal(id) {
            document.getElementById('deleteProviderId').value = id;
            openModal('deleteProviderModal');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        };
    </script>
</body>
</html>

