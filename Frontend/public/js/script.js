// Basic frontend auth and navigation wiring
(function () {
  const apiBase = '/doctor-appoinment/Backend/api/auth.php';

  function byId(id) {
    return document.getElementById(id);
  }

  function setAuthUI(user) {
    const guestLinks = document.querySelectorAll('[data-auth="guest"]');
    const userItems = document.querySelectorAll('[data-auth="user"]');
    const emailSpan = byId('nav-user-email');
    if (user && user.email) {
      guestLinks.forEach(el => el.classList.add('hidden'));
      userItems.forEach(el => el.classList.remove('hidden'));
      if (emailSpan) emailSpan.textContent = user.email;
    } else {
      guestLinks.forEach(el => el.classList.remove('hidden'));
      userItems.forEach(el => el.classList.add('hidden'));
      if (emailSpan) emailSpan.textContent = '';
    }
  }

  async function fetchMe() {
    try {
      const res = await fetch(apiBase + '?action=me', { credentials: 'include' });
      const data = await res.json();
      return data && data.authenticated ? data.user : null;
    } catch (e) {
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
      const data = await res.json();
      if (!res.ok) throw new Error(data?.error || 'Signin failed');
      window.location.href = './index.html';
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
      const data = await res.json();
      if (!res.ok) throw new Error(data?.error || 'Signup failed');
      window.location.href = './index.html';
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


