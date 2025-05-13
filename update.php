<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, PUT');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once 'db.php';
include_once 'api.php';

// Initialize database connection
$database = new Database();
$db = $database->connect();

// Initialize bookstore API
$bookstore = new bookstoreAPI($db);
// Get POST data
$bookstore->id = $_POST['id'] ?? null;
$bookstore->title = $_POST['title'] ?? null;
$bookstore->author = $_POST['author'] ?? null;
$bookstore->description = $_POST['description'] ?? null;
$bookstore->genre = $_POST['genre'] ?? null;
$bookstore->published_date = $_POST['published_date'] ?? null;

// Handle file upload
if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $bookstore->cover_image = $_FILES['cover_image'];
} else {
    $bookstore->cover_image = null;
}
// Perform the update
if ($bookstore->update()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Book updated successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Book not updated'
    ]);
}
?>