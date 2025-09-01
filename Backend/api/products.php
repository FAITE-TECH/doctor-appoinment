<?php
header('Content-Type: application/json');
include('../includes/db.php');
include('../includes/functions.php');

ensure_session_started();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'GET' && $action === 'doctors') {
    // Get all doctors
    $sql = "SELECT id, name, email, specialization, phone FROM doctors ORDER BY name";
    $result = $conn->query($sql);
    
    if (!$result) {
        json_response(['error' => 'Database error'], 500);
    }
    
    $doctors = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $doctors[] = $row;
        }
    }
    
    json_response($doctors);
}

if ($method === 'GET' && $action === 'appointments') {
    // Check if user is authenticated
    if (!isset($_SESSION['user_id'])) {
        json_response(['error' => 'Unauthorized access'], 401);
    }
    
    $userId = $_SESSION['user_id'];
    $sql = "SELECT a.*, d.name as doctor_name, d.specialization 
            FROM appointments a 
            JOIN doctors d ON a.doctor_id = d.id 
            WHERE a.user_id = ? 
            ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        json_response(['error' => 'Database error'], 500);
    }
    
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
    }
    
    $stmt->close();
    json_response($appointments);
}

if ($method === 'POST' && $action === 'book') {
    // Check if user is authenticated
    if (!isset($_SESSION['user_id'])) {
        json_response(['error' => 'Unauthorized access'], 401);
    }
    
    $body = get_json_body();
    require_fields($body, ['doctor_id', 'appointment_date', 'appointment_time']);
    
    $userId = $_SESSION['user_id'];
    $doctorId = intval($body['doctor_id']);
    $appointmentDate = $body['appointment_date'];
    $appointmentTime = $body['appointment_time'];
    $notes = isset($body['notes']) ? trim($body['notes']) : '';
    
    // Validate date and time
    if (!strtotime($appointmentDate) || !strtotime($appointmentTime)) {
        json_response(['error' => 'Invalid date or time format'], 422);
    }
    
    // Check if appointment slot is available
    $checkSql = "SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'";
    $checkStmt = $conn->prepare($checkSql);
    if (!$checkStmt) {
        json_response(['error' => 'Database error'], 500);
    }
    
    $checkStmt->bind_param('iss', $doctorId, $appointmentDate, $appointmentTime);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        json_response(['error' => 'Appointment slot not available'], 409);
    }
    $checkStmt->close();
    
    // Book the appointment
    $insertSql = "INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, notes) VALUES (?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) {
        json_response(['error' => 'Database error'], 500);
    }
    
    $insertStmt->bind_param('iisss', $userId, $doctorId, $appointmentDate, $appointmentTime, $notes);
    
    if (!$insertStmt->execute()) {
        $insertStmt->close();
        json_response(['error' => 'Failed to book appointment'], 500);
    }
    
    $appointmentId = $insertStmt->insert_id;
    $insertStmt->close();
    
    json_response(['message' => 'Appointment booked successfully', 'appointment_id' => $appointmentId], 201);
}

json_response(['error' => 'Not found'], 404);
?>
