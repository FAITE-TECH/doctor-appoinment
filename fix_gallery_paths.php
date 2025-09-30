<?php
// Fix Gallery Paths Script
// This script fixes existing gallery image paths in the database

include('Backend/includes/db.php');

echo "<h2>Gallery Path Fix Script</h2>\n";

// Check current gallery entries
$stmt = $conn->prepare('SELECT id, title, image_path FROM gallery');
$stmt->execute();
$result = $stmt->get_result();

echo "<h3>Current Gallery Entries:</h3>\n";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th>ID</th><th>Title</th><th>Current Path</th><th>Action Needed</th></tr>\n";

$needsFix = [];

while ($row = $result->fetch_assoc()) {
    $needsFixing = false;
    $action = "OK - Filename only";
    
    // Check if path contains full path instead of just filename
    if (strpos($row['image_path'], '/') !== false) {
        $needsFixing = true;
        $action = "NEEDS FIX - Contains full path";
        $needsFix[] = $row;
    }
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
    echo "<td>" . htmlspecialchars($row['image_path']) . "</td>";
    echo "<td style='color: " . ($needsFixing ? 'red' : 'green') . ";'>" . $action . "</td>";
    echo "</tr>\n";
}

echo "</table>\n";
$stmt->close();

if (!empty($needsFix)) {
    echo "<h3>Fixing Problematic Entries:</h3>\n";
    
    foreach ($needsFix as $entry) {
        // Extract just the filename from the full path
        $filename = basename($entry['image_path']);
        
        $updateStmt = $conn->prepare('UPDATE gallery SET image_path = ? WHERE id = ?');
        $updateStmt->bind_param('si', $filename, $entry['id']);
        
        if ($updateStmt->execute()) {
            echo "<p style='color: green;'>✓ Fixed ID {$entry['id']} ({$entry['title']}): '{$entry['image_path']}' → '{$filename}'</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Failed to fix ID {$entry['id']} ({$entry['title']})</p>\n";
        }
        
        $updateStmt->close();
    }
    
    echo "<p><strong>Database paths have been fixed!</strong></p>\n";
} else {
    echo "<p style='color: green;'><strong>All gallery paths are already correct!</strong></p>\n";
}

echo "<hr>\n";
echo "<p><a href='index.php'>← Back to Main Page</a></p>\n";
?>