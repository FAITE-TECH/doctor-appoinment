// Shared Authentication System for Admin Pages
class SharedAdminAuth {
    constructor() {
        this.isAuthenticated = false;
        this.user = null;
        this.authChecked = false;
        this.init();
    }

    async init() {
        // Check if we already have auth data in localStorage (for persistence across page refreshes)
        const cachedAuth = this.getCachedAuth();
        if (cachedAuth && cachedAuth.timestamp > Date.now() - 1800000) { // 30 minutes cache
            this.isAuthenticated = true;
            this.user = cachedAuth.user;
            this.authChecked = true;
            this.updateUI();
        }
        
        await this.checkAuth();
        this.setupLogoutButton();
        this.updateUI();
    }

    getCachedAuth() {
        try {
            const cached = localStorage.getItem('admin_auth_cache');
            return cached ? JSON.parse(cached) : null;
        } catch (e) {
            return null;
        }
    }

    setCachedAuth(user) {
        try {
            const cacheData = {
                user: user,
                timestamp: Date.now()
            };
            localStorage.setItem('admin_auth_cache', JSON.stringify(cacheData));
        } catch (e) {
            console.warn('Could not cache auth data:', e);
        }
    }

    clearCachedAuth() {
        try {
            localStorage.removeItem('admin_auth_cache');
        } catch (e) {
            console.warn('Could not clear auth cache:', e);
        }
    }

    async checkAuth() {
        try {
            const response = await fetch('/doctor-appoinment/Backend/api/auth.php?action=me', {
                credentials: 'include',
                cache: 'no-cache' // Ensure fresh auth check
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.authenticated && data.user.role === 'admin') {
                    this.isAuthenticated = true;
                    this.user = data.user;
                    this.setCachedAuth(data.user);
                    this.authChecked = true;
                    return true;
                }
            }
            
            this.isAuthenticated = false;
            this.user = null;
            this.clearCachedAuth();
            this.authChecked = true;
            return false;
        } catch (error) {
            console.error('Auth check failed:', error);
            this.isAuthenticated = false;
            this.user = null;
            this.authChecked = true;
            return false;
        }
    }

    setupLogoutButton() {
        const logoutBtn = document.getElementById('adminLogoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                this.logout();
            });
        }
    }

    updateUI() {
        const userInfoElement = document.getElementById('admin-user-info') || document.getElementById('admin-email');
        if (userInfoElement) {
            if (this.isAuthenticated && this.user) {
                userInfoElement.textContent = `${this.user.name} (${this.user.role})`;
            } else {
                userInfoElement.textContent = 'Not authenticated';
            }
        }
    }

    async logout() {
        try {
            await fetch('/doctor-appoinment/Backend/api/auth.php?action=signout', {
                method: 'POST',
                credentials: 'include'
            });
            this.clearCachedAuth();
            window.location.href = '../signin.html';
        } catch (error) {
            console.error('Logout failed:', error);
            this.clearCachedAuth();
            window.location.href = '../signin.html';
        }
    }

    // Helper method to check if user can access admin features
    canAccess() {
        return this.isAuthenticated && this.user && this.user.role === 'admin';
    }

    // Helper method to show authentication status
    showAuthStatus() {
        if (!this.canAccess()) {
            const container = document.querySelector('.main-content') || document.querySelector('.flex-1');
            if (container) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Authentication Required</h2>
                        <p class="text-gray-600 mb-6">You need to be logged in as an administrator to access this page.</p>
                        <a href="../signin.html" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">
                            Go to Login
                        </a>
                    </div>
                `;
            }
        }
    }

    // Method to check if auth has been verified
    isAuthChecked() {
        return this.authChecked;
    }
}

// Initialize shared auth when DOM is loaded
let sharedAuth;
document.addEventListener('DOMContentLoaded', () => {
    sharedAuth = new SharedAdminAuth();
});
