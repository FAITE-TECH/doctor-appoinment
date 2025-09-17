// Shared navigation functionality
(function() {
  const apiBase = '../../Backend/api/auth.php';

  // Mobile menu toggle
  function initMobileMenu() {
    const mobileMenuToggle = document.getElementById("mobileMenuToggle");
    const mobileMenu = document.getElementById("mobileMenu");

    if (mobileMenuToggle && mobileMenu) {
      mobileMenuToggle.addEventListener("click", () => {
        mobileMenu.classList.toggle("hidden");
      });
    }
  }

  // Set active navigation link
  function setActiveNavLink() {
    const currentPage = window.location.pathname.split('/').pop().replace('.html', '');
    const navLinks = document.querySelectorAll('[data-nav]');
    
    navLinks.forEach(link => {
      const linkPage = link.getAttribute('href').replace('./', '').replace('.html', '');
      if (linkPage === currentPage) {
        link.classList.add('text-blue-600', 'font-semibold');
        link.classList.remove('hover:text-blue-600');
      } else {
        link.classList.remove('text-blue-600', 'font-semibold');
        link.classList.add('hover:text-blue-600');
      }
    });
  }

  // Handle sign out functionality
  function initSignOut() {
    const signoutBtn = document.getElementById('signoutBtn');
    if (signoutBtn) {
      // Remove any existing event listeners to avoid duplicates
      signoutBtn.replaceWith(signoutBtn.cloneNode(true));
      const newSignoutBtn = document.getElementById('signoutBtn');
      
      newSignoutBtn.addEventListener('click', async function() {
        try {
          await fetch(apiBase + '?action=signout', { method: 'POST', credentials: 'include' });
        } catch (e) {
          console.error('Signout error:', e);
        }
        window.location.href = './index.html';
      });
    }
  }

  // Initialize navigation when DOM is loaded
  document.addEventListener('DOMContentLoaded', () => {
    // Wait a bit for dynamic content to load
    setTimeout(() => {
      initMobileMenu();
      setActiveNavLink();
      initSignOut();
    }, 100);
  });

  // Also initialize when navigation is dynamically loaded
  function initNavigationAfterLoad() {
    setTimeout(() => {
      initMobileMenu();
      setActiveNavLink();
      initSignOut();
    }, 100);
  }

  // Make the function globally available
  window.initNavigationAfterLoad = initNavigationAfterLoad;
})();
