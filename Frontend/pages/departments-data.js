// Fetch departments data from API
let departments = [];

async function fetchDepartmentsData() {
  try {
  const response = await fetch(
    window.buildApiUrl
      ? window.buildApiUrl('departments.php?action=public')
      : (window.APP_API_FOLDER ? window.APP_API_FOLDER + 'departments.php?action=public' : 'https://spchospital.com/Backend/api/departments.php?action=public')
  );
    const data = await response.json();
    
    if (data.status === 'success') {
      // Transform the data to match the expected format for gallery
      // Helper to normalize image path like in departments.html
      function resolveImagePath(path) {
        const fallback = "https://spchospital.com/Frontend/public/assets/nephrology.jpg";
        if (!path) return fallback;
        const p = String(path || '').trim();
        if (!p) return fallback;
        if (/^https?:\/\//i.test(p)) return p;
        if (p.startsWith('/')) return window.location.origin.replace(/\/+$|$/, '') + p;
        if (p.indexOf('uploads/') !== -1) return window.location.origin.replace(/\/+$|$/, '') + '/' + p.replace(/^\/+/, '');
        const filename = p.split('/').pop();
        if (typeof window.buildUploadUrl === 'function') {
          try { return window.buildUploadUrl('departments/' + filename); } catch (e) {}
        }
        return 'https://spchospital.com/uploads/departments/' + filename;
      }

      departments = data.data.map(department => ({
        id: department.id,
        name: department.name,
        description: department.description || "Specialized medical department providing quality healthcare services.",
        img: resolveImagePath(department.image_path),
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