// Admin Panel JavaScript
class AdminPanel {
    constructor() {
        this.apiBase = '/doctor-appoinment/Backend/api/admin.php';
        this.init();
    }

    async init() {
        try {
            // Wait for shared auth to initialize if it exists
            if (typeof sharedAuth !== 'undefined') {
                await sharedAuth.init();
                if (!sharedAuth.canAccess()) {
                    // Don't redirect immediately, just show a warning
                    const userInfoEl = document.getElementById('admin-user-info');
                    const welcomeEl = document.getElementById('welcomeMessage');
                    if (userInfoEl) userInfoEl.textContent = 'Not authenticated';
                    if (welcomeEl) welcomeEl.textContent = 'Please log in to access admin features.';
                    return;
                }
            }
            
            await this.checkAdminAuth();
            await this.loadDashboardStats();
            await this.loadRecentAppointments();
            await this.loadRecentMessages();
            this.setupEventListeners();
        } catch (error) {
            console.error('AdminPanel initialization failed:', error);
            // Set error state for user info
            const userInfoEl = document.getElementById('admin-user-info');
            const welcomeEl = document.getElementById('welcomeMessage');
            if (userInfoEl) userInfoEl.textContent = 'Initialization error';
            if (welcomeEl) welcomeEl.textContent = 'Failed to initialize admin panel. Please refresh the page.';
        }
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
            console.log('AdminPanel: Checking admin authentication...');
            const data = await this.handleApiCall('/doctor-appoinment/Backend/api/auth.php?action=me');
            console.log('AdminPanel: Auth response:', data);
            
            if (data.authenticated && data.user && data.user.role === 'admin') {
                // User is authenticated as admin
                const userInfoEl = document.getElementById('admin-user-info');
                const welcomeEl = document.getElementById('welcomeMessage');
                
                if (userInfoEl) {
                    userInfoEl.textContent = `${data.user.name} (${data.user.role})`;
                }
                if (welcomeEl) {
                    welcomeEl.textContent = `Welcome, ${data.user.name}! You are logged in as an administrator.`;
                }
                console.log('✅ AdminPanel: Admin authenticated successfully');
                return true;
            } else {
                // User is not authenticated as admin, but don't redirect
                const userInfoEl = document.getElementById('admin-user-info');
                const welcomeEl = document.getElementById('welcomeMessage');
                
                if (userInfoEl) {
                    userInfoEl.textContent = 'Not authenticated';
                }
                if (welcomeEl) {
                    welcomeEl.textContent = 'Please log in to access admin features.';
                }
                console.log('❌ AdminPanel: Not authenticated as admin');
                return false;
            }
        } catch (error) {
            console.error('AdminPanel: Auth check failed:', error);
            const userInfoEl = document.getElementById('admin-user-info');
            const welcomeEl = document.getElementById('welcomeMessage');
            
            if (userInfoEl) {
                userInfoEl.textContent = 'Authentication error';
            }
            if (welcomeEl) {
                welcomeEl.textContent = 'Unable to verify authentication.';
            }
            return false;
        }
    }

    async loadDashboardStats() {
        try {
            const data = await this.handleApiCall(this.apiBase + '?action=dashboard_stats');
            
            // Helper function to safely set text content
            const setTextContent = (elementId, value) => {
                const element = document.getElementById(elementId);
                if (element) {
                    element.textContent = value;
                } else {
                    console.warn(`Element with id '${elementId}' not found`);
                }
            };
            
            if (data.success) {
                setTextContent('totalDoctors', data.stats.doctors || 0);
                setTextContent('totalAppointments', data.stats.appointments || 0);
                setTextContent('totalUsers', data.stats.users || 0);
                setTextContent('totalEvents', data.stats.events || 0);
            } else {
                console.error('Failed to load stats:', data.error);
                // Set default values
                setTextContent('totalDoctors', '0');
                setTextContent('totalAppointments', '0');
                setTextContent('totalUsers', '0');
                setTextContent('totalEvents', '0');
            }
            
            // Load message count separately
            await this.loadMessageCount();
            await this.loadUnreadMessagesCount();
        } catch (error) {
            console.error('Failed to load dashboard stats:', error);
            // Set default values on error
            const setTextContent = (elementId, value) => {
                const element = document.getElementById(elementId);
                if (element) {
                    element.textContent = value;
                } else {
                    console.warn(`Element with id '${elementId}' not found`);
                }
            };
            
            setTextContent('totalDoctors', '0');
            setTextContent('totalAppointments', '0');
            setTextContent('totalUsers', '0');
            setTextContent('totalEvents', '0');
            setTextContent('totalMessages', '0');
        }
    }

    async loadMessageCount() {
        try {
            const data = await this.handleApiCall('../../../Backend/api/contact.php?page=1&limit=1');
            const element = document.getElementById('totalMessages');
            if (element) {
                if (data.success && data.pagination) {
                    element.textContent = data.pagination.total || 0;
                } else {
                    element.textContent = '0';
                }
            } else {
                console.warn("Element with id 'totalMessages' not found");
            }
        } catch (error) {
            console.error('Failed to load message count:', error);
            const element = document.getElementById('totalMessages');
            if (element) {
                element.textContent = '0';
            } else {
                console.warn("Element with id 'totalMessages' not found");
            }
        }
    }

    async loadUnreadMessagesCount() {
        try {
            const data = await this.handleApiCall('../../../Backend/api/contact.php?page=1&limit=1&status=new');
            const badge = document.getElementById('unreadMessagesBadge');
            if (data.success && data.pagination && data.pagination.total > 0) {
                badge.textContent = data.pagination.total;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        } catch (error) {
            console.error('Failed to load unread messages count:', error);
            const badge = document.getElementById('unreadMessagesBadge');
            if (badge) badge.classList.add('hidden');
        }
    }

    async loadRecentAppointments() {
        try {
            const data = await this.handleApiCall(this.apiBase + '?action=recent_appointments');
            const container = document.getElementById('recentAppointments');
            
            if (!container) {
                console.log('Recent appointments container not found on this page');
                return;
            }
            
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

    async loadRecentMessages() {
        try {
            const data = await this.handleApiCall('../../../Backend/api/contact.php?page=1&limit=5');
            const container = document.getElementById('recentMessages');
            
            if (!container) {
                console.log('Recent messages container not found on this page');
                return;
            }
            
            if (data.success && data.messages && data.messages.length > 0) {
                container.innerHTML = data.messages.map(message => `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">${message.first_name} ${message.last_name}</p>
                            <p class="text-sm text-gray-600">${message.subject}</p>
                            <p class="text-xs text-gray-500">${this.formatDate(message.created_at)}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full ${
                            message.status === 'new' ? 'bg-red-100 text-red-800' :
                            message.status === 'read' ? 'bg-yellow-100 text-yellow-800' :
                            message.status === 'replied' ? 'bg-green-100 text-green-800' :
                            'bg-gray-100 text-gray-800'
                        }">${message.status}</span>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-gray-500">No recent messages</p>';
            }
        } catch (error) {
            console.error('Failed to load recent messages:', error);
            const container = document.getElementById('recentMessages');
            if (container) {
                container.innerHTML = '<p class="text-red-500">Failed to load messages. Check console for details.</p>';
            }
        }
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
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
    // Small delay to ensure all DOM elements are fully loaded
    setTimeout(() => {
        new AdminPanel();
    }, 100);
});

// Add global error handler for uncaught errors
window.addEventListener('error', (event) => {
    console.error('Global error:', event.error);
});

// Add global promise rejection handler
window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled promise rejection:', event.reason);
});