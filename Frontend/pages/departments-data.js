// Fetch departments data from API
let departments = [];

async function fetchDepartmentsData() {
  try {
    const response = await fetch('../../Backend/api/departments.php?action=public');
    const data = await response.json();
    
    if (data.status === 'success') {
      // Transform the data to match the expected format for gallery
      departments = data.data.map(department => ({
        id: department.id,
        name: department.name,
        description: department.description || "Specialized medical department providing quality healthcare services.",
        img: department.image_path ? `/doctor-appoinment/uploads/departments/${department.image_path}` : "../public/assets/nephrology.jpg",
        email: "contact@hospital.com",
        phone: "123-456-7890"
      }));
      
      // Make departments available globally
      window.departments = departments;
      
      // Trigger a custom event to notify that departments data is loaded
      window.dispatchEvent(new CustomEvent('departmentsDataLoaded', { detail: departments }));
    } else {
      console.error('Failed to fetch departments:', data.message);
      // Fallback to empty array
      window.departments = [];
    }
  } catch (error) {
    console.error('Error fetching departments:', error);
    // Fallback to empty array
    window.departments = [];
  }
}

// Fetch departments data immediately
fetchDepartmentsData();