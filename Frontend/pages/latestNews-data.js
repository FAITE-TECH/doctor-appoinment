// Fetch latest news (events) data from API - Cross-browser compatible
var latestNews = [];

// Cross-browser fetch implementation
function fetchLatestNewsData() {
  // Get the base path dynamically to work across different server setups
  var basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
  var apiUrl = basePath.replace('/pages', '') + '/../Backend/api/events.php?action=public';
  
  // Use our polyfilled fetch or native fetch
  window.fetch(apiUrl)
    .then(function(response) {
      return response.json();
    })
    .then(function(data) {
      if (data.status === 'success') {
        // Transform the data to match the expected format for gallery
        latestNews = [];
        for (var i = 0; i < data.data.length; i++) {
          var event = data.data[i];
          
          // Create cross-platform image path
          var imagePath = "../public/assets/eyecare.jpg"; // Default fallback
          if (event.image_path) {
            // Use relative path that works across different server setups
            imagePath = "../../uploads/events/" + event.image_path;
          }
          
          latestNews.push({
            id: event.id,
            title: event.title,
            description: event.description || "Join us for this special event.",
            img: imagePath,
            date: event.event_date || "TBA"
          });
        }
        
        // Make latestNews available globally
        window.latestNews = latestNews;
        
        // Cross-browser custom event dispatch
        if (window.CustomEvent && window.dispatchEvent) {
          try {
            window.dispatchEvent(new CustomEvent('latestNewsDataLoaded', { detail: latestNews }));
          } catch (e) {
            // Fallback for older browsers that don't support CustomEvent constructor
            var event = document.createEvent('CustomEvent');
            event.initCustomEvent('latestNewsDataLoaded', false, false, latestNews);
            window.dispatchEvent(event);
          }
        } else {
          // Fallback for very old browsers - just set a flag
          window.latestNewsDataLoaded = true;
        }
      } else {
        console.error('Failed to fetch latest news:', data.message);
        window.latestNews = [];
      }
    })
    .catch(function(error) {
      console.error('Error fetching latest news:', error);
      window.latestNews = [];
    });
}

// Fetch latest news data immediately
fetchLatestNewsData();