// Admin Panel JavaScript
class AdminPanel {
    constructor() {
        this.apiBase = '/doctor-appoinment/Backend/api/admin.php';
        this.init();
    }

    async init() {
        // Wait for shared auth to initialize if it exists
        if (typeof sharedAuth !== 'undefined') {
            await sharedAuth.init();
            if (!sharedAuth.canAccess()) {
                // Don't redirect immediately, just show a warning
                document.getElementById('admin-user-info').textContent = 'Not authenticated';
                document.getElementById('welcomeMessage').textContent = 'Please log in to access admin features.';
                return;
            }
        }
        
        await this.checkAdminAuth();
        await this.loadDashboardStats();
        await this.loadRecentAppointments();
        this.setupEventListeners();
    }

    async handleApiCall(url, options = {}) {
        try {
            const response = await fetch(url, {
                credentials: 'include',
                cache: 'no-cache', // Ensure fresh data
                ...options
            });
            
            const text = await response.text();
            
            // Try to parse as JSON first
            try {
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                // If it's not JSON, it's probably a PHP error
                console.error('PHP Error Response:', text);
                throw new Error('Server returned an error. Check console for details.');
            }
        } catch (error) {
            console.error('API Call failed:', error);
            throw error;
        }
    }

    async checkAdminAuth() {
        try {
            const data = await this.handleApiCall('/doctor-appoinment/Backend/api/auth.php?action=me');
            
            if (data.authenticated && data.user.role === 'admin') {
                // User is authenticated as admin
                document.getElementById('admin-user-info').textContent = `${data.user.name} (${data.user.role})`;
                document.getElementById('welcomeMessage').textContent = `Welcome, ${data.user.name}! You are logged in as an administrator.`;
                return true;
            } else {
                // User is not authenticated as admin, but don't redirect
                document.getElementById('admin-user-info').textContent = 'Not authenticated';
                document.getElementById('welcomeMessage').textContent = 'Please log in to access admin features.';
                return false;
            }
        } catch (error) {
            console.error('Auth check failed:', error);
            document.getElementById('admin-user-info').textContent = 'Authentication error';
            document.getElementById('welcomeMessage').textContent = 'Unable to verify authentication.';
            return false;
        }
    }

    async loadDashboardStats() {
        try {
            const data = await this.handleApiCall(this.apiBase + '?action=dashboard_stats');
            
            if (data.success) {
                document.getElementById('totalDoctors').textContent = data.stats.doctors || 0;
                document.getElementById('totalAppointments').textContent = data.stats.appointments || 0;
                document.getElementById('totalUsers').textContent = data.stats.users || 0;
                document.getElementById('totalEvents').textContent = data.stats.events || 0;
            } else {
                console.error('Failed to load stats:', data.error);
                // Set default values
                document.getElementById('totalDoctors').textContent = '0';
                document.getElementById('totalAppointments').textContent = '0';
                document.getElementById('totalUsers').textContent = '0';
                document.getElementById('totalEvents').textContent = '0';
            }
        } catch (error) {
            console.error('Failed to load dashboard stats:', error);
            // Set default values on error
            document.getElementById('totalDoctors').textContent = '0';
            document.getElementById('totalAppointments').textContent = '0';
            document.getElementById('totalUsers').textContent = '0';
            document.getElementById('totalEvents').textContent = '0';
        }
    }

    async loadRecentAppointments() {
        try {
            const data = await this.handleApiCall(this.apiBase + '?action=recent_appointments');
            const container = document.getElementById('recentAppointments');
            
            if (data.success && data.appointments && data.appointments.length > 0) {
                container.innerHTML = data.appointments.map(appointment => `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">${appointment.patient_name || 'Unknown Patient'}</p>
                            <p class="text-sm text-gray-600">${appointment.doctor_name || 'Unknown Doctor'} - ${appointment.appointment_date || 'No date'}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full ${
                            appointment.status === 'confirmed' ? 'bg-green-100 text-green-800' :
                            appointment.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                            'bg-gray-100 text-gray-800'
                        }">${appointment.status || 'unknown'}</span>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-gray-500">No recent appointments</p>';
            }
        } catch (error) {
            console.error('Failed to load recent appointments:', error);
            const container = document.getElementById('recentAppointments');
            if (container) {
                container.innerHTML = '<p class="text-red-500">Failed to load appointments. Check console for details.</p>';
            }
        }
    }

    setupEventListeners() {
        const logoutBtn = document.getElementById('adminLogoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                this.logout();
            });
        } else {
            console.warn('Logout button not found');
        }
    }

    async logout() {
        try {
            await this.handleApiCall('/doctor-appoinment/Backend/api/auth.php?action=signout', {
                method: 'POST'
            });
            window.location.href = '../signin.html';
        } catch (error) {
            console.error('Logout failed:', error);
            window.location.href = '../signin.html';
        }
    }
}

// Updated AdminUtils with better error handling
class AdminUtils {
    static async makeRequest(action, method = 'GET', data = null) {
        const apiBase = '/doctor-appoinment/Backend/api/admin.php';
        const options = {
            method,
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        const url = method === 'GET' && data ? 
            `${apiBase}?action=${action}&${new URLSearchParams(data)}` :
            `${apiBase}?action=${action}`;

        try {
            const response = await fetch(url, options);
            const text = await response.text();
            
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON response:', text);
                return { success: false, error: 'Invalid server response' };
            }
        } catch (error) {
            console.error('API request failed:', error);
            return { success: false, error: 'Network error' };
        }
    }

    static showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    static confirmDelete(message = 'Are you sure you want to delete this item?') {
        return confirm(message);
    }

    static formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            return new Date(dateString).toLocaleDateString();
        } catch (e) {
            return 'Invalid date';
        }
    }

    static formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        try {
            return new Date(dateString).toLocaleString();
        } catch (e) {
            return 'Invalid date';
        }
    }
}

// Initialize admin panel when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new AdminPanel();
});

// Add global error handler for uncaught errors
window.addEventListener('error', (event) => {
    console.error('Global error:', event.error);
});

// Add global promise rejection handler
window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled promise rejection:', event.reason);
});