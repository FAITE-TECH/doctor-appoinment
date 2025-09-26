// Ensure the page has a proper structure for the footer
function ensurePageStructure() {
    // Make sure body has min-height and flex setup for sticky footer
    document.body.style.minHeight = '100vh';
    document.body.style.display = 'flex';
    document.body.style.flexDirection = 'column';
    
    // Create a main content wrapper if it doesn't exist
    if (!document.querySelector('main')) {
        const mainContent = document.createElement('main');
        mainContent.style.flex = '1 0 auto';
        
        // Move all body content (except scripts) into main
        const bodyChildren = Array.from(document.body.children);
        bodyChildren.forEach(child => {
            if (child.tagName !== 'SCRIPT' && child.tagName !== 'FOOTER') {
                mainContent.appendChild(child);
            }
        });
        
        document.body.insertBefore(mainContent, document.body.firstChild);
    }
}

// Function to get the footer path
function getFooterPath() {
    const cacheBuster = Date.now();
    const scripts = document.getElementsByTagName('script');
    for (const script of scripts) {
        if (script.src.includes('footer.js')) {
            return script.src.replace('footer.js', `footer.html?v=${cacheBuster}`);
        }
    }
    return `./footer.html?v=${cacheBuster}`;
}

// Function to load and inject the shared footer
async function loadFooter() {
    try {
        // Ensure proper page structure first
        ensurePageStructure();
        
        // Remove any existing footer
        const existingFooter = document.querySelector('footer');
        if (existingFooter) {
            existingFooter.remove();
        }
        
        const footerPath = getFooterPath();
        const response = await fetch(footerPath, {
            headers: {
                'Accept': 'text/html',
                'Cache-Control': 'no-cache, no-store, must-revalidate'
            },
            cache: 'no-store'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const footerContent = await response.text();
        
        // Create a temporary container and insert the footer HTML
        const temp = document.createElement('div');
        temp.innerHTML = footerContent.trim();
        
        // Get the actual footer element
        const footer = temp.firstElementChild;
        
        // Add the footer to the page
        document.body.appendChild(footer);
        
        // Initialize any footer-specific functionality
        initializeFooter();
        
    } catch (error) {
        console.error('Error loading footer:', error);
        showFooterError();
    }
}

// Initialize any footer-specific functionality
function initializeFooter() {
    // Subscribe button functionality
    const subscribeButton = document.querySelector('footer button');
    const emailInput = document.querySelector('footer input[type="email"]');
    
    if (subscribeButton && emailInput) {
        subscribeButton.addEventListener('click', () => {
            const email = emailInput.value.trim();
            if (email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                // TODO: Implement newsletter subscription
                alert('Thank you for subscribing!');
                emailInput.value = '';
            } else {
                alert('Please enter a valid email address.');
            }
        });
    }
}

// Show error message if footer fails to load
function showFooterError() {
    const errorDiv = document.createElement('footer');
    errorDiv.className = 'bg-red-100 text-red-800 p-4 text-center';
    errorDiv.innerHTML = `
        <p class="text-sm">Failed to load the footer. Please refresh the page.</p>
    `;
    document.body.appendChild(errorDiv);
}

// Load footer when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadFooter);
} else {
    loadFooter();
}