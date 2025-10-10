// Shared Authentication System for Admin Pages
// Guard: avoid redeclaring if this script is loaded twice
if (typeof window.SharedAdminAuth === 'undefined') {
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
            console.log('Checking authentication...');
            // Prefer buildApiUrl (more robust) and fall back to APP_API_BASE.
            let authUrl;
            if (typeof window.buildApiUrl === 'function') {
                authUrl = window.buildApiUrl('auth.php?action=me');
            } else if (window.APP_API_BASE) {
                authUrl = window.APP_API_BASE;
                if (authUrl.startsWith('/')) authUrl = window.location.origin.replace(/\/$/, '') + authUrl;
                authUrl = authUrl.includes('?') ? authUrl + '&action=me' : authUrl + '?action=me';
            } else if (window.APP_API_FOLDER) {
                authUrl = (window.APP_API_FOLDER.endsWith('/') ? window.APP_API_FOLDER : window.APP_API_FOLDER + '/') + 'auth.php?action=me';
            } else {
                authUrl = 'https://spchospital.com/Backend/api/auth.php?action=me';
            }
            const response = await fetch(authUrl, {
                credentials: 'include',
                cache: 'no-cache' // Ensure fresh auth check
            });
            
            console.log('Auth response status:', response.status);
            
            if (response.ok) {
                let data;
                try { data = await response.json(); } catch (e) { throw new Error('Unable to parse JSON from auth endpoint'); }
                console.log('Auth response data:', data);
                
                if (data.authenticated && data.user && data.user.role === 'admin') {
                    this.isAuthenticated = true;
                    this.user = data.user;
                    this.setCachedAuth(data.user);
                    this.authChecked = true;
                    console.log('✅ Admin authenticated successfully');
                    return true;
                } else {
                    console.log('❌ Not authenticated as admin:', data);
                }
            } else {
                console.log('❌ Auth response not ok:', response.status);
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
            // Reuse same resolver used for auth checks to avoid inconsistent endpoints
            let signoutUrl;
            if (typeof window.buildApiUrl === 'function') {
                signoutUrl = window.buildApiUrl('auth.php?action=signout');
            } else if (window.APP_API_BASE) {
                signoutUrl = window.APP_API_BASE;
                if (signoutUrl.startsWith('/')) signoutUrl = window.location.origin.replace(/\/$/, '') + signoutUrl;
                signoutUrl = signoutUrl.includes('?') ? signoutUrl + '&action=signout' : signoutUrl + '?action=signout';
            } else if (window.APP_API_FOLDER) {
                signoutUrl = (window.APP_API_FOLDER.endsWith('/') ? window.APP_API_FOLDER : window.APP_API_FOLDER + '/') + 'auth.php?action=signout';
            } else {
                signoutUrl = 'https://spchospital.com/Backend/api/auth.php?action=signout';
            }
            await fetch(signoutUrl, {
                method: 'POST',
                credentials: 'include'
            });
            this.clearCachedAuth();
            window.location.href = 'login.html';
        } catch (error) {
            console.error('Logout failed:', error);
            this.clearCachedAuth();
            window.location.href = 'login.html';
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
                        <a href="login.html" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">
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

    // Expose the class on window for other scripts and to avoid redeclaration
    window.SharedAdminAuth = SharedAdminAuth;
}

// Initialize shared auth when DOM is loaded (only once)
if (!window.__sharedAdminAuthInitialized) {
    document.addEventListener('DOMContentLoaded', () => {
        try {
            // store on window so dev tools can access it if needed
            window.sharedAuth = new window.SharedAdminAuth();
        } catch (e) {
            console.error('Failed to initialize SharedAdminAuth:', e);
        }
    });
    window.__sharedAdminAuthInitialized = true;
}
