<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$action = isset($_GET['action']) ? $_GET['action'] : '';

// ===================================================
// PUBLIC ENDPOINTS (No authentication required)
// ===================================================

// Book appointment (public endpoint)
if ($method === 'POST' && $action === 'book') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required_fields = ['patient_name', 'patient_email', 'patient_phone', 'doctor_id', 'appointment_date', 'appointment_time'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            json_response(['error' => "Field '$field' is required"], 400);
        }
    }
    
    $patient_name = trim($input['patient_name']);
    $patient_email = trim($input['patient_email']);
    $patient_phone = trim($input['patient_phone']);
    $doctor_id = intval($input['doctor_id']);
    $appointment_date = $input['appointment_date'];
    $appointment_time = $input['appointment_time'];
    $notes = trim($input['notes'] ?? '');
    
    // Validate email format
    if (!filter_var($patient_email, FILTER_VALIDATE_EMAIL)) {
        json_response(['error' => 'Invalid email format'], 400);
    }
    
    // Validate date format and ensure it's not in the past
    $appointment_datetime = DateTime::createFromFormat('Y-m-d H:i', $appointment_date . ' ' . $appointment_time);
    if (!$appointment_datetime) {
        json_response(['error' => 'Invalid date or time format'], 400);
    }
    
    if ($appointment_datetime < new DateTime()) {
        json_response(['error' => 'Cannot book appointments in the past'], 400);
    }
    
    // Check if doctor exists
    $stmt = $conn->prepare('SELECT id, name FROM doctors WHERE id = ?');
    $stmt->bind_param('i', $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();
    $stmt->close();
    
    if (!$doctor) {
        json_response(['error' => 'Doctor not found'], 404);
    }
    
    // Create or find patient user first
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->bind_param('s', $patient_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        // Create new patient user
        $stmt = $conn->prepare('INSERT INTO users (name, email, phone, password, role_id) VALUES (?, ?, ?, ?, 3)'); // role_id 3 = patient
        $hashed_password = password_hash(uniqid(), PASSWORD_DEFAULT); // Random password for patients
        $stmt->bind_param('ssss', $patient_name, $patient_email, $patient_phone, $hashed_password);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();
    } else {
        // Update the phone number if user exists
        $stmt = $conn->prepare('UPDATE users SET phone = ? WHERE id = ?');
        $stmt->bind_param('si', $patient_phone, $user['id']);
        $stmt->execute();
        $stmt->close();
        $user_id = $user['id'];
    }
    
    // Check if this patient has already booked this time slot
    $stmt = $conn->prepare('SELECT id FROM appointments WHERE user_id = ? AND doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != "cancelled"');
    $stmt->bind_param('iiss', $user_id, $doctor_id, $appointment_date, $appointment_time);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        json_response(['error' => 'You have already booked an appointment for this time slot'], 409);
    }
    $stmt->close();
    
    // Create appointment
    $stmt = $conn->prepare('INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status, notes) VALUES (?, ?, ?, ?, "pending", ?)');
    $stmt->bind_param('iisss', $user_id, $doctor_id, $appointment_date, $appointment_time, $notes);
    
    if ($stmt->execute()) {
        $appointment_id = $stmt->insert_id;
        $stmt->close();
        
        json_response([
            'message' => 'Appointment booked successfully',
            'appointment_id' => $appointment_id,
            'doctor_name' => $doctor['name'],
            'appointment_date' => $appointment_date,
            'appointment_time' => $appointment_time
        ], 201);
    } else {
        $stmt->close();
        json_response(['error' => 'Failed to book appointment'], 500);
    }
}

// Get available time slots for a doctor on a specific date
if ($method === 'GET' && $action === 'time_slots') {
    $doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;
    $date = isset($_GET['date']) ? $_GET['date'] : null;
    
    if (!$doctor_id || !$date) {
        json_response(['error' => 'Doctor ID and date are required'], 400);
    }
    
    // Validate date format
    $appointment_date = DateTime::createFromFormat('Y-m-d', $date);
    if (!$appointment_date) {
        json_response(['error' => 'Invalid date format'], 400);
    }
    
    // Generate available time slots (9 AM to 5 PM, 30-minute intervals)
    // Since multiple users can now book the same time slot, all slots are available
    $available_slots = [];
    $start_time = new DateTime($date . ' 09:00');
    $end_time = new DateTime($date . ' 17:00');
    $interval = new DateInterval('PT30M');
    
    $current_time = clone $start_time;
    while ($current_time < $end_time) {
        $time_str = $current_time->format('H:i');
        $available_slots[] = [
            'time' => $time_str,
            'display' => $current_time->format('g:i A')
        ];
        $current_time->add($interval);
    }
    
    json_response(['available_slots' => $available_slots]);
}

// ===================================================
// ADMIN ENDPOINTS (Authentication required)
// ===================================================

// Check if user is admin for other operations
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    json_response(['error' => 'Unauthorized access'], 403);
}

