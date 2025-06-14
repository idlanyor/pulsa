// Authentication functions
class Auth {
    constructor() {
        this.baseUrl = window.location.origin;
        this.apiUrl = this.baseUrl + '/api.php';
    }

    async login(username, password) {
        try {
            showLoading();
            
            const response = await fetch(this.apiUrl + '/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            });

            const data = await response.json();
            hideLoading();

            if (data.success) {
                // Redirect to dashboard
                window.location.href = 'dashboard.php';
                return true;
            } else {
                showAlert('Login Gagal', data.error || 'Username atau password salah', 'error');
                return false;
            }
        } catch (error) {
            hideLoading();
            showAlert('Error', 'Terjadi kesalahan koneksi', 'error');
            console.error('Login error:', error);
            return false;
        }
    }

    async logout() {
        try {
            await fetch(this.apiUrl + '/auth/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
        } catch (error) {
            console.error('Logout error:', error);
        }
        
        // Redirect to login
        window.location.href = 'index.php';
    }

    async getProfile() {
        try {
            const response = await fetch(this.apiUrl + '/auth/profile', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.error);
            }
        } catch (error) {
            console.error('Get profile error:', error);
            return null;
        }
    }

    isLoggedIn() {
        return localStorage.getItem('user') !== null;
    }

    getCurrentUser() {
        const userData = localStorage.getItem('user');
        return userData ? JSON.parse(userData) : null;
    }

    requireAuth() {
        if (!this.isLoggedIn()) {
            window.location.href = 'index.php';
            return false;
        }
        return true;
    }

    isAdmin() {
        const user = this.getCurrentUser();
        return user && user.role === 'admin';
    }
}

// Initialize auth
const auth = new Auth();

// Login form handler
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                showAlert('Peringatan', 'Username dan password harus diisi', 'warning');
                return;
            }
            
            await auth.login(username, password);
        });
    }
});

// Logout function
function logout() {
    if (confirm('Apakah Anda yakin ingin keluar?')) {
        auth.logout();
    }
}

