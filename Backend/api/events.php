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
        // Create new event
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
        
        $stmt = $conn->prepare('INSERT INTO events (title, description, event_date, event_time, location) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $title, $description, $eventDate, $eventTime, $location);
        
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
