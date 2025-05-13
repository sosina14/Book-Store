<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'new_bookstore';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
public function createBooksTable() {
    $sql = "CREATE TABLE IF NOT EXISTS books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(255) NOT NULL,
        genre VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        description TEXT NOT NULL,
        published_date DATE NOT NULL,
        cover_image VARCHAR(255) NOT NULL
    )";

    try {
        $this->conn->exec($sql);
    } catch (PDOException $e) {
        echo 'Table creation failed: ' . $e->getMessage();
        exit;
    }
    }
}
?>