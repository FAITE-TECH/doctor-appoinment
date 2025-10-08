// Fetch doctors data from API
let doctors = [];

async function fetchDoctorsData(searchTerm = '') {
  try {
  let url = (window.buildApiUrl ? window.buildApiUrl('doctors.php?action=doctors') : '../../Backend/api/doctors.php?action=doctors');
    if (searchTerm) {
      url += '&search=' + encodeURIComponent(searchTerm);
    }
    
    const response = await fetch(url);
    const data = await response.json();
    
    if (data.status === 'success') {
      // Transform the data to match the expected format for gallery
      doctors = data.data.map(doctor => ({
        id: doctor.id,
        name: doctor.name,
        special: doctor.department_name || doctor.specialization || 'General',
        img: doctor.image_path || "../public/assets/doctor1.jpg",
        description: doctor.description || "Experienced medical professional dedicated to providing quality healthcare services.",
        email: doctor.email || "contact@hospital.com",
        phone: doctor.phone || "123-456-7890",
        schedule: null // Will be loaded dynamically
      }));
      
      // Make doctors available globally
      window.doctors = doctors;
      
      // Trigger a custom event to notify that doctors data is loaded
      window.dispatchEvent(new CustomEvent('doctorsDataLoaded', { detail: doctors }));
    } else {
      console.error('Failed to fetch doctors:', data.message);
      // Fallback to empty array
      window.doctors = [];
    }
  } catch (error) {
    console.error('Error fetching doctors:', error);
    // Fallback to empty array
    window.doctors = [];
  }
}

// Fetch doctors data immediately
fetchDoctorsData();
