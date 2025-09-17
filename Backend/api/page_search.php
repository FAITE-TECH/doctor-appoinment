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
$page = isset($_GET['page']) ? $_GET['page'] : '';

if ($method === 'GET' && $action === 'search') {
    if (empty($query)) {
        json_response(['error' => 'Search query is required'], 400);
    }
    
    if (empty($page)) {
        json_response(['error' => 'Page type is required'], 400);
    }
    
    try {
        $results = [];
        
        switch ($page) {
            case 'users':
                $sql = "SELECT u.id, u.name, u.email, u.created_at, r.role_name 
                       FROM users u 
                       JOIN roles r ON u.role_id = r.id 
                       WHERE u.name LIKE ? OR u.email LIKE ? OR r.role_name LIKE ?
                       ORDER BY u.created_at DESC";
                $stmt = $conn->prepare($sql);
                $searchTerm = "%$query%";
                $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $results[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'role' => $row['role_name'],
                        'created_at' => $row['created_at']
                    ];
                }
                break;
                
            case 'doctors':
                $sql = "SELECT d.id, d.name, d.email, d.specialization, d.phone, d.description,
                               dept.name as department_name
                        FROM doctors d 
                        LEFT JOIN departments dept ON d.department_id = dept.id
                        WHERE d.name LIKE ? OR d.email LIKE ? OR d.specialization LIKE ? 
                              OR d.phone LIKE ? OR d.description LIKE ? OR dept.name LIKE ?
                        ORDER BY d.name ASC";
                $stmt = $conn->prepare($sql);
                $searchTerm = "%$query%";
                $stmt->bind_param("ssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $results[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'specialization' => $row['specialization'],
                        'phone' => $row['phone'],
                        'description' => $row['description'],
                        'department' => $row['department_name']
                    ];
                }
                break;
                
            case 'departments':
                $sql = "SELECT id, name, description, created_at 
                       FROM departments 
                       WHERE name LIKE ? OR description LIKE ?
                       ORDER BY name ASC";
                $stmt = $conn->prepare($sql);
                $searchTerm = "%$query%";
                $stmt->bind_param("ss", $searchTerm, $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $results[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'description' => $row['description'],
                        'created_at' => $row['created_at']
                    ];
                }
                break;
                
            case 'appointments':
                $sql = "SELECT a.id, u.name as patient_name, u.email as patient_email, 
                               a.appointment_date, a.appointment_time, a.status, a.notes,
                               d.name as doctor_name, a.created_at
                        FROM appointments a
                        LEFT JOIN users u ON a.user_id = u.id
                        LEFT JOIN doctors d ON a.doctor_id = d.id
                        WHERE u.name LIKE ? OR u.email LIKE ? 
                              OR d.name LIKE ? OR a.status LIKE ? OR a.notes LIKE ?
                        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
                $stmt = $conn->prepare($sql);
                $searchTerm = "%$query%";
                $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $results[] = [
                        'id' => $row['id'],
                        'patient_name' => $row['patient_name'],
                        'patient_email' => $row['patient_email'],
                        'patient_phone' => '', // Not available in current schema
                        'doctor_name' => $row['doctor_name'],
                        'appointment_date' => $row['appointment_date'],
                        'appointment_time' => $row['appointment_time'],
                        'status' => $row['status'],
                        'notes' => $row['notes'],
                        'created_at' => $row['created_at']
                    ];
                }
                break;
                
            case 'events':
                $sql = "SELECT id, title, description, location, event_date, event_time, created_at 
                        FROM events 
                        WHERE title LIKE ? OR description LIKE ? OR location LIKE ?
                        ORDER BY event_date DESC";
                $stmt = $conn->prepare($sql);
                $searchTerm = "%$query%";
                $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $results[] = [
                        'id' => $row['id'],
                        'title' => $row['title'],
                        'description' => $row['description'],
                        'location' => $row['location'],
                        'event_date' => $row['event_date'],
                        'event_time' => $row['event_time'],
                        'created_at' => $row['created_at']
                    ];
                }
                break;
                
            case 'services':
                $sql = "SELECT id, name, description, price, created_at 
                        FROM services 
                        WHERE name LIKE ? OR description LIKE ? OR price LIKE ?
                        ORDER BY name ASC";
                $stmt = $conn->prepare($sql);
                $searchTerm = "%$query%";
                $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $results[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'description' => $row['description'],
                        'price' => $row['price'],
                        'created_at' => $row['created_at']
                    ];
                }
                break;
                
            case 'gallery':
                $sql = "SELECT id, title, description, category, image_path, created_at 
                        FROM gallery 
                        WHERE title LIKE ? OR description LIKE ? OR category LIKE ?
                        ORDER BY created_at DESC";
                $stmt = $conn->prepare($sql);
                $searchTerm = "%$query%";
                $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $results[] = [
                        'id' => $row['id'],
                        'title' => $row['title'],
                        'description' => $row['description'],
                        'category' => $row['category'],
                        'image_path' => $row['image_path'],
                        'created_at' => $row['created_at']
                    ];
                }
                break;
                
            case 'messages':
                $sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name, email, subject, message, status, created_at 
                        FROM contact_messages 
                        WHERE CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ? OR status LIKE ?
                        ORDER BY created_at DESC";
                $stmt = $conn->prepare($sql);
                $searchTerm = "%$query%";
                $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $results[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'subject' => $row['subject'],
                        'message' => $row['message'],
                        'status' => $row['status'],
                        'created_at' => $row['created_at']
                    ];
                }
                break;
                
            case 'schedules':
                $sql = "SELECT s.id, s.schedule_date as date, 
                               CONCAT(s.start_time, ' - ', s.end_time) as time_slots, 
                               s.is_available,
                               d.name as doctor_name, s.created_at
                        FROM doctor_schedules s
                        LEFT JOIN doctors d ON s.doctor_id = d.id
                        WHERE d.name LIKE ? OR s.schedule_date LIKE ? 
                              OR CONCAT(s.start_time, ' - ', s.end_time) LIKE ?
                        ORDER BY s.schedule_date DESC";
                $stmt = $conn->prepare($sql);
                $searchTerm = "%$query%";
                $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $results[] = [
                        'id' => $row['id'],
                        'doctor_name' => $row['doctor_name'],
                        'date' => $row['date'],
                        'time_slots' => $row['time_slots'],
                        'is_available' => $row['is_available'],
                        'created_at' => $row['created_at']
                    ];
                }
                break;
                
            default:
                json_response(['error' => 'Invalid page type'], 400);
        }
        
        json_response([
            'success' => true,
            'query' => $query,
            'page' => $page,
            'total_results' => count($results),
            'results' => $results
        ]);
        
    } catch (Exception $e) {
        json_response(['error' => 'Search failed: ' . $e->getMessage()], 500);
    }
}

// If no valid action
json_response(['error' => 'Invalid action'], 400);
?>
