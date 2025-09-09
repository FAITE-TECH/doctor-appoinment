<?php
header('Content-Type: application/json');
ini_set('display_errors', 1); // Debugging
error_reporting(E_ALL);

// Session
include('../includes/session_config.php');

// DB + helper functions
include('../includes/db.php');
include('../includes/functions.php');

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// --- AUTH CHECK ---
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    json_response(['error' => 'Unauthorized access'], 403);
}

// --- METHODS ---
switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $conn->prepare('SELECT * FROM gallery WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $image = $result->fetch_assoc();
            $stmt->close();

            if ($image) {
                json_response(['image' => $image]);
            } else {
                json_response(['error' => 'Image not found'], 404);
            }
        } else {
            $stmt = $conn->prepare('SELECT * FROM gallery ORDER BY created_at DESC');
            $stmt->execute();
            $result = $stmt->get_result();
            $images = [];
            while ($row = $result->fetch_assoc()) {
                $images[] = $row;
            }
            $stmt->close();

            json_response(['gallery' => $images]);
        }
        break;

    case 'POST':
        // --- UPLOAD NEW IMAGE ---
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            json_response(['error' => 'Image upload failed'], 422);
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($title)) {
            json_response(['error' => 'Image title is required'], 422);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            json_response(['error' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed'], 422);
        }

        // Upload dir
        $uploadDir = __DIR__ . '/../../uploads/gallery/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $fileExtension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
            $stmt = $conn->prepare('INSERT INTO gallery (title, description, image_path) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $title, $description, $filename);

            if ($stmt->execute()) {
                $imageId = $stmt->insert_id;
                $stmt->close();

                json_response([
                    'message' => 'Image uploaded successfully',
                    'image_id' => $imageId,
                    'filename' => $filename
                ], 201);
            } else {
                $stmt->close();
                unlink($filepath);
                json_response(['error' => 'Failed to save image to database'], 500);
            }
        } else {
            json_response(['error' => 'Failed to upload image'], 500);
        }
        break;

    case 'PUT':
        // --- UPDATE IMAGE DETAILS ---
        if (!$id) {
            json_response(['error' => 'Image ID is required'], 422);
        }

        // Handle both FormData and JSON requests
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            // JSON request
            $body = get_json_body();
            $title = trim($body['title'] ?? '');
            $description = trim($body['description'] ?? '');
        } else {
            // FormData request - parse from php://input
            $input = file_get_contents("php://input");
            parse_str($input, $putVars);
            $title = trim($putVars['title'] ?? '');
            $description = trim($putVars['description'] ?? '');
        }

        if (empty($title)) {
            json_response(['error' => 'Image title is required'], 422);
        }

        $stmt = $conn->prepare('UPDATE gallery SET title = ?, description = ? WHERE id = ?');
        $stmt->bind_param('ssi', $title, $description, $id);

        if ($stmt->execute()) {
            $stmt->close();
            json_response(['message' => 'Image updated successfully']);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to update image'], 500);
        }
        break;

    case 'DELETE':
        if (!$id) {
            json_response(['error' => 'Image ID is required'], 422);
        }

        $stmt = $conn->prepare('SELECT image_path FROM gallery WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $image = $result->fetch_assoc();
        $stmt->close();

        if ($image) {
            $filepath = __DIR__ . '/../../uploads/gallery/' . $image['image_path'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }

        $stmt = $conn->prepare('DELETE FROM gallery WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $stmt->close();
            json_response(['message' => 'Image deleted successfully']);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to delete image'], 500);
        }
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}
