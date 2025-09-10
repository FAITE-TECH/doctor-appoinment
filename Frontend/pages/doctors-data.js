// Fetch doctors data from API
let doctors = [];

async function fetchDoctorsData() {
  try {
    const response = await fetch('../../Backend/api/doctors.php?action=doctors');
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
        schedule: {
          Sunday: "09:00 - 12:00",
          Monday: "10:00 - 14:00",
          Tuesday: "10:00 - 14:00",
          Wednesday: "10:00 - 14:00",
          Thursday: "11:00 - 15:00",
          Friday: "10:00 - 14:00",
          Saturday: "08:00 - 11:00",
        }
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
