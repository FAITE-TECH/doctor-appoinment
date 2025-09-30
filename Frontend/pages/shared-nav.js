// Shared navigation functionality - Cross-browser compatible
(function() {
  'use strict'; // Enable strict mode for better error catching
  
  var apiBase = '../../Backend/api/auth.php';

  // Mobile menu toggle - compatible with older browsers
  function initMobileMenu() {
    // Wait for DOM elements to be available
    var waitForElements = setInterval(function() {
      var mobileMenuToggle = document.getElementById("mobileMenuToggle");
      var mobileMenu = document.getElementById("mobileMenu");
      var mobileMenuOverlay = document.getElementById("mobileMenuOverlay");

      if (mobileMenuToggle && mobileMenu && mobileMenuOverlay) {
        clearInterval(waitForElements);

        // Make sure the menu starts in the correct state - compatible with older browsers
        if (mobileMenu.classList) {
          mobileMenu.classList.add("translate-x-full");
          mobileMenuOverlay.classList.add("hidden");
        } else {
          // Fallback for browsers without classList support
          mobileMenu.className += " translate-x-full";
          mobileMenuOverlay.className += " hidden";
        }

        function openMenu() {
          if (mobileMenu.classList) {
            mobileMenu.classList.remove("translate-x-full");
            mobileMenuOverlay.classList.remove("hidden");
            setTimeout(function() {
              mobileMenuOverlay.classList.add("opacity-100");
            }, 0);
          } else {
            // Fallback for browsers without classList support
            mobileMenu.className = mobileMenu.className.replace(/\btranslate-x-full\b/g, '');
            mobileMenuOverlay.className = mobileMenuOverlay.className.replace(/\bhidden\b/g, '');
            setTimeout(function() {
              mobileMenuOverlay.className += " opacity-100";
            }, 0);
          }
          
          var icon = mobileMenuToggle.querySelector("i");
          if (icon) {
            if (icon.classList) {
              icon.classList.remove("fa-bars");
              icon.classList.add("fa-times");
            } else {
              icon.className = icon.className.replace(/\bfa-bars\b/g, '') + " fa-times";
            }
          }
        }

        function closeMenu() {
          if (mobileMenu.classList) {
            mobileMenu.classList.add("translate-x-full");
            mobileMenuOverlay.classList.remove("opacity-100");
            setTimeout(function() {
              mobileMenuOverlay.classList.add("hidden");
            }, 300);
          } else {
            // Fallback for browsers without classList support
            mobileMenu.className += " translate-x-full";
            mobileMenuOverlay.className = mobileMenuOverlay.className.replace(/\bopacity-100\b/g, '');
            setTimeout(function() {
              mobileMenuOverlay.className += " hidden";
            }, 300);
          }
          
          var icon = mobileMenuToggle.querySelector("i");
          if (icon) {
            if (icon.classList) {
              icon.classList.add("fa-bars");
              icon.classList.remove("fa-times");
            } else {
              icon.className = icon.className.replace(/\bfa-times\b/g, '') + " fa-bars";
            }
          }
        }

        // Remove any existing event listeners - cross-browser compatible
        var newMenuToggle = mobileMenuToggle.cloneNode(true);
        mobileMenuToggle.parentNode.replaceChild(newMenuToggle, mobileMenuToggle);
        
        // Add new event listeners - compatible with older browsers
        if (newMenuToggle.addEventListener) {
          newMenuToggle.addEventListener("click", function(e) {
            e.stopPropagation();
            var isHidden = mobileMenu.classList ? 
              mobileMenu.classList.contains("translate-x-full") :
              mobileMenu.className.indexOf("translate-x-full") !== -1;
            if (isHidden) {
              openMenu();
            } else {
              closeMenu();
            }
          });
        } else if (newMenuToggle.attachEvent) {
          // Fallback for IE8 and below
          newMenuToggle.attachEvent("onclick", function(e) {
            e = e || window.event;
            if (e.stopPropagation) e.stopPropagation();
            else e.cancelBubble = true;
            
            var isHidden = mobileMenu.className.indexOf("translate-x-full") !== -1;
            if (isHidden) {
              openMenu();
            } else {
              closeMenu();
            }
          });
        }

        // Cross-browser event listener for overlay
        if (mobileMenuOverlay.addEventListener) {
          mobileMenuOverlay.addEventListener("click", closeMenu);
        } else if (mobileMenuOverlay.attachEvent) {
          mobileMenuOverlay.attachEvent("onclick", closeMenu);
        }

        // Close menu when clicking on menu links - cross-browser compatible
        var menuLinks = mobileMenu.querySelectorAll ? mobileMenu.querySelectorAll("a") : mobileMenu.getElementsByTagName("a");
        for (var i = 0; i < menuLinks.length; i++) {
          if (menuLinks[i].addEventListener) {
            menuLinks[i].addEventListener("click", closeMenu);
          } else if (menuLinks[i].attachEvent) {
            menuLinks[i].attachEvent("onclick", closeMenu);
          }
        }

        // Cross-browser keydown event
        if (document.addEventListener) {
          document.addEventListener("keydown", function(e) {
            var isOpen = mobileMenu.classList ? 
              !mobileMenu.classList.contains("translate-x-full") :
              mobileMenu.className.indexOf("translate-x-full") === -1;
            if ((e.key === "Escape" || e.keyCode === 27) && isOpen) {
              closeMenu();
            }
          });
        } else if (document.attachEvent) {
          document.attachEvent("onkeydown", function(e) {
            e = e || window.event;
            var isOpen = mobileMenu.className.indexOf("translate-x-full") === -1;
            if (e.keyCode === 27 && isOpen) {
              closeMenu();
            }
          });
        }
      }
    }, 100); // Check every 100ms

    // Cleanup after 5 seconds to prevent infinite checking
    setTimeout(function() { clearInterval(waitForElements); }, 5000);
  }

  // Set active navigation link - cross-browser compatible
  function setActiveNavLink() {
    var currentPage = window.location.pathname.split('/').pop().replace('.html', '');
    var navLinks = document.querySelectorAll ? 
      document.querySelectorAll('[data-nav]') : 
      document.getElementsByTagName('a'); // Fallback for older browsers
    
    // Cross-browser forEach implementation
    var forEach = function(array, callback) {
      if (array.forEach) {
        array.forEach(callback);
      } else {
        for (var i = 0; i < array.length; i++) {
          callback(array[i], i, array);
        }
      }
    };
    
    forEach(navLinks, function(link) {
      var linkPage = link.getAttribute('href').replace('./', '').replace('.html', '');
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
