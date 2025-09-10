// Fetch latest news (events) data from API
let latestNews = [];

async function fetchLatestNewsData() {
  try {
    const response = await fetch('../../Backend/api/events.php?action=public');
    const data = await response.json();
    
    if (data.status === 'success') {
      // Transform the data to match the expected format for gallery
      latestNews = data.data.map(event => ({
        id: event.id,
        title: event.title,
        description: event.description || "Join us for this special event.",
        img: event.image_path ? `/doctor-appoinment/uploads/events/${event.image_path}` : "../public/assets/eyecare.jpg",
        date: event.event_date || "TBA"
      }));
      
      // Make latestNews available globally
      window.latestNews = latestNews;
      
      // Trigger a custom event to notify that latestNews data is loaded
      window.dispatchEvent(new CustomEvent('latestNewsDataLoaded', { detail: latestNews }));
    } else {
      console.error('Failed to fetch latest news:', data.message);
      // Fallback to empty array
      window.latestNews = [];
    }
  } catch (error) {
    console.error('Error fetching latest news:', error);
    // Fallback to empty array
    window.latestNews = [];
  }
}

// Fetch latest news data immediately
fetchLatestNewsData();