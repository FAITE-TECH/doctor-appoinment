// Shared navigation functionality
(function() {
  const apiBase = '../../Backend/api/auth.php';

  // Mobile menu toggle
  function initMobileMenu() {
    // Wait for DOM elements to be available
    const waitForElements = setInterval(() => {
      const mobileMenuToggle = document.getElementById("mobileMenuToggle");
      const mobileMenu = document.getElementById("mobileMenu");
      const mobileMenuOverlay = document.getElementById("mobileMenuOverlay");

      if (mobileMenuToggle && mobileMenu && mobileMenuOverlay) {
        clearInterval(waitForElements);

        // Make sure the menu starts in the correct state
        mobileMenu.classList.add("translate-x-full");
        mobileMenuOverlay.classList.add("hidden");

        function openMenu() {
          mobileMenu.classList.remove("translate-x-full");
          mobileMenuOverlay.classList.remove("hidden");
          setTimeout(() => {
            mobileMenuOverlay.classList.add("opacity-100");
          }, 0);
          const icon = mobileMenuToggle.querySelector("i");
          if (icon) {
            icon.classList.remove("fa-bars");
            icon.classList.add("fa-times");
          }
        }

        function closeMenu() {
          mobileMenu.classList.add("translate-x-full");
          mobileMenuOverlay.classList.remove("opacity-100");
          setTimeout(() => {
            mobileMenuOverlay.classList.add("hidden");
          }, 300);
          const icon = mobileMenuToggle.querySelector("i");
          if (icon) {
            icon.classList.add("fa-bars");
            icon.classList.remove("fa-times");
          }
        }

        // Remove any existing event listeners
        const newMenuToggle = mobileMenuToggle.cloneNode(true);
        mobileMenuToggle.parentNode.replaceChild(newMenuToggle, mobileMenuToggle);
        
        // Add new event listeners
        newMenuToggle.addEventListener("click", (e) => {
          e.stopPropagation();
          if (mobileMenu.classList.contains("translate-x-full")) {
            openMenu();
          } else {
            closeMenu();
          }
        });

        mobileMenuOverlay.addEventListener("click", closeMenu);

        mobileMenu.querySelectorAll("a").forEach(link => {
          link.addEventListener("click", closeMenu);
        });

        document.addEventListener("keydown", (e) => {
          if (e.key === "Escape" && !mobileMenu.classList.contains("translate-x-full")) {
            closeMenu();
          }
        });
      }
    }, 100); // Check every 100ms

    // Cleanup after 5 seconds to prevent infinite checking
    setTimeout(() => clearInterval(waitForElements), 5000);
  }

  // Set active navigation link
  function setActiveNavLink() {
    const currentPage = window.location.pathname.split('/').pop().replace('.html', '');
    const navLinks = document.querySelectorAll('[data-nav]');
    
    navLinks.forEach(link => {
      const linkPage = link.getAttribute('href').replace('./', '').replace('.html', '');
      // Check if current page matches exactly or if it's a detail page (e.g., doctor-details should highlight doctors)
      if (linkPage === currentPage || 
          (currentPage === 'doctor-details' && linkPage === 'doctors') ||
          (currentPage === 'department-details' && linkPage === 'departments') ||
          (currentPage === 'service-details' && linkPage === 'services') ||
          (currentPage === 'event-details' && linkPage === 'events') ||
          (currentPage === 'gallery-details' && linkPage === 'gallery')) {
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
