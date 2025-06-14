<?php
$current_user = $auth->getCurrentUser();
?>
<header class="header">
    <div class="header-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="header-title"><?= ucfirst(str_replace('.php', '', basename($_SERVER['PHP_SELF']))) ?></h1>
    </div>
    
    <div class="header-actions">
        <button class="btn-icon" onclick="refreshData()" title="Refresh">
            <i class="fas fa-sync-alt"></i>
        </button>
        
        <div class="user-menu" onclick="toggleUserMenu()">
            <div class="user-avatar">
                <?= strtoupper(substr($current_user['full_name'], 0, 1)) ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($current_user['full_name']) ?></div>
                <div class="user-role"><?= ucfirst($current_user['role']) ?></div>
            </div>
            <i class="fas fa-chevron-down"></i>
        </div>
        
        <div class="user-dropdown" id="userDropdown">
            <a href="profile.php" class="dropdown-item">
                <i class="fas fa-user"></i>
                Profil
            </a>
            <a href="#" onclick="logout()" class="dropdown-item">
                <i class="fas fa-sign-out-alt"></i>
                Keluar
            </a>
        </div>
    </div>
</header>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}

function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('active');
}

// Close user menu when clicking outside
document.addEventListener('click', function(e) {
    const userMenu = document.querySelector('.user-menu');
    const dropdown = document.getElementById('userDropdown');
    
    if (!userMenu.contains(e.target)) {
        dropdown.classList.remove('active');
    }
});

function refreshData() {
    window.location.reload();
}

function logout() {
    if (confirm('Apakah Anda yakin ingin keluar?')) {
        window.location.href = 'logout.php';
    }
}
</script> 