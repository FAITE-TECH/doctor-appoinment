<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// DB connection
include('../includes/db.php');
include('../includes/functions.php');

$method = $_SERVER['REQUEST_METHOD'];

// Only allow GET requests for public access
if ($method !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}

try {
    // Fetch all gallery images
    $stmt = $conn->prepare('SELECT id, title, description, image_path, created_at FROM gallery ORDER BY created_at DESC');
    $stmt->execute();
    $result = $stmt->get_result();
    $images = [];
    
    while ($row = $result->fetch_assoc()) {
        $images[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'image_path' => $row['image_path'],
            'created_at' => $row['created_at']
        ];
    }
    $stmt->close();
    
    json_response(['gallery' => $images]);
    
} catch (Exception $e) {
    json_response(['error' => 'Failed to fetch gallery images'], 500);
}
?>
