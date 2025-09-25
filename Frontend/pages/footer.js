// Function to get the base URL for the footer
function getFooterPath() {
    // Get all script tags
    const scripts = document.getElementsByTagName('script');
    // Find the footer script tag
    for (const script of scripts) {
        if (script.src.includes('footer.js')) {
            // Return the path to the footer HTML file
            return script.src.replace('footer.js', 'footer.html');
        }
    }
    // Fallback to default path if script tag not found
    return '/doctor-appoinment/Frontend/pages/footer.html';
}

// Function to load and inject the shared footer
async function loadFooter() {
    try {
        const footerPath = getFooterPath();
        const response = await fetch(footerPath);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const footerContent = await response.text();
        
        // Create a container for the footer
        const footerContainer = document.createElement('div');
        footerContainer.innerHTML = footerContent;
        
        // Append the footer to the end of the body
        document.body.appendChild(footerContainer.firstChild);
    } catch (error) {
        console.error('Error loading footer:', error);
        // Add a visible error message on the page
        const errorDiv = document.createElement('div');
        errorDiv.style.cssText = 'background-color: #fee2e2; color: #991b1b; padding: 1rem; margin: 1rem; border-radius: 0.375rem;';
        errorDiv.textContent = 'Failed to load the footer. Please refresh the page or contact support if the problem persists.';
        document.body.appendChild(errorDiv);
    }
}

// Execute immediately if DOM is already loaded, otherwise wait for it
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadFooter);
} else {
    loadFooter();
}