<?php
header('Content-Type: application/json');
include('../includes/db.php');
include('../includes/functions.php');

ensure_session_started();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// ===================================================
// DOCTORS MANAGEMENT
// ===================================================
if ($action === 'doctors') {

    // ---------------- LIST DOCTORS ----------------
    if ($method === 'GET') {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        if (!empty($search)) {
            // Search functionality - filter by name, email, phone, or specialization
            $searchTerm = '%' . $search . '%';
            $sql = "SELECT d.*, dept.name as department_name 
                    FROM doctors d 
                    LEFT JOIN departments dept ON d.department_id = dept.id 
                    WHERE d.name LIKE ? 
                       OR d.email LIKE ? 
                       OR d.phone LIKE ? 
                       OR d.specialization LIKE ?
                    ORDER BY d.id DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            // No search - return all doctors
            $sql = "SELECT d.*, dept.name as department_name 
                    FROM doctors d 
                    LEFT JOIN departments dept ON d.department_id = dept.id 
                    ORDER BY d.id DESC";
            $result = $conn->query($sql);
        }
        
        $doctors = [];
        while ($row = $result->fetch_assoc()) {
            $doctors[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $doctors]);
        exit;
    }

    // ---------------- ADD DOCTOR ----------------
    if ($method === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
        $description = trim($_POST['description'] ?? '');

        // Image Upload Handling
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/../../uploads/doctors/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $allowed = ['image/jpeg','image/png','image/gif'];
            if (!in_array($_FILES['image']['type'], $allowed)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid image type']);
                exit;
            }

            $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('', true) . '.' . $fileExtension;
            $filepath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                $imagePath = '/doctor-appoinment/uploads/doctors/' . $filename; // save web-accessible path
            }
        }

        $stmt = $conn->prepare(
            "INSERT INTO doctors (user_id, name, email, specialization, phone, description, department_id, image_path) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        // Types: user_id(i), name(s), email(s), specialization(s), phone(s), description(s), department_id(i), image_path(s)
        $user_id = null; // Admin-created doctors don't need a user_id
        $stmt->bind_param("isssssis", $user_id, $name, $email, $specialization, $phone, $description, $department_id, $imagePath);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Doctor added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        exit;
    }

    // ---------------- DELETE DOCTOR ----------------
    if ($method === 'DELETE') {
        parse_str(file_get_contents("php://input"), $deleteVars);
        $id = intval($deleteVars['id'] ?? 0);

        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM doctors WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Doctor deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $stmt->error]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid doctor ID']);
        }
        exit;
    }
}
