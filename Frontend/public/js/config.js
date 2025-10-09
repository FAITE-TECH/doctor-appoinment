// Global frontend config: set API base for different hosts
(function(){
  try {
    var host = (window && window.location && window.location.hostname) ? window.location.hostname : '';
    // Default local dev behavior (will be overridden by script.js logic when appropriate)
    if (host === 'localhost' || host === '127.0.0.1') {
      window.APP_API_FOLDER = window.APP_API_FOLDER || (window.location.origin + '/doctor-appoinment/Backend/api/');
      window.APP_API_BASE = window.APP_API_BASE || (window.APP_API_FOLDER + 'auth.php');
    }
    // Production host override
    if (host && host.indexOf('spchospital.com') !== -1) {
      window.APP_API_FOLDER = 'https://spchospital.com/Backend/api/';
      window.APP_API_BASE = 'https://spchospital.com/Backend/api/auth.php';
    }
  } catch (e) {
    // Safe noop
  }
})();