switch ($method) {
    case 'GET':
        if ($action === 'list') {
            // Get all appointments with patient and doctor details
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 20;
            $offset = ($page - 1) * $limit;
            
            $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
            $doctor_filter = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
            $date_filter = isset($_GET['date']) ? $_GET['date'] : '';
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            
            $where_conditions = [];
            $params = [];
            $param_types = '';
            
            if ($status_filter) {
                $where_conditions[] = "a.status = ?";
                $params[] = $status_filter;
                $param_types .= 's';
            }
            
            if ($doctor_filter) {
                $where_conditions[] = "a.doctor_id = ?";
                $params[] = $doctor_filter;
                $param_types .= 'i';
            }
            
            if ($date_filter) {
                $where_conditions[] = "a.appointment_date = ?";
                $params[] = $date_filter;
                $param_types .= 's';
            }
            
            if ($search) {
                $searchTerm = '%' . $search . '%';
                $where_conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR d.name LIKE ? OR d.specialization LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $param_types .= 'ssss';
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            $sql = "SELECT a.*, u.name as patient_name, u.email as patient_email, u.phone as phone_number, d.name as doctor_name, d.specialization, dept.name as department_name
                    FROM appointments a
                    JOIN users u ON a.user_id = u.id
                    JOIN doctors d ON a.doctor_id = d.id
                    LEFT JOIN departments dept ON d.department_id = dept.id
                    $where_clause
                    ORDER BY a.appointment_date DESC, a.appointment_time DESC
                    LIMIT ? OFFSET ?";
            
            // Add limit and offset parameters
            $params[] = $limit;
            $params[] = $offset;
            $param_types .= 'ii';
            
            $stmt = $conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($param_types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $appointments = [];
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
            $stmt->close();
            
            // Get total count for pagination
            $count_sql = "SELECT COUNT(*) as total FROM appointments a
                         JOIN users u ON a.user_id = u.id
                         JOIN doctors d ON a.doctor_id = d.id
                         LEFT JOIN departments dept ON d.department_id = dept.id
                         $where_clause";
            $count_stmt = $conn->prepare($count_sql);
            if (!empty($where_conditions)) {
                // Create separate parameters for count query (without limit/offset)
                $count_params = [];
                $count_param_types = '';
                
                if ($status_filter) {
                    $count_params[] = $status_filter;
                    $count_param_types .= 's';
                }
                if ($doctor_filter) {
                    $count_params[] = $doctor_filter;
                    $count_param_types .= 'i';
                }
                if ($date_filter) {
                    $count_params[] = $date_filter;
                    $count_param_types .= 's';
                }
                if ($search) {
                    $searchTerm = '%' . $search . '%';
                    $count_params[] = $searchTerm;
                    $count_params[] = $searchTerm;
                    $count_params[] = $searchTerm;
                    $count_params[] = $searchTerm;
                    $count_param_types .= 'ssss';
                }
                
                $count_stmt->bind_param($count_param_types, ...$count_params);
            }
            $count_stmt->execute();
            $total = $count_stmt->get_result()->fetch_assoc()['total'];
            $count_stmt->close();
            
            json_response([
                'appointments' => $appointments,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        } elseif ($id) {
            // Get specific appointment
            $stmt = $conn->prepare('SELECT a.*, u.name as patient_name, u.email as patient_email, u.phone as phone_number, d.name as doctor_name, d.specialization, dept.name as department_name
                                   FROM appointments a
                                   JOIN users u ON a.user_id = u.id
                                   JOIN doctors d ON a.doctor_id = d.id
                                   LEFT JOIN departments dept ON d.department_id = dept.id
                                   WHERE a.id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $appointment = $result->fetch_assoc();
            $stmt->close();
            
            if ($appointment) {
                json_response(['appointment' => $appointment]);
            } else {
                json_response(['error' => 'Appointment not found'], 404);
            }
        } else {
            json_response(['error' => 'Invalid request'], 400);
        }
        break;
        
    case 'PUT':
        if (!$id) {
            json_response(['error' => 'Appointment ID is required'], 400);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Update appointment status
        if (isset($input['status'])) {
            $status = $input['status'];
            $valid_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
            
            if (!in_array($status, $valid_statuses)) {
                json_response(['error' => 'Invalid status'], 400);
            }
            
            $stmt = $conn->prepare('UPDATE appointments SET status = ? WHERE id = ?');
            $stmt->bind_param('si', $status, $id);
            
            if ($stmt->execute()) {
                $stmt->close();
                json_response(['message' => 'Appointment status updated successfully']);
            } else {
                $stmt->close();
                json_response(['error' => 'Failed to update appointment status'], 500);
            }
        } else {
            json_response(['error' => 'No valid fields to update'], 400);
        }
        break;
        
    case 'DELETE':
        if (!$id) {
            json_response(['error' => 'Appointment ID is required'], 400);
        }
        
        $stmt = $conn->prepare('DELETE FROM appointments WHERE id = ?');
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            json_response(['message' => 'Appointment deleted successfully']);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to delete appointment'], 500);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}
?>
