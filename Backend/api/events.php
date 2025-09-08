<?php
header('Content-Type: application/json');

// Include session configuration first
include('../includes/session_config.php');

// Your existing includes
include('../includes/db.php');
include('../includes/functions.php');

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    json_response(['error' => 'Unauthorized access'], 403);
}

switch ($method) {
    case 'GET':
        if ($id) {
            // Get specific event
            $stmt = $conn->prepare('SELECT * FROM events WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $event = $result->fetch_assoc();
            $stmt->close();
            
            if ($event) {
                json_response(['event' => $event]);
            } else {
                json_response(['error' => 'Event not found'], 404);
            }
        } else {
            // Get all events
            $stmt = $conn->prepare('SELECT * FROM events ORDER BY event_date DESC, event_time ASC');
            $stmt->execute();
            $result = $stmt->get_result();
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            $stmt->close();
            
            json_response(['events' => $events]);
        }
        break;
        
    case 'POST':
        // Create new event (support JSON and multipart with optional image)
        // Ensure image column exists
        $stmt = $conn->prepare("SHOW COLUMNS FROM events LIKE 'image_path'");
        $stmt->execute();
        $result = $stmt->get_result();
        $hasImageColumn = $result && $result->num_rows > 0;
        $stmt->close();
        if (!$hasImageColumn) {
            $conn->query("ALTER TABLE events ADD COLUMN image_path VARCHAR(500) NULL AFTER location");
        }

        if (!empty($_POST) || !empty($_FILES)) {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $eventDate = $_POST['event_date'] ?? '';
            $eventTime = $_POST['event_time'] ?? null;
            $location = trim($_POST['location'] ?? '');
        } else {
            $body = get_json_body();
            require_fields($body, ['title', 'description', 'event_date']);
            $title = trim($body['title']);
            $description = trim($body['description']);
            $eventDate = $body['event_date'];
            $eventTime = $body['event_time'] ?? null;
            $location = trim($body['location'] ?? '');
        }
        
        if (empty($title)) {
            json_response(['error' => 'Event title is required'], 422);
        }
        
        if (empty($eventDate)) {
            json_response(['error' => 'Event date is required'], 422);
        }
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
            json_response(['error' => 'Invalid date format. Use YYYY-MM-DD'], 422);
        }
        
        // Normalize/Validate time format if provided (accept HH:MM or HH:MM:SS)
        if ($eventTime) {
            if (preg_match('/^\d{2}:\d{2}$/', $eventTime)) {
                $eventTime = $eventTime . ':00';
            } elseif (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $eventTime)) {
                json_response(['error' => 'Invalid time format. Use HH:MM or HH:MM:SS'], 422);
            }
        }
        
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg','image/png','image/gif'];
            if (!in_array($_FILES['image']['type'], $allowed)) {
                json_response(['error' => 'Invalid image type'], 422);
            }
            $uploadDir = __DIR__ . '/../../uploads/events/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('', true) . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                json_response(['error' => 'Failed to upload image'], 500);
            }
            $imagePath = $filename;
        }
        
        $stmt = $conn->prepare('INSERT INTO events (title, description, event_date, event_time, location, image_path) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssss', $title, $description, $eventDate, $eventTime, $location, $imagePath);
        
        if ($stmt->execute()) {
            $eventId = $stmt->insert_id;
            $stmt->close();
            
            json_response([
                'message' => 'Event created successfully',
                'event_id' => $eventId
            ], 201);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to create event'], 500);
        }
        break;
        
    case 'PUT':
        // Update event
        if (!$id) {
            json_response(['error' => 'Event ID is required'], 422);
        }
        
        $body = get_json_body();
        require_fields($body, ['title', 'description', 'event_date']);
        
        $title = trim($body['title']);
        $description = trim($body['description']);
        $eventDate = $body['event_date'];
        $eventTime = $body['event_time'] ?? null;
        $location = trim($body['location'] ?? '');
        
        if (empty($title)) {
            json_response(['error' => 'Event title is required'], 422);
        }
        
        if (empty($eventDate)) {
            json_response(['error' => 'Event date is required'], 422);
        }
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
            json_response(['error' => 'Invalid date format. Use YYYY-MM-DD'], 422);
        }
        
        // Validate time format if provided
        if ($eventTime && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $eventTime)) {
            json_response(['error' => 'Invalid time format. Use HH:MM:SS'], 422);
        }
        
        $stmt = $conn->prepare('UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ? WHERE id = ?');
        $stmt->bind_param('sssssi', $title, $description, $eventDate, $eventTime, $location, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            json_response(['message' => 'Event updated successfully']);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to update event'], 500);
        }
        break;
        
    case 'DELETE':
        // Delete event
        if (!$id) {
            json_response(['error' => 'Event ID is required'], 422);
        }
        
        $stmt = $conn->prepare('DELETE FROM events WHERE id = ?');
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            json_response(['message' => 'Event deleted successfully']);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to delete event'], 500);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}
?>
