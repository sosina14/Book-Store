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

// Get search parameters
$bookstore->title = isset($_GET['title']) ? $_GET['title'] : '';
$bookstore->author = isset($_GET['author']) ? $_GET['author'] : '';
$bookstore->genre = isset($_GET['genre']) ? $_GET['genre'] : '';

// Perform search

$result = $bookstore->search();
if ($result && $result->rowCount() > 0) {
    $books_arr = array();
    $books_arr['data'] = array();

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $book_item = array(
            'id' => $id,
            'title' => $title,
            'author' => $author,
            'description' => html_entity_decode($description),
            'cover_image' => $cover_image,
            'genre' => $genre,
            'published_date' => $published_date,
        );
        array_push($books_arr['data'], $book_item);
    }
    echo json_encode($books_arr);
} else {
    echo json_encode(array('message' => 'No books found'));
}