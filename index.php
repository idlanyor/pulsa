<?php
require_once 'config.php';
require_once 'database.php';
require_once 'includes/auth.php';

$auth = new Auth();

// Jika pengguna sudah login, arahkan ke dashboard
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Jika tidak, tampilkan halaman login
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dashboard Konter Pulsa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h1>Dashboard Konter Pulsa</h1>
                <p>Sistem Inventory Voucher Data</p>
            </div>
            
            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" id="remember">
                        <span class="checkmark"></span>
                        Ingat saya
                    </label>
                </div>
                
                <button type="submit" class="btn-login">
                    <span class="btn-text">Masuk</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
            
            <div class="login-footer">
                <p>&copy; 2024 Dashboard Konter Pulsa. All rights reserved.</p>
            </div>
        </div>
        
        <!-- <div class="background-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div> -->
    </div>
    
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Memproses login...</p>
        </div>
    </div>
    
    <!-- Alert Modal -->
    <div id="alertModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="alertTitle">Peringatan</h3>
                <button class="modal-close" onclick="closeAlert()">&times;</button>
            </div>
            <div class="modal-body">
                <p id="alertMessage"></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="closeAlert()">OK</button>
            </div>
        </div>
    </div>

    <script src="assets/js/auth.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const toggleBtn = document.querySelector(".toggle-password i");
            
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleBtn.className = "fas fa-eye-slash";
            } else {
                passwordInput.type = "password";
                toggleBtn.className = "fas fa-eye";
            }
        }
        
        function showAlert(title, message, type = "info") {
            document.getElementById("alertTitle").textContent = title;
            document.getElementById("alertMessage").textContent = message;
            document.getElementById("alertModal").style.display = "flex";
        }
        
        function closeAlert() {
            document.getElementById("alertModal").style.display = "none";
        }
        
        function showLoading() {
            document.getElementById("loadingOverlay").style.display = "flex";
        }
        
        function hideLoading() {
            document.getElementById("loadingOverlay").style.display = "none";
        }
    </script>
</body>
</html>

