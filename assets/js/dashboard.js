// API functions for dashboard
class API {
    constructor() {
        this.baseUrl = window.location.origin;
        this.apiUrl = this.baseUrl + '/api.php';
    }

    async request(endpoint, options = {}) {
        try {
            const response = await fetch(this.apiUrl + endpoint, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });

            const data = await response.json();
            
            if (response.status === 401) {
                // Unauthorized - redirect to login
                auth.logout();
                return null;
            }
            
            return data;
        } catch (error) {
            console.error('API request error:', error);
            throw error;
        }
    }

    // Stock methods
    async getStockSummary() {
        return await this.request('/stock/summary');
    }

    async getLowStock() {
        return await this.request('/stock/low');
    }

    async getStockStatistics() {
        return await this.request('/stock/statistics');
    }

    async stockIn(data) {
        return await this.request('/stock/in', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async stockOut(data) {
        return await this.request('/stock/out', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async adjustStock(data) {
        return await this.request('/stock/adjust', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    // Product methods
    async getProducts(page = 1, limit = 20) {
        return await this.request(`/products?page=${page}&limit=${limit}`);
    }

    async getProduct(id) {
        return await this.request(`/products/${id}`);
    }

    async createProduct(data) {
        return await this.request('/products', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async updateProduct(id, data) {
        return await this.request(`/products/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async deleteProduct(id) {
        return await this.request(`/products/${id}`, {
            method: 'DELETE'
        });
    }

    // Transaction methods
    async getStockInTransactions(page = 1, limit = 20, filters = {}) {
        const params = new URLSearchParams({
            page: page,
            limit: limit,
            ...filters
        });
        return await this.request(`/transactions/in?${params}`);
    }

    async getStockOutTransactions(page = 1, limit = 20, filters = {}) {
        const params = new URLSearchParams({
            page: page,
            limit: limit,
            ...filters
        });
        return await this.request(`/transactions/out?${params}`);
    }

    async getSalesStatistics(period = 'today') {
        return await this.request(`/transactions/statistics?period=${period}`);
    }

    async getSalesReport(dateFrom, dateTo) {
        return await this.request(`/transactions/report?date_from=${dateFrom}&date_to=${dateTo}`);
    }
}

// Initialize API
const api = new API();

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStockStatusBadge(quantity, minStock, maxStock) {
    if (quantity <= minStock) {
        return '<span class="badge badge-danger">Stok Rendah</span>';
    } else if (quantity >= maxStock) {
        return '<span class="badge badge-warning">Stok Berlebih</span>';
    } else {
        return '<span class="badge badge-success">Normal</span>';
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Loading functions
function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
    }
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Modal functions
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// Table functions
function createTable(data, columns, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    let html = `
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        ${columns.map(col => `<th>${col.title}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
    `;
    
    data.forEach(row => {
        html += '<tr>';
        columns.forEach(col => {
            let value = row[col.key];
            if (col.format) {
                value = col.format(value, row);
            }
            html += `<td>${value}</td>`;
        });
        html += '</tr>';
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
}

// Chart functions (using Chart.js if available)
function createChart(canvasId, type, data, options = {}) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || typeof Chart === 'undefined') return;
    
    new Chart(canvas, {
        type: type,
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            ...options
        }
    });
}

// Export functions
function exportToCSV(data, filename) {
    const csv = data.map(row => Object.values(row).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

function printTable(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Print</title>
                <style>
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                </style>
            </head>
            <body>
                ${table.outerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

