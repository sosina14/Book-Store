<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

include_once 'db.php';
include_once 'api.php';

// Initialize database connection
$database = new Database();
$db = $database->connect();

// Initialize bookstore API
$bookstore = new bookstoreAPI($db);

// Check if an ID is provided
if (isset($_GET['id'])) {
    $bookstore->id = $_GET['id'];
    $result = $bookstore->getSingleBook();
} else {
    $result = $bookstore->getBooks();
}

$num = $result->rowCount();
if ($num > 0) {
    $books_arr = array();
    $books_arr['data'] = array();

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $book_item = array(
            'id' => $id,
            'title' => $title,
            'author' => $author,
            'genre' => $genre,
            'description' => html_entity_decode($description),
            'published_date' => $published_date,
            'cover_image' =>!empty($cover_image) ? $cover_image : 'default_image.jpg',
            'created_at' => $created_at,
        );
        array_push($books_arr['data'], $book_item);
    }
    echo json_encode($books_arr);
} else {
    echo json_encode(array('message' => 'No books found'));
}
?>