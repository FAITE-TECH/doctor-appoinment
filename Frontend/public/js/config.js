// Global frontend config: APP API path detection and helper
(function(){
  const host = (window && window.location && window.location.hostname) ? window.location.hostname : '';

  // Production Hostinger paths (explicit)
  if (host.includes('spchospital.com')) {
    window.APP_API_FOLDER = 'https://spchospital.com/Backend/api/';
    window.APP_API_BASE = 'https://spchospital.com/Backend/api/auth.php';
  } else {
    // Local / development XAMPP fallback
    window.APP_API_FOLDER = window.APP_API_FOLDER || (window.location.origin + '/doctor-appoinment/Backend/api/');
    window.APP_API_BASE = window.APP_API_BASE || (window.APP_API_FOLDER + 'auth.php');
  }

  // Build full API URL for an endpoint like 'doctors.php?action=doctors' or 'auth.php?action=me'
  // Minimal, predictable fallback: return APP_API_FOLDER + endpoint
  // Define buildApiUrl only if it isn't already a function. Some environments
  // or third-party scripts may set a non-function value on this name; using
  // a typeof check prevents accidentally leaving a non-callable value in place.
  if (typeof window.buildApiUrl !== 'function') {
    window.buildApiUrl = function(endpoint){
      try {
        if (!endpoint) return window.APP_API_FOLDER;
        if (/^https?:\/\//i.test(endpoint)) return endpoint;
        if (endpoint.startsWith('/')) return endpoint;
        const lower = endpoint.toLowerCase();
        if (lower.startsWith('auth.php')) {
          const suffix = endpoint.substring('auth.php'.length);
          return (window.APP_API_BASE || (window.APP_API_FOLDER + 'auth.php')) + suffix;
        }
        // Default fallback: append to APP_API_FOLDER
        return (window.APP_API_FOLDER.endsWith('/') ? window.APP_API_FOLDER : window.APP_API_FOLDER + '/') + endpoint.replace(/^\/+/, '');
      } catch (e) {
        return endpoint;
      }
    };
  }

  // Optional: lightweight fetch wrapper that rewrites common relative Backend/api paths to canonical APP_API_FOLDER
  try {
    if (window.fetch) {
      const _fetch = window.fetch.bind(window);
      window.fetch = function(input, init) {
        try {
          if (typeof input === 'string') {
            const low = input.toLowerCase();
            if (low.indexOf('backend/api/') !== -1) {
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
        return _fetch(input, init);
      };
    }
  } catch (e) {}

})();
