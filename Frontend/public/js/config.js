// Global frontend config: set API base for different hosts
// The user requested a small, explicit detection snippet for Hostinger vs local dev.
// Ensure this appears at the very top so other scripts can rely on window.APP_API_*.
(function(){
  try {
    // User-provided snippet: prefer hostinger production when hostname includes 'spchospital.com'
    if (window.location && window.location.hostname && window.location.hostname.includes('spchospital.com')) {
      // Production (Hostinger)
      window.APP_API_FOLDER = 'https://spchospital.com/Backend/api/';
    } else {
      // Local development
      window.APP_API_FOLDER = '/doctor-appoinment/Backend/api/';
    }
    window.APP_API_BASE = window.APP_API_FOLDER + 'auth.php';
  } catch (e) {
    // noop - fall through to later heuristics
  }
})();

// Provide a minimal buildApiUrl helper and a conservative fetch rewrite early
(function(){
  try {
    // buildApiUrl: prefer APP_API_BASE for auth.php, else APP_API_FOLDER + endpoint
    window.buildApiUrl = window.buildApiUrl || function(endpoint){
      try {
        endpoint = endpoint || '';
        if (/^https?:\/\//i.test(endpoint)) return endpoint;
        if (endpoint.startsWith('/')) return endpoint; // absolute path intentionally left as-is
        if (endpoint.toLowerCase().startsWith('auth.php')) {
          const suffix = endpoint.substring('auth.php'.length);
          return (window.APP_API_BASE || (window.APP_API_FOLDER + 'auth.php')) + suffix;
        }
        const folder = window.APP_API_FOLDER || (window.location.origin + '/doctor-appoinment/Backend/api/');
        return (folder.endsWith('/') ? folder : folder + '/') + endpoint.replace(/^\/+/, '');
      } catch (e) { return endpoint; }
    };

    // Lightweight fetch wrapper: rewrite string URLs that reference Backend/api or bare auth.php
    if (window.fetch) {
      const originalFetch = window.fetch.bind(window);
      window.fetch = function(input, init) {
        try {
          if (typeof input === 'string') {
            const low = input.toLowerCase();
            if (low.indexOf('backend/api/') !== -1) {
              // extract the part after backend/api/
              const parts = input.split(/backend\/api\//i);
              const rest = parts.length > 1 ? parts[1] : '';
              const folder = window.APP_API_FOLDER || (window.location.origin + '/doctor-appoinment/Backend/api/');
              input = (folder.endsWith('/') ? folder : folder + '/') + rest.replace(/^\/+/, '');
            } else if (/^auth\.php/i.test(input)) {
              const suffix = input.substring('auth.php'.length);
              input = (window.APP_API_BASE || (window.APP_API_FOLDER + 'auth.php')) + suffix;
            }
          }
        } catch (e) {}
        return originalFetch(input, init);
      };
    }
  } catch (e) {}
})();
