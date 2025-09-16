<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include session configuration first
include('../includes/session_config.php');

require_once '../includes/db.php';
require_once '../includes/functions.php';

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to validate required fields
function validateRequired($fields) {
    foreach ($fields as $field => $value) {
        if (empty($value)) {
            return "Field '$field' is required";
        }
    }
    return null;
}

// Handle POST request - Submit contact message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Invalid JSON input');
        }
        
        // Extract and sanitize form data
        $firstName = sanitizeInput($input['firstName'] ?? '');
        $lastName = sanitizeInput($input['lastName'] ?? '');
        $email = sanitizeInput($input['email'] ?? '');
        $subject = sanitizeInput($input['subject'] ?? '');
        $message = sanitizeInput($input['message'] ?? '');
        
        // Validate required fields
        $requiredFields = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'subject' => $subject,
            'message' => $message
        ];
        
        $validationError = validateRequired($requiredFields);
        if ($validationError) {
            throw new Exception($validationError);
        }
        
        // Validate email format
        if (!validateEmail($email)) {
            throw new Exception('Invalid email format');
        }
        
        // Check if user is logged in (optional)
        $userId = null;
        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        
        // Insert contact message into database
        $stmt = $conn->prepare("
            INSERT INTO contact_messages (user_id, first_name, last_name, email, subject, message, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'new')
        ");
        
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }
        
        $stmt->bind_param("isssss", $userId, $firstName, $lastName, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $messageId = $conn->insert_id;
            
            echo json_encode([
                'success' => true,
                'message' => 'Your message has been sent successfully. We will get back to you soon!',
                'messageId' => $messageId
            ]);
        } else {
            throw new Exception('Failed to save message: ' . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Handle GET request - Get contact messages (Admin or user's own messages)
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Check if user is authenticated
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Authentication required');
        }
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
        $userId = $_SESSION['user_id'];
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        
        $offset = ($page - 1) * $limit;
        
        // Build query based on user role
        $whereClause = '';
        $params = [];
        $types = '';
        
        if ($isAdmin) {
            // Admin can see all messages
            if (!empty($status)) {
                $whereClause = 'WHERE cm.status = ?';
                $params[] = $status;
                $types .= 's';
            }
        } else {
            // Regular users can only see their own messages
            $whereClause = 'WHERE cm.user_id = ?';
            $params[] = $userId;
            $types .= 'i';
            
            if (!empty($status)) {
                $whereClause .= ' AND cm.status = ?';
                $params[] = $status;
                $types .= 's';
            }
        }
        
        // Get total count
        $countQuery = "SELECT COUNT(*) as total FROM contact_messages cm $whereClause";
        $countStmt = $conn->prepare($countQuery);
        
        if (!empty($params)) {
            $countStmt->bind_param($types, ...$params);
        }
        
        $countStmt->execute();
        $totalResult = $countStmt->get_result();
        $total = $totalResult->fetch_assoc()['total'];
        $countStmt->close();
        
        // Get messages with pagination
        $query = "
            SELECT 
                cm.*,
                u.name as user_name,
                admin.name as replied_by_name
            FROM contact_messages cm
            LEFT JOIN users u ON cm.user_id = u.id
            LEFT JOIN users admin ON cm.replied_by = admin.id
            $whereClause
            ORDER BY cm.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'messages' => $messages,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Handle PUT request - Update message status or reply
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // Check if user is authenticated
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Authentication required');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Invalid JSON input');
        }
        
        $messageId = (int)($input['messageId'] ?? 0);
        $status = sanitizeInput($input['status'] ?? '');
        $adminReply = sanitizeInput($input['adminReply'] ?? '');
        $userId = $_SESSION['user_id'];
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        
        if ($messageId <= 0) {
            throw new Exception('Invalid message ID');
        }
        
        // Check if user can modify this message
        if (!$isAdmin) {
            // Regular users can only modify their own messages
            $checkStmt = $conn->prepare("SELECT user_id FROM contact_messages WHERE id = ?");
            $checkStmt->bind_param("i", $messageId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $message = $result->fetch_assoc();
            $checkStmt->close();
            
            if (!$message || $message['user_id'] != $userId) {
                throw new Exception('Unauthorized access to this message');
            }
        }
        
        // Update message
        if (!empty($adminReply) && $isAdmin) {
            // Admin is replying to the message
            $stmt = $conn->prepare("
                UPDATE contact_messages 
                SET status = 'replied', admin_reply = ?, replied_by = ?, replied_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("sii", $adminReply, $userId, $messageId);
        } else if (!empty($status)) {
            // Updating status (admin can set any status, users can only mark as read)
            if ($isAdmin || $status === 'read') {
                $stmt = $conn->prepare("
                    UPDATE contact_messages 
                    SET status = ? 
                    WHERE id = ?
                ");
                $stmt->bind_param("si", $status, $messageId);
            } else {
                throw new Exception('You can only mark messages as read');
            }
        } else {
            throw new Exception('No valid update provided');
        }
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Message updated successfully'
            ]);
        } else {
            throw new Exception('Failed to update message: ' . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

$conn->close();
?>
