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
            // Get specific service
            $stmt = $conn->prepare('SELECT * FROM services WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $service = $result->fetch_assoc();
            $stmt->close();
            
            if ($service) {
                json_response(['service' => $service]);
            } else {
                json_response(['error' => 'Service not found'], 404);
            }
        } else {
            // Get all services
            $stmt = $conn->prepare('SELECT * FROM services ORDER BY created_at DESC');
            $stmt->execute();
            $result = $stmt->get_result();
            $services = [];
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }
            $stmt->close();
            
            json_response(['services' => $services]);
        }
        break;
        
    case 'POST':
        // Create new service
        $body = get_json_body();
        require_fields($body, ['name', 'description', 'price']);
        
        $name = trim($body['name']);
        $description = trim($body['description']);
        $price = floatval($body['price']);
        
        if (empty($name)) {
            json_response(['error' => 'Service name is required'], 422);
        }
        
        if ($price < 0) {
            json_response(['error' => 'Price must be non-negative'], 422);
        }
        
        $stmt = $conn->prepare('INSERT INTO services (name, description, price) VALUES (?, ?, ?)');
        $stmt->bind_param('ssd', $name, $description, $price);
        
        if ($stmt->execute()) {
            $serviceId = $stmt->insert_id;
            $stmt->close();
            
            json_response([
                'message' => 'Service created successfully',
                'service_id' => $serviceId
            ], 201);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to create service'], 500);
        }
        break;
        
    case 'PUT':
        // Update service
        if (!$id) {
            json_response(['error' => 'Service ID is required'], 422);
        }
        
        $body = get_json_body();
        require_fields($body, ['name', 'description', 'price']);
        
        $name = trim($body['name']);
        $description = trim($body['description']);
        $price = floatval($body['price']);
        
        if (empty($name)) {
            json_response(['error' => 'Service name is required'], 422);
        }
        
        if ($price < 0) {
            json_response(['error' => 'Price must be non-negative'], 422);
        }
        
        $stmt = $conn->prepare('UPDATE services SET name = ?, description = ?, price = ? WHERE id = ?');
        $stmt->bind_param('ssdi', $name, $description, $price, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            json_response(['message' => 'Service updated successfully']);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to update service'], 500);
        }
        break;
        
    case 'DELETE':
        // Delete service
        if (!$id) {
            json_response(['error' => 'Service ID is required'], 422);
        }
        
        $stmt = $conn->prepare('DELETE FROM services WHERE id = ?');
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            json_response(['message' => 'Service deleted successfully']);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to delete service'], 500);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}
?>
