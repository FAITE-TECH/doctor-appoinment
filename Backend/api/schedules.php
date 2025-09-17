<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_schedules':
            getDoctorSchedules();
            break;
        case 'add_schedule':
            addDoctorSchedule();
            break;
        case 'update_schedule':
            updateDoctorSchedule();
            break;
        case 'delete_schedule':
            deleteDoctorSchedule();
            break;
        case 'get_available_slots':
            getAvailableSlots();
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

function getDoctorSchedules() {
    global $conn;
    
    $doctor_id = $_GET['doctor_id'] ?? null;
    $month = $_GET['month'] ?? null;
    $date = $_GET['date'] ?? null;
    
    if (!$doctor_id) {
        throw new Exception('Doctor ID is required');
    }
    
    $sql = "SELECT ds.*, d.name as doctor_name, d.specialization 
            FROM doctor_schedules ds 
            JOIN doctors d ON ds.doctor_id = d.id 
            WHERE ds.doctor_id = ?";
    $params = [$doctor_id];
    
    if ($month) {
        // Get start and end of month
        $start_date = $month . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));
        $sql .= " AND ds.schedule_date BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
    } elseif ($date) {
        $sql .= " AND ds.schedule_date = ?";
        $params[] = $date;
    }
    
    $sql .= " ORDER BY ds.schedule_date, ds.start_time";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedules = $result->fetch_all(MYSQLI_ASSOC);
    
    // Group schedules by date for easier frontend handling
    $grouped_schedules = [];
    foreach ($schedules as $schedule) {
        $date = $schedule['schedule_date'];
        if (!isset($grouped_schedules[$date])) {
            $grouped_schedules[$date] = [
                'date' => $date,
                'doctor_name' => $schedule['doctor_name'],
                'specialization' => $schedule['specialization'],
                'slots' => []
            ];
        }
        $grouped_schedules[$date]['slots'][] = [
            'id' => $schedule['id'],
            'start_time' => $schedule['start_time'],
            'end_time' => $schedule['end_time'],
            'is_available' => (bool)$schedule['is_available'],
            'max_appointments' => $schedule['max_appointments'],
            'current_appointments' => $schedule['current_appointments']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => array_values($grouped_schedules)
    ]);
}

function addDoctorSchedule() {
    global $conn;
    
    $doctor_id = $_POST['doctor_id'] ?? null;
    $schedule_date = $_POST['schedule_date'] ?? null;
    $start_time = $_POST['start_time'] ?? null;
    $end_time = $_POST['end_time'] ?? null;
    $max_appointments = $_POST['max_appointments'] ?? 1;
    
    if (!$doctor_id || !$schedule_date || !$start_time || !$end_time) {
        throw new Exception('All required fields must be provided');
    }
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $schedule_date)) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }
    
    // Validate and normalize time format
    $start_time = normalizeTimeFormat($start_time);
    $end_time = normalizeTimeFormat($end_time);
    
    if (!$start_time || !$end_time) {
        throw new Exception('Invalid time format. Use HH:MM (24-hour format)');
    }
    
    // Check if the date is in the past
    $today = date('Y-m-d');
    if ($schedule_date < $today) {
        throw new Exception('Cannot create schedules for past dates');
    }
    
    // If it's today, check if the time is in the past
    if ($schedule_date === $today) {
        $current_time = date('H:i');
        if ($start_time <= $current_time) {
            throw new Exception('Cannot create schedules for past times on the same day');
        }
    }
    
    // Validate time logic
    if (strtotime($start_time) >= strtotime($end_time)) {
        throw new Exception('End time must be after start time');
    }
    
    // Check for overlapping schedules
    $check_sql = "SELECT id FROM doctor_schedules 
                  WHERE doctor_id = ? AND schedule_date = ? 
                  AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('isssss', $doctor_id, $schedule_date, $end_time, $start_time, $end_time, $start_time);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->fetch_assoc()) {
        throw new Exception('Schedule overlaps with existing time slot');
    }
    
    $sql = "INSERT INTO doctor_schedules (doctor_id, schedule_date, start_time, end_time, max_appointments) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isssi', $doctor_id, $schedule_date, $start_time, $end_time, $max_appointments);
    $result = $stmt->execute();
    
    if ($result) {
        $schedule_id = $conn->insert_id;
        echo json_encode([
            'status' => 'success',
            'message' => 'Schedule added successfully',
            'schedule_id' => $schedule_id
        ]);
    } else {
        throw new Exception('Failed to add schedule');
    }
}

function updateDoctorSchedule() {
    global $conn;
    
    $schedule_id = $_POST['schedule_id'] ?? null;
    $start_time = $_POST['start_time'] ?? null;
    $end_time = $_POST['end_time'] ?? null;
    $max_appointments = $_POST['max_appointments'] ?? null;
    $is_available = $_POST['is_available'] ?? null;
    
    if (!$schedule_id) {
        throw new Exception('Schedule ID is required');
    }
    
    // Get current schedule data
    $get_sql = "SELECT * FROM doctor_schedules WHERE id = ?";
    $get_stmt = $conn->prepare($get_sql);
    $get_stmt->bind_param('i', $schedule_id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    $current_schedule = $result->fetch_assoc();
    
    if (!$current_schedule) {
        throw new Exception('Schedule not found');
    }
    
    // Use current values if not provided
    $start_time = $start_time ?: $current_schedule['start_time'];
    $end_time = $end_time ?: $current_schedule['end_time'];
    $max_appointments = $max_appointments ?: $current_schedule['max_appointments'];
    $is_available = $is_available !== null ? (bool)$is_available : $current_schedule['is_available'];
    
    // Validate and normalize time format if provided
    if ($start_time) {
        $start_time = normalizeTimeFormat($start_time);
        if (!$start_time) {
            throw new Exception('Invalid start time format. Use HH:MM (24-hour format)');
        }
    }
    if ($end_time) {
        $end_time = normalizeTimeFormat($end_time);
        if (!$end_time) {
            throw new Exception('Invalid end time format. Use HH:MM (24-hour format)');
        }
    }
    
    // Check if the date is in the past
    $today = date('Y-m-d');
    if ($current_schedule['schedule_date'] < $today) {
        throw new Exception('Cannot modify schedules for past dates');
    }
    
    // If it's today, check if the time is in the past
    if ($current_schedule['schedule_date'] === $today) {
        $current_time = date('H:i');
        if ($start_time <= $current_time) {
            throw new Exception('Cannot modify schedules for past times on the same day');
        }
    }
    
    // Validate time logic
    if (strtotime($start_time) >= strtotime($end_time)) {
        throw new Exception('End time must be after start time');
    }
    
    // Check for overlapping schedules (excluding current one)
    $check_sql = "SELECT id FROM doctor_schedules 
                  WHERE doctor_id = ? AND schedule_date = ? AND id != ?
                  AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('isissss', 
        $current_schedule['doctor_id'], 
        $current_schedule['schedule_date'], 
        $schedule_id,
        $end_time, $start_time, $end_time, $start_time
    );
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->fetch_assoc()) {
        throw new Exception('Schedule overlaps with existing time slot');
    }
    
    $sql = "UPDATE doctor_schedules 
            SET start_time = ?, end_time = ?, max_appointments = ?, is_available = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssiii', $start_time, $end_time, $max_appointments, $is_available, $schedule_id);
    $result = $stmt->execute();
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Schedule updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update schedule');
    }
}

function deleteDoctorSchedule() {
    global $conn;
    
    $schedule_id = $_POST['schedule_id'] ?? $_GET['schedule_id'] ?? null;
    
    if (!$schedule_id) {
        throw new Exception('Schedule ID is required');
    }
    
    // Get schedule details first
    $get_sql = "SELECT * FROM doctor_schedules WHERE id = ?";
    $get_stmt = $conn->prepare($get_sql);
    $get_stmt->bind_param('i', $schedule_id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    $schedule = $result->fetch_assoc();
    
    if (!$schedule) {
        throw new Exception('Schedule not found');
    }
    
    // Check if the date is in the past
    $today = date('Y-m-d');
    if ($schedule['schedule_date'] < $today) {
        throw new Exception('Cannot delete schedules for past dates');
    }
    
    // If it's today, check if the time is in the past
    if ($schedule['schedule_date'] === $today) {
        $current_time = date('H:i');
        if ($schedule['start_time'] <= $current_time) {
            throw new Exception('Cannot delete schedules for past times on the same day');
        }
    }
    
    // Check if schedule has appointments
    $check_sql = "SELECT COUNT(*) as appointment_count FROM appointments WHERE schedule_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('i', $schedule_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $count = $result->fetch_assoc();
    
    if ($count['appointment_count'] > 0) {
        throw new Exception('Cannot delete schedule with existing appointments');
    }
    
    $sql = "DELETE FROM doctor_schedules WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $schedule_id);
    $result = $stmt->execute();
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Schedule deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete schedule');
    }
}

function getAvailableSlots() {
    global $conn;
    
    $doctor_id = $_GET['doctor_id'] ?? null;
    $date = $_GET['date'] ?? null;
    
    if (!$doctor_id || !$date) {
        throw new Exception('Doctor ID and date are required');
    }
    
    $sql = "SELECT ds.*, d.name as doctor_name, d.specialization 
            FROM doctor_schedules ds 
            JOIN doctors d ON ds.doctor_id = d.id 
            WHERE ds.doctor_id = ? AND ds.schedule_date = ? 
            AND ds.is_available = 1 
            AND ds.current_appointments < ds.max_appointments
            ORDER BY ds.start_time";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $doctor_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $slots = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => $slots
    ]);
}

/**
 * Normalize time format to HH:MM (24-hour format)
 * Handles various input formats and converts them to HH:MM
 */
function normalizeTimeFormat($timeString) {
    if (empty($timeString)) {
        return false;
    }
    
    // Remove any whitespace
    $timeString = trim($timeString);
    
    // If already in HH:MM format, validate and return
    if (preg_match('/^\d{2}:\d{2}$/', $timeString)) {
        // Validate that it's a valid time
        $parts = explode(':', $timeString);
        $hours = (int)$parts[0];
        $minutes = (int)$parts[1];
        
        if ($hours >= 0 && $hours <= 23 && $minutes >= 0 && $minutes <= 59) {
            return $timeString;
        }
    }
    
    // If in HH:MM:SS format, remove seconds
    if (preg_match('/^(\d{2}):(\d{2}):\d{2}$/', $timeString, $matches)) {
        $hours = (int)$matches[1];
        $minutes = (int)$matches[2];
        
        if ($hours >= 0 && $hours <= 23 && $minutes >= 0 && $minutes <= 59) {
            return $matches[1] . ':' . $matches[2];
        }
    }
    
    // If in H:MM format, pad with zero
    if (preg_match('/^(\d{1}):(\d{2})$/', $timeString, $matches)) {
        $hours = (int)$matches[1];
        $minutes = (int)$matches[2];
        
        if ($hours >= 0 && $hours <= 23 && $minutes >= 0 && $minutes <= 59) {
            return str_pad($matches[1], 2, '0', STR_PAD_LEFT) . ':' . $matches[2];
        }
    }
    
    // Try to parse with strtotime
    $timestamp = strtotime($timeString);
    if ($timestamp !== false) {
        return date('H:i', $timestamp);
    }
    
    // If all else fails, return false
    return false;
}
?>
