<?php
header('Content-Type: application/json');
include('../includes/db.php');
include('../includes/functions.php');

ensure_session_started();

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    json_response(['error' => 'Unauthorized access'], 403);
}

switch ($method) {
    case 'GET':
        if ($id) {
            // Get specific doctor
            $stmt = $conn->prepare('SELECT d.*, u.name, u.email FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $doctor = $result->fetch_assoc();
            $stmt->close();
            
            if ($doctor) {
                json_response(['doctor' => $doctor]);
            } else {
                json_response(['error' => 'Doctor not found'], 404);
            }
        } else {
            // Get all doctors
            $stmt = $conn->prepare('SELECT d.*, u.name, u.email FROM doctors d JOIN users u ON d.user_id = u.id ORDER BY d.created_at DESC');
            $stmt->execute();
            $result = $stmt->get_result();
            $doctors = [];
            while ($row = $result->fetch_assoc()) {
                $doctors[] = $row;
            }
            $stmt->close();
            
            json_response(['doctors' => $doctors]);
        }
        break;
        
    case 'POST':
        // Create new doctor (without user account)
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $specialization = trim($_POST['specialization'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if (empty($name) || empty($email) || empty($specialization)) {
            json_response(['error' => 'Name, email, and specialization are required'], 422);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['error' => 'Invalid email format'], 422);
        }
        
        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/doctors/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $fileExtension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                $imagePath = $filename;
            }
        }
        
        // Create doctor record directly (no user account)
        $stmt = $conn->prepare('INSERT INTO doctors (name, email, specialization, phone, image_path) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $name, $email, $specialization, $phone, $imagePath);
        
        if ($stmt->execute()) {
            $doctorId = $stmt->insert_id;
            $stmt->close();
            
            json_response([
                'message' => 'Doctor created successfully',
                'doctor_id' => $doctorId
            ], 201);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to create doctor'], 500);
        }
        break;
        
    case 'PUT':
        // Update doctor
        if (!$id) {
            json_response(['error' => 'Doctor ID is required'], 422);
        }
        
        $body = get_json_body();
        require_fields($body, ['specialization']);
        
        $specialization = trim($body['specialization']);
        $phone = trim($body['phone'] ?? '');
        
        if (empty($specialization)) {
            json_response(['error' => 'Specialization is required'], 422);
        }
        
        $stmt = $conn->prepare('UPDATE doctors SET specialization = ?, phone = ? WHERE id = ?');
        $stmt->bind_param('ssi', $specialization, $phone, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            json_response(['message' => 'Doctor updated successfully']);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to update doctor'], 500);
        }
        break;
        
    case 'DELETE':
        // Delete doctor
        if (!$id) {
            json_response(['error' => 'Doctor ID is required'], 422);
        }
        
        // Get user_id before deletion
        $stmt = $conn->prepare('SELECT user_id FROM doctors WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $doctor = $result->fetch_assoc();
        $stmt->close();
        
        if ($doctor) {
            $userId = $doctor['user_id'];
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Delete doctor record first (due to foreign key constraint)
                $stmt = $conn->prepare('DELETE FROM doctors WHERE id = ?');
                $stmt->bind_param('i', $id);
                
                if (!$stmt->execute()) {
                    throw new Exception('Failed to delete doctor record');
                }
                $stmt->close();
                
                // Delete user account
                $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
                $stmt->bind_param('i', $userId);
                
                if (!$stmt->execute()) {
                    throw new Exception('Failed to delete user account');
                }
                $stmt->close();
                
                // Commit transaction
                $conn->commit();
                
                json_response(['message' => 'Doctor deleted successfully']);
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                json_response(['error' => $e->getMessage()], 500);
            }
        } else {
            json_response(['error' => 'Doctor not found'], 404);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}
?>
