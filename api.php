<?php
class bookstoreAPI {
    private $conn;
    private $table = 'books';

    public $id;
    public $title;
    public $author;
    public $description;
    public $cover_image;
    public $created_at;
    public $genre;
    public $published_date;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getBooks() {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY created_at DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getSingleBook() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }




public function search() {
    $query = 'SELECT * FROM ' . $this->table . ' WHERE 1=1';

    // Add conditions based on provided search parameters
    if (!empty($this->title)) {
        $query .= ' AND title LIKE :title';
    }
    if (!empty($this->author)) {
        $query .= ' AND author LIKE :author';
    }
    if (!empty($this->genre)) {
        $query .= ' AND genre LIKE :genre';
    }

    $query .= ' ORDER BY created_at DESC';// Add ordering by created_at

    $stmt = $this->conn->prepare($query);

    // Bind parameters
    if (!empty($this->title)) {
        $this->title = '%' . htmlspecialchars(strip_tags($this->title)) . '%';
        $stmt->bindParam(':title', $this->title);//% is used for wildcard search which is to be used in LIKE clause
    }
    if (!empty($this->author)) {
        $this->author = '%' . htmlspecialchars(strip_tags($this->author)) . '%';
        $stmt->bindParam(':author', $this->author);
    }
    if (!empty($this->genre)) {
        $this->genre = '%' . htmlspecialchars(strip_tags($this->genre)) . '%';
        $stmt->bindParam(':genre', $this->genre);//bindparm is used to bind the parameter to the query which means that the value of the parameter is passed to the query
    }

    if ($stmt->execute()) {
        return $stmt; // Return the PDOStatement if the query executes successfully
    }

    return null; // Return null if the query fails
}

    public function create() {
        $this->cover_image = $this->processImage();

        $query = 'INSERT INTO ' . $this->table . ' 
            SET title = :title, author = :author, genre = :genre, 
            description = :description, published_date = :published_date, cover_image = :cover_image';

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->genre = htmlspecialchars(strip_tags($this->genre));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->published_date = date('Y-m-d', strtotime($this->published_date));

        // Bind
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':author', $this->author);
        $stmt->bindParam(':genre', $this->genre);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':published_date', $this->published_date);
        $stmt->bindParam(':cover_image', $this->cover_image);

        if ($stmt->execute()) {
            $this->logBookAction("CREATE", $this->title);
            return true;
        } else {
            printf("Error: %s.\n", $stmt->error);
            return false;
        }
        error_log("SQL Query: " . $query);
        error_log("Parameters: " . json_encode([
            'title' => $this->title,
            'author' => $this->author,
            'genre' => $this->genre,
            'description' => $this->description,
            'published_date' => $this->published_date,
           'cover_image' => $this->cover_image,
]));
        
    }
     
    public function update() {
        $query = 'UPDATE ' . $this->table . ' 
                  SET title = :title, 
                      author = :author, 
                      genre = :genre, 
                      description = :description, 
                      published_date = :published_date';
        
        // Only update cover_image if it's provided
        if (!empty($this->cover_image)) {
            $query .= ', cover_image = :cover_image';
        }
        
        $query .= ' WHERE id = :id';
    
        $stmt = $this->conn->prepare($query);
    
        // Clean data
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->genre = htmlspecialchars(strip_tags($this->genre));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->published_date = htmlspecialchars(strip_tags($this->published_date));
        $this->id = htmlspecialchars(strip_tags($this->id));
    
        // Bind parameters
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':author', $this->author);
        $stmt->bindParam(':genre', $this->genre);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':published_date', $this->published_date);
        $stmt->bindParam(':id', $this->id);
        
        // Only bind cover_image if it's provided
        if (!empty($this->cover_image)) {
            $this->cover_image = htmlspecialchars(strip_tags($this->cover_image));
            $stmt->bindParam(':cover_image', $this->cover_image);
        }
    
        // Execute query
        if ($stmt->execute()) {
            $this->logBookAction("UPDATE", $this->title);
            return true;
        } else {
            error_log("SQL Error: " . json_encode($stmt->errorInfo()));
            return false;
        }

   
       error_log("SQL Query: " . $query);
       error_log("Parameters: " . json_encode([
            'title' => $this->title,
            'author' => $this->author,
            'genre' => $this->genre,
            'description' => $this->description,
            'published_date' => $this->published_date,
            'cover_image' => $this->cover_image,
]));
    }




    public function delete() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            $this->logBookAction("DELETE", "Book ID " . $this->id);
            return true;
        } else {
            printf("Error: %s.\n", $stmt->error);
            return false;
        }
    }

    private function processImage() {
        if (!isset($_FILES['cover_image']) || $_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
    
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
    
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($fileInfo, $_FILES["cover_image"]["tmp_name"]);
        finfo_close($fileInfo);
    
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];
    
        if (!array_key_exists($mime, $allowedTypes)) {
            return null;
        }
    
        $extension = $allowedTypes[$mime];
        $filename = time() . "_" . uniqid() . ".$extension";
        $target_file = $target_dir . $filename;
    
        if (!move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
            return null;
        }
    
        // Check if GD library functions are available and process the image
        $image = null;
        if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
            $image = imagecreatefromjpeg($target_file);
        } elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) {
            $image = imagecreatefrompng($target_file);
        } elseif ($mime === 'image/gif' && function_exists('imagecreatefromgif')) {
            $image = imagecreatefromgif($target_file);
        }
    
        if ($image) {
            // Resize and add watermark
            $maxWidth = 400;
            $resized = imagescale($image, $maxWidth);
    
            // Add watermark
            $watermark = "DO NOT COPY";
            $fontSize = 5;
            $textColor = imagecolorallocate($resized, 255, 0, 0);
            $x = imagesx($resized) - imagefontwidth($fontSize) * strlen($watermark) - 10;
            $y = imagesy($resized) - imagefontheight($fontSize) - 10;
            imagestring($resized, $fontSize, $x, $y, $watermark, $textColor);
    
            // Save the final image
            if ($mime === 'image/jpeg') {
                imagejpeg($resized, $target_file, 90);
            } elseif ($mime === 'image/png') {
                imagepng($resized, $target_file);
            } elseif ($mime === 'image/gif') {
                imagegif($resized, $target_file);
            }
    
            // Free memory
            imagedestroy($image);
            imagedestroy($resized);
        }
    
        return $filename;
    }


    public function Image() {
        $targetDir = "updated/";
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
    
        $fileName = basename($_FILES["cover_image"]["name"]);
        $targetFile = $targetDir . uniqid() . "_" . $fileName;
    
        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $targetFile)) {
            error_log("Image uploaded successfully to: $targetFile");
            return $targetFile;
        } else {
            error_log("Image upload failed");
            return null;
        }
    }
    
    

    public function logBookAction($action, $bookTitle) {
        $logfile = 'book_log.txt';
        $date = date('Y-m-d H:i:s');
        $entry = "[$date] ACTION: $action | BOOK: $bookTitle\n";
        file_put_contents($logfile, $entry, FILE_APPEND);
    }
  }
?>
