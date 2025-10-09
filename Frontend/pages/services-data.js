// Fetch services data from API
let services = [];

async function fetchServicesData() {
  try {
  const response = await fetch(
    window.buildApiUrl
      ? window.buildApiUrl('services.php?action=public')
      : (window.APP_API_FOLDER ? window.APP_API_FOLDER + 'services.php?action=public' : 'https://spchospital.com/Backend/api/services.php?action=public')
  );
    const data = await response.json();
    
    if (data.status === 'success') {
      // Transform the data to match the expected format for gallery
      services = data.data.map(service => ({
        id: service.id,
        type: service.name,
        description: service.description || "Quality healthcare service provided by our experienced team.",
  img: service.image_path ? (window.buildUploadUrl ? window.buildUploadUrl(`services/${service.image_path}`) : (`https://spchospital.com/uploads/services/${service.image_path}`)) : "https://spchospital.com/Frontend/public/assets/eyecare.jpg",
        price: service.price ? parseFloat(service.price) : 0
      }));
      
      // Make services available globally
      window.services = services;
      
      // Trigger a custom event to notify that services data is loaded
      window.dispatchEvent(new CustomEvent('servicesDataLoaded', { detail: services }));
    } else {
      console.error('Failed to fetch services:', data.message);
      // Fallback to empty array
      window.services = [];
    }
  } catch (error) {
    console.error('Error fetching services:', error);
    // Fallback to empty array
    window.services = [];
  }
}

// Fetch services data immediately
fetchServicesData();