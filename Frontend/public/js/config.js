// Global frontend config: APP API path detection and helper
(function(){
  const host = (window && window.location && window.location.hostname) ? window.location.hostname : '';

  // Production host uses the deployed absolute API path
  if (host.includes('spchospital.com')) {
    window.APP_API_FOLDER = 'https://spchospital.com/Backend/api/';
  } else {
    // Local / development: build using origin so scripts can work when served from filesystem or dev server
    window.APP_API_FOLDER = window.location.origin + '/doctor-appoinment/Backend/api/';
  }

  // auth.php is a special endpoint (session and cookies). Keep a direct base for it.
  window.APP_API_BASE = window.APP_API_FOLDER + 'auth.php';

  // Build full API URL for an endpoint like 'doctors.php?action=doctors' or 'auth.php?action=me'
  window.buildApiUrl = window.buildApiUrl || function(endpoint){
    try {
      if (!endpoint) return window.APP_API_FOLDER;
      // If caller already passed absolute URL, return it unchanged
      if (/^https?:\/\//i.test(endpoint)) return endpoint;
      // If absolute path (starts with /) return as-is - caller expects site-root path
      if (endpoint.startsWith('/')) return endpoint;
      const lower = endpoint.toLowerCase();
      if (lower.startsWith('auth.php')) {
        const suffix = endpoint.substring('auth.php'.length);
        return (window.APP_API_BASE || (window.APP_API_FOLDER + 'auth.php')) + suffix;
      }
      // Ensure folder ends with slash, endpoint does not start with slash
      const base = (window.APP_API_FOLDER || (window.location.origin + '/doctor-appoinment/Backend/api/'));
      return (base.endsWith('/') ? base : base + '/') + endpoint.replace(/^\/+/, '');
    } catch (e) {
      return endpoint;
    }
  };

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
