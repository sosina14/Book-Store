<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once 'db.php';
include_once 'api.php';

// Initialize database connection
$database = new Database();
$db = $database->connect();

// Initialize bookstore API
$bookstore = new bookstoreAPI($db);

// Get POST data
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


if($bookstore->create()) {
    echo json_encode(array('message' => 'Book created successfully'));
    // Define the logBookAction function to log book actions
    function logBookAction($action, $title) {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] Action: $action, Title: $title" . PHP_EOL;
        file_put_contents('book_actions.log', $logMessage, FILE_APPEND);
    }
    
    logBookAction("CREATE", $bookstore->title);
} else {
    echo json_encode(array('message' => 'Book not created'));
}
