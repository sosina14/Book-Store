<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once 'db.php';
include_once 'api.php';

// Initialize database connection
$database = new Database();
$db = $database->connect();

// Initialize bookstore API
$bookstore = new bookstoreAPI($db);

// Get raw input data
$data = json_decode(file_get_contents("php://input"));

// Validate the ID
if (empty($data->id)) {
    http_response_code(400);
    echo json_encode(['message' => 'Book ID is required']);
    exit;
}

// Retrieve the book's title before deletion
$bookstore->id = $data->id;
$bookDetails = $bookstore->getSingleBook();
$bookTitleBeforeDelete = null;

if ($bookDetails && $bookDetails->rowCount() > 0) {
    $book = $bookDetails->fetch(PDO::FETCH_ASSOC);
    $bookTitleBeforeDelete = $book['title'];
}

// Attempt to delete the book
if ($bookstore->delete()) {
    echo json_encode(['message' => 'Book deleted successfully']);

    // Log the action
    function logBookAction($action, $title) {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] Action: $action, Title: $title" . PHP_EOL;
        file_put_contents('book_actions.log', $logMessage, FILE_APPEND);
    }

    logBookAction("DELETE", $bookTitleBeforeDelete ?? 'Unknown Title');
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Book not deleted']);
}
?>