// Basic frontend auth and navigation wiring
(function () {
  // Dynamically compute project root and API base so the frontend works
  // whether the app is deployed at the domain root or inside a subfolder
  (function computeApiBase() {
    // Build a robust absolute API base so fetch() always targets the correct PHP endpoint
    // Strategy: detect the project root from the pathname (strip any '/Frontend' segment) and
    // combine it with the current origin to form an absolute URL. This avoids producing
    // relative paths like 'Backend/...' which the browser resolves under the current folder
    // (causing requests to hit '/doctor-appoinment/Frontend/Backend/...').
    const path = window.location.pathname || '';
    let projectRoot = '';
    const frontendIdx = path.indexOf('/Frontend');
    if (frontendIdx !== -1) {
      projectRoot = path.substring(0, frontendIdx);
    } else if (path.indexOf('/doctor-appoinment') !== -1) {
      // fallback if project folder is present in path
      projectRoot = '/doctor-appoinment';
    }
    // Ensure leading slash and no trailing slash
    if (projectRoot && !projectRoot.startsWith('/')) projectRoot = '/' + projectRoot;
    projectRoot = projectRoot.replace(/\/+$/,'');
    // Always use an absolute URL on the same origin
    window.APP_API_BASE = window.location.origin + (projectRoot || '') + '/Backend/api/auth.php';
  })();
  const apiBase = window.APP_API_BASE;

  // Helper to parse JSON safely and surface non-JSON responses for debugging.
  async function parseJsonSafe(res) {
    const ct = res.headers.get('content-type') || '';
    if (!ct.includes('application/json')) {
      // try to extract text for diagnostics (server likely returned HTML error page)
      const text = await res.text();
      const snippet = text.slice(0, 200);
      throw new Error('Expected JSON response but received: ' + snippet);
    }
    return res.json();
  }

  function byId(id) {
    return document.getElementById(id);
  }

  function setAuthUI(user) {
    const guestLinks = document.querySelectorAll('[data-auth="guest"]');
    const userItems = document.querySelectorAll('[data-auth="user"]');
    const adminItems = document.querySelectorAll('[data-auth="admin"]');
    const emailSpan = byId('nav-user-email');
    const adminLink = byId('adminLink');
    
    if (user && user.email) {
      guestLinks.forEach(el => el.classList.add('hidden'));
      userItems.forEach(el => el.classList.remove('hidden'));
      
      // Show admin panel link for admin users
      if (user.role === 'admin') {
        adminItems.forEach(el => el.classList.remove('hidden'));
        if (adminLink) {
          adminLink.classList.remove('hidden');
          console.log('Admin panel link shown for:', user.email);
        }
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
      const res = await fetch(apiBase + '?action=me', { credentials: 'include' });
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
      const res = await fetch(apiBase + '?action=signin', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ email, password }),
      });
      const data = await parseJsonSafe(res);
      if (!res.ok) throw new Error(data?.error || ('Signin failed (status ' + res.status + ')'));
      
      // Check if user is admin and redirect accordingly
      if (data.user.role === 'admin') {
        // Always redirect admin users to admin dashboard
        window.location.href = './admin/index.html';
      } else {
        window.location.href = './index.html';
      }
    } catch (err) {
      alert(err.message);
    }
  }

  async function handleSignupSubmit(e) {
    e.preventDefault();
    const name = byId('name')?.value?.trim();
    const email = byId('email')?.value?.trim();
    const password = byId('password')?.value;
    if (!name || !email || !password) return;
    try {
      const res = await fetch(apiBase + '?action=signup', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ name, email, password }),
      });
      const data = await parseJsonSafe(res);
      if (!res.ok) throw new Error(data?.error || ('Signup failed (status ' + res.status + ')'));
      
      // Check if user is admin and redirect accordingly
      if (data.user.role === 'admin') {
        // Always redirect admin users to admin dashboard
        window.location.href = './admin/index.html';
      } else {
        window.location.href = './index.html';
      }
    } catch (err) {
      alert(err.message);
    }
  }

  async function handleSignoutClick() {
    try {
      await fetch(apiBase + '?action=signout', { method: 'POST', credentials: 'include' });
    } catch (e) {}
    window.location.href = './index.html';
  }

  document.addEventListener('DOMContentLoaded', () => {
    initAuthUI();
    const signinForm = byId('signinForm');
    const signupForm = byId('signupForm');
    const signoutBtn = byId('signoutBtn');
    if (signinForm) signinForm.addEventListener('submit', handleSigninSubmit);
    if (signupForm) signupForm.addEventListener('submit', handleSignupSubmit);
    if (signoutBtn) signoutBtn.addEventListener('click', handleSignoutClick);
  });
})();


