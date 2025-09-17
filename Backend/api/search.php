<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include session configuration first
include('../includes/session_config.php');

// Your existing includes
include('../includes/db.php');
include('../includes/functions.php');

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    json_response(['error' => 'Unauthorized access'], 401);
}

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    json_response(['error' => 'Forbidden: Admins only'], 403);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($method === 'GET' && $action === 'search') {
    if (empty($query)) {
        json_response(['error' => 'Search query is required'], 400);
    }
    
    $results = [];
    
    try {
        // Search Users
        if ($type === '' || $type === 'users') {
            $userSql = "SELECT u.id, u.name, u.email, u.created_at, r.role_name 
                       FROM users u 
                       JOIN roles r ON u.role_id = r.id 
                       WHERE u.name LIKE ? OR u.email LIKE ? OR r.role_name LIKE ?
                       ORDER BY u.created_at DESC";
            $stmt = $conn->prepare($userSql);
            $searchTerm = "%$query%";
            $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $userResult = $stmt->get_result();
            
            $users = [];
            while ($row = $userResult->fetch_assoc()) {
                $users[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'role' => $row['role_name'],
                    'created_at' => $row['created_at'],
                    'type' => 'user'
                ];
            }
            $results['users'] = $users;
        }
        
        // Search Doctors
        if ($type === '' || $type === 'doctors') {
            $doctorSql = "SELECT d.id, d.name, d.email, d.specialization, d.phone, d.description,
                                 dept.name as department_name
                          FROM doctors d 
                          LEFT JOIN departments dept ON d.department_id = dept.id
                          WHERE d.name LIKE ? OR d.email LIKE ? OR d.specialization LIKE ? 
                                OR d.phone LIKE ? OR d.description LIKE ? OR dept.name LIKE ?
                          ORDER BY d.name ASC";
            $stmt = $conn->prepare($doctorSql);
            $searchTerm = "%$query%";
            $stmt->bind_param("ssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $doctorResult = $stmt->get_result();
            
            $doctors = [];
            while ($row = $doctorResult->fetch_assoc()) {
                $doctors[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'specialization' => $row['specialization'],
                    'phone' => $row['phone'],
                    'description' => $row['description'],
                    'department' => $row['department_name'],
                    'type' => 'doctor'
                ];
            }
            $results['doctors'] = $doctors;
        }
        
        // Search Departments
        if ($type === '' || $type === 'departments') {
            $deptSql = "SELECT id, name, description, created_at 
                       FROM departments 
                       WHERE name LIKE ? OR description LIKE ?
                       ORDER BY name ASC";
            $stmt = $conn->prepare($deptSql);
            $searchTerm = "%$query%";
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
            $stmt->execute();
            $deptResult = $stmt->get_result();
            
            $departments = [];
            while ($row = $deptResult->fetch_assoc()) {
                $departments[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'created_at' => $row['created_at'],
                    'type' => 'department'
                ];
            }
            $results['departments'] = $departments;
        }
        
        // Search Appointments
        if ($type === '' || $type === 'appointments') {
            $appointmentSql = "SELECT a.id, a.patient_name, a.patient_email, a.patient_phone, 
                                     a.appointment_date, a.appointment_time, a.status, a.notes,
                                     d.name as doctor_name, a.created_at
                              FROM appointments a
                              LEFT JOIN doctors d ON a.doctor_id = d.id
                              WHERE a.patient_name LIKE ? OR a.patient_email LIKE ? OR a.patient_phone LIKE ?
                                    OR d.name LIKE ? OR a.status LIKE ? OR a.notes LIKE ?
                              ORDER BY a.appointment_date DESC, a.appointment_time DESC";
            $stmt = $conn->prepare($appointmentSql);
            $searchTerm = "%$query%";
            $stmt->bind_param("ssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $appointmentResult = $stmt->get_result();
            
            $appointments = [];
            while ($row = $appointmentResult->fetch_assoc()) {
                $appointments[] = [
                    'id' => $row['id'],
                    'patient_name' => $row['patient_name'],
                    'patient_email' => $row['patient_email'],
                    'patient_phone' => $row['patient_phone'],
                    'doctor_name' => $row['doctor_name'],
                    'appointment_date' => $row['appointment_date'],
                    'appointment_time' => $row['appointment_time'],
                    'status' => $row['status'],
                    'notes' => $row['notes'],
                    'created_at' => $row['created_at'],
                    'type' => 'appointment'
                ];
            }
            $results['appointments'] = $appointments;
        }
        
        // Search Events
        if ($type === '' || $type === 'events') {
            $eventSql = "SELECT id, title, description, location, event_date, event_time, created_at 
                        FROM events 
                        WHERE title LIKE ? OR description LIKE ? OR location LIKE ?
                        ORDER BY event_date DESC";
            $stmt = $conn->prepare($eventSql);
            $searchTerm = "%$query%";
            $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $eventResult = $stmt->get_result();
            
            $events = [];
            while ($row = $eventResult->fetch_assoc()) {
                $events[] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'location' => $row['location'],
                    'event_date' => $row['event_date'],
                    'event_time' => $row['event_time'],
                    'created_at' => $row['created_at'],
                    'type' => 'event'
                ];
            }
            $results['events'] = $events;
        }
        
        // Search Services
        if ($type === '' || $type === 'services') {
            $serviceSql = "SELECT id, name, description, price, created_at 
                          FROM services 
                          WHERE name LIKE ? OR description LIKE ? OR price LIKE ?
                          ORDER BY name ASC";
            $stmt = $conn->prepare($serviceSql);
            $searchTerm = "%$query%";
            $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $serviceResult = $stmt->get_result();
            
            $services = [];
            while ($row = $serviceResult->fetch_assoc()) {
                $services[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'price' => $row['price'],
                    'created_at' => $row['created_at'],
                    'type' => 'service'
                ];
            }
            $results['services'] = $services;
        }
        
        // Search Gallery
        if ($type === '' || $type === 'gallery') {
            $gallerySql = "SELECT id, title, description, category, image_path, created_at 
                          FROM gallery 
                          WHERE title LIKE ? OR description LIKE ? OR category LIKE ?
                          ORDER BY created_at DESC";
            $stmt = $conn->prepare($gallerySql);
            $searchTerm = "%$query%";
            $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $galleryResult = $stmt->get_result();
            
            $gallery = [];
            while ($row = $galleryResult->fetch_assoc()) {
                $gallery[] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'category' => $row['category'],
                    'image_path' => $row['image_path'],
                    'created_at' => $row['created_at'],
                    'type' => 'gallery'
                ];
            }
            $results['gallery'] = $gallery;
        }
        
        // Search Messages (Contact Messages)
        if ($type === '' || $type === 'messages') {
            $messageSql = "SELECT id, name, email, subject, message, status, created_at 
                          FROM contact_messages 
                          WHERE name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ? OR status LIKE ?
                          ORDER BY created_at DESC";
            $stmt = $conn->prepare($messageSql);
            $searchTerm = "%$query%";
            $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $messageResult = $stmt->get_result();
            
            $messages = [];
            while ($row = $messageResult->fetch_assoc()) {
                $messages[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'subject' => $row['subject'],
                    'message' => $row['message'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at'],
                    'type' => 'message'
                ];
            }
            $results['messages'] = $messages;
        }
        
        // Search Schedules
        if ($type === '' || $type === 'schedules') {
            $scheduleSql = "SELECT s.id, s.date, s.time_slots, s.is_available,
                                   d.name as doctor_name, s.created_at
                            FROM doctor_schedules s
                            LEFT JOIN doctors d ON s.doctor_id = d.id
                            WHERE d.name LIKE ? OR s.date LIKE ? OR s.time_slots LIKE ?
                            ORDER BY s.date DESC";
            $stmt = $conn->prepare($scheduleSql);
            $searchTerm = "%$query%";
            $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $scheduleResult = $stmt->get_result();
            
            $schedules = [];
            while ($row = $scheduleResult->fetch_assoc()) {
                $schedules[] = [
                    'id' => $row['id'],
                    'doctor_name' => $row['doctor_name'],
                    'date' => $row['date'],
                    'time_slots' => $row['time_slots'],
                    'is_available' => $row['is_available'],
                    'created_at' => $row['created_at'],
                    'type' => 'schedule'
                ];
            }
            $results['schedules'] = $schedules;
        }
        
        // Calculate total results
        $totalResults = 0;
        foreach ($results as $typeResults) {
            $totalResults += count($typeResults);
        }
        
        json_response([
            'success' => true,
            'query' => $query,
            'total_results' => $totalResults,
            'results' => $results
        ]);
        
    } catch (Exception $e) {
        json_response(['error' => 'Search failed: ' . $e->getMessage()], 500);
    }
}

// Get search suggestions/autocomplete
if ($method === 'GET' && $action === 'suggestions') {
    if (empty($query)) {
        json_response(['suggestions' => []]);
    }
    
    try {
        $suggestions = [];
        $searchTerm = "%$query%";
        
        // Get user name suggestions
        $userSql = "SELECT DISTINCT name FROM users WHERE name LIKE ? LIMIT 5";
        $stmt = $conn->prepare($userSql);
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = ['text' => $row['name'], 'type' => 'user'];
        }
        
        // Get doctor name suggestions
        $doctorSql = "SELECT DISTINCT name FROM doctors WHERE name LIKE ? LIMIT 5";
        $stmt = $conn->prepare($doctorSql);
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = ['text' => $row['name'], 'type' => 'doctor'];
        }
        
        // Get department name suggestions
        $deptSql = "SELECT DISTINCT name FROM departments WHERE name LIKE ? LIMIT 5";
        $stmt = $conn->prepare($deptSql);
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = ['text' => $row['name'], 'type' => 'department'];
        }
        
        // Get service name suggestions
        $serviceSql = "SELECT DISTINCT name FROM services WHERE name LIKE ? LIMIT 5";
        $stmt = $conn->prepare($serviceSql);
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = ['text' => $row['name'], 'type' => 'service'];
        }
        
        json_response([
            'success' => true,
            'suggestions' => array_slice($suggestions, 0, 10) // Limit to 10 suggestions
        ]);
        
    } catch (Exception $e) {
        json_response(['error' => 'Failed to get suggestions: ' . $e->getMessage()], 500);
    }
}

// If no valid action
json_response(['error' => 'Invalid action'], 400);
?>
