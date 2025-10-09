// Basic frontend auth and navigation wiring
(function () {
  // Ensure production host overrides are applied as early as possible.
  try {
    var _host = (window && window.location && window.location.hostname) ? window.location.hostname : '';
    if (_host && _host.indexOf('spchospital.com') !== -1) {
      // Force canonical production API endpoints so relative path heuristics don't produce incorrect URLs
      window.APP_API_FOLDER = window.APP_API_FOLDER || 'https://spchospital.com/Backend/api/';
      window.APP_API_BASE = window.APP_API_BASE || 'https://spchospital.com/Backend/api/auth.php';
    }
  } catch (e) {
    // noop
  }

  // Compute a project root fallback (detect either 'Frontend' or known project folder)
  function computeProjectRoot() {
    const path = window.location.pathname || '';
    let projectRoot = '';
    const frontendIdx = path.indexOf('/Frontend');
    if (frontendIdx !== -1) {
      projectRoot = path.substring(0, frontendIdx);
    } else if (path.indexOf('/doctor-appoinment') !== -1) {
      projectRoot = '/doctor-appoinment';
    } else if (path.indexOf('/doctor-appointment') !== -1) {
      // Accept both spellings but prefer the repo's actual folder name
      projectRoot = '/doctor-appoinment';
    }
    if (projectRoot && !projectRoot.startsWith('/')) projectRoot = '/' + projectRoot;
    projectRoot = projectRoot.replace(/\/+$|\/?$/,'').replace(/\/+$/,'');
    // normalize: remove trailing slashes
    projectRoot = projectRoot.replace(/\/+$/,'');
    return projectRoot;
  }

  // Prefer values already set in `config.js` (or by other early scripts)
  window.APP_API_FOLDER = window.APP_API_FOLDER || (function(){
    // fallback: compute a reasonable default when config wasn't loaded
    try {
      const host = window.location.hostname || '';
      const root = computeProjectRoot();
      if (host && host.indexOf('spchospital.com') !== -1) return 'https://spchospital.com/Backend/api/';
      return window.location.origin + (root || '/doctor-appoinment') + '/Backend/api/';
    } catch (e) { return window.location.origin + '/doctor-appoinment/Backend/api/'; }
  })();

  window.APP_API_BASE = window.APP_API_BASE || (window.APP_API_FOLDER + 'auth.php');

  function getAuthEndpoint() {
    return window.APP_API_BASE;
  }

  async function ensureApiBase(timeoutMs = 3000) {
    const candidates = [
      window.APP_API_BASE,
      window.APP_API_FOLDER + 'auth.php',
      // some environments may have lowercase 'backend' or alternative project root spelling
      (window.APP_API_FOLDER || '').replace('/Backend/api/', '/backend/api/') + 'auth.php',
      (window.APP_API_FOLDER || '').replace('/doctor-appointment/', '/doctor-appoinment/') + 'auth.php',
      window.location.origin + computeProjectRoot() + '/Backend/api/auth.php'
    ].filter(Boolean).map((c, i, arr) => arr.indexOf(c) === i ? c : null).filter(Boolean);

    for (const candidate of candidates) {
      try {
        const controller = new AbortController();
        const id = setTimeout(() => controller.abort(), timeoutMs);
        const res = await fetch(candidate + '?action=me', { credentials: 'include', signal: controller.signal });
        clearTimeout(id);
        if (!res.ok) continue;
        const ct = res.headers.get('content-type') || '';
        if (!ct.includes('application/json')) continue;
        window.APP_API_BASE = candidate;
        console.log('Using API endpoint:', candidate);
        return candidate;
      } catch (e) {
        // try next
      }
    }
    console.warn('No working API endpoint detected; falling back to', window.APP_API_BASE);
    return window.APP_API_BASE;
  }

  async function parseJsonSafe(res) {
    const ct = res.headers.get('content-type') || '';
    if (!ct.includes('application/json')) {
      const text = await res.text();
      const snippet = text.slice(0, 200);
      throw new Error('Expected JSON response but received: ' + snippet);
    }
    return res.json();
  }

  function byId(id) { return document.getElementById(id); }

  function setAuthUI(user) {
    const guestLinks = document.querySelectorAll('[data-auth="guest"]');
    const userItems = document.querySelectorAll('[data-auth="user"]');
    const adminItems = document.querySelectorAll('[data-auth="admin"]');
    const emailSpan = byId('nav-user-email');
    const adminLink = byId('adminLink');
    if (user && user.email) {
      guestLinks.forEach(el => el.classList.add('hidden'));
      userItems.forEach(el => el.classList.remove('hidden'));
      if (user.role === 'admin') {
        adminItems.forEach(el => el.classList.remove('hidden'));
        if (adminLink) adminLink.classList.remove('hidden');
      } else {
        adminItems.forEach(el => el.classList.add('hidden'));
        if (adminLink) adminLink.classList.add('hidden');
      }
      if (emailSpan) emailSpan.textContent = user.email;
    } else {
      guestLinks.forEach(el => el.classList.remove('hidden'));
      userItems.forEach(el => el.classList.add('hidden'));
      adminItems.forEach(el => el.classList.add('hidden'));
      if (emailSpan) emailSpan.textContent = '';
      if (adminLink) adminLink.classList.add('hidden');
    }
  }

  async function fetchMe() {
    try {
      const res = await fetch(getAuthEndpoint() + '?action=me', { credentials: 'include' });
      if (!res.ok) return null;
      const data = await parseJsonSafe(res);
      return data && data.authenticated ? data.user : null;
    } catch (e) {
      console.warn('fetchMe error:', e.message || e);
      return null;
    }
  }

  async function initAuthUI() {
    const user = await fetchMe();
    setAuthUI(user);
  }

  async function handleSigninSubmit(e) {
    e.preventDefault();
    const email = byId('email')?.value?.trim();
    const password = byId('password')?.value;
    if (!email || !password) return;
    try {
      const res = await fetch(getAuthEndpoint() + '?action=signin', {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include', body: JSON.stringify({ email, password })
      });
      const data = await parseJsonSafe(res);
      if (!res.ok) throw new Error(data?.error || ('Signin failed (status ' + res.status + ')'));
      if (data.user.role === 'admin') window.location.href = './admin/index.html'; else window.location.href = './index.html';
    } catch (err) { alert(err.message); }
  }

  async function handleSignupSubmit(e) {
    e.preventDefault();
    const name = byId('name')?.value?.trim();
    const email = byId('email')?.value?.trim();
    const password = byId('password')?.value;
    if (!name || !email || !password) return;
    try {
      const res = await fetch(getAuthEndpoint() + '?action=signup', {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include', body: JSON.stringify({ name, email, password })
      });
      const data = await parseJsonSafe(res);
      if (!res.ok) throw new Error(data?.error || ('Signup failed (status ' + res.status + ')'));
      if (data.user.role === 'admin') window.location.href = './admin/index.html'; else window.location.href = './index.html';
    } catch (err) { alert(err.message); }
  }

  async function handleSignoutClick() {
    try { await fetch(getAuthEndpoint() + '?action=signout', { method: 'POST', credentials: 'include' }); } catch (e) {}
    window.location.href = './index.html';
  }

  document.addEventListener('DOMContentLoaded', async () => {
    await ensureApiBase();
    await initAuthUI();
    const signinForm = byId('signinForm');
    const signupForm = byId('signupForm');
    const signoutBtn = byId('signoutBtn');
    if (signinForm) signinForm.addEventListener('submit', handleSigninSubmit);
    if (signupForm) signupForm.addEventListener('submit', handleSignupSubmit);
    if (signoutBtn) signoutBtn.addEventListener('click', handleSignoutClick);
  });

  // Re-expose for other scripts (in case other scripts ran earlier)
  window.APP_API_FOLDER = window.APP_API_FOLDER || BASE_URL;
  window.APP_API_BASE = window.APP_API_BASE || (window.APP_API_FOLDER + 'auth.php');

  // Helper: build a canonical API URL for an endpoint (used by many pages)
  // Examples callers use: buildApiUrl('events.php?action=public') or buildApiUrl('auth.php?action=me')
  window.buildApiUrl = window.buildApiUrl || function(endpoint) {
    try {
      endpoint = endpoint || '';
      // If caller passed a full URL or absolute path, return as-is
      if (/^https?:\/\//i.test(endpoint) || endpoint.startsWith('/')) return endpoint;

      // Special-case auth.php to prefer APP_API_BASE (may include host and path adjustments)
      if (endpoint.toLowerCase().startsWith('auth.php')) {
        const suffix = endpoint.substring('auth.php'.length);
        return (window.APP_API_BASE || (window.APP_API_FOLDER + 'auth.php')) + suffix;
      }

      // Otherwise return APP_API_FOLDER + endpoint
      const folder = (window.APP_API_FOLDER || BASE_URL || '');
      if (!folder) return endpoint;
      return (folder.endsWith('/') ? folder : folder + '/') + endpoint.replace(/^\/+/, '');
    } catch (e) {
      return endpoint;
    }
  };

  // Optional global fetch wrapper:
  // Rewrites common Backend/api paths (relative or absolute) to the canonical APP_API_FOLDER / APP_API_BASE
  // This avoids editing every file that uses '../../Backend/api/...' when the app is hosted.
  (function attachFetchRewrite() {
    if (!window.fetch) return;
    const originalFetch = window.fetch.bind(window);

    window.fetch = function(input, init) {
      try {
        // Only rewrite string URLs (leave Request objects alone to avoid streaming/body issues)
        if (typeof input === 'string') {
          const low = input.toLowerCase();
          const marker = 'backend/api/';
          const idx = low.indexOf(marker);
          if (idx !== -1) {
            // capture the path after 'backend/api/' and rebuild using APP_API_FOLDER
            const rest = input.substring(idx + marker.length);
            const folder = (window.APP_API_FOLDER || BASE_URL || '');
            if (folder) {
              input = (folder.endsWith('/') ? folder : folder + '/') + rest.replace(/^\/+/, '');
            }
          } else if (/^auth\.php/i.test(input)) {
            // For bare auth.php URLs, prefer APP_API_BASE
            const suffix = input.substring('auth.php'.length);
            input = (window.APP_API_BASE || (window.APP_API_FOLDER + 'auth.php')) + suffix;
          }
        }
      } catch (e) {
        // if rewrite fails, fall back to original input
      }
      return originalFetch(input, init);
    };
  })();

})();
