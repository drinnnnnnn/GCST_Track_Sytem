<?php
// Database configuration
require_once __DIR__ . '/../config/db_connect.php';
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS $database");
$conn->select_db($database);

// Create table if not exists
$createTableSQL = "
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_name VARCHAR(255) NOT NULL,
    book_category VARCHAR(255) NOT NULL,
    book_author VARCHAR(255) NOT NULL,
    book_description TEXT,
    book_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
";
$conn->query($createTableSQL);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bookName = $conn->real_escape_string($_POST['book_name']);
    $bookCategory = $conn->real_escape_string($_POST['book_category']);
    $bookAuthor = $conn->real_escape_string($_POST['book_author']);
    $bookDescription = $conn->real_escape_string($_POST['book_description']);
    $quantity = (int)$_POST['quantity']; // Ensure quantity is an integer
    $fineValue = (float)$_POST['fine_value']; // Ensure fine value is a float

    $imagePath = "";

    // Handle file upload
    if (isset($_FILES["book_image"]) && $_FILES["book_image"]["error"] === UPLOAD_ERR_OK) {
        $uploadDir = "/GCST_Track_System/uploads/";
        $targetDir = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileTmpPath = $_FILES["book_image"]["tmp_name"];
        $fileName = basename($_FILES["book_image"]["name"]);
        $uniqueName = uniqid() . "_" . $fileName;
        $targetPath = $targetDir . $uniqueName;

        $imagePath = $uploadDir . $uniqueName;

        if (!move_uploaded_file($fileTmpPath, $targetPath)) {
            $imagePath = "";
        }
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO books (book_name, book_category, book_author, book_description, book_image, quantity, fine_value) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssid", $bookName, $bookCategory, $bookAuthor, $bookDescription, $imagePath, $quantity, $fineValue);

    if ($stmt->execute()) {
        updateBookCount($conn);
        header("Location: http://localhost/GCST_Track_System/pages/admin/create.html?status=success");
        exit();
    } else {
        header("Location: http://localhost/GCST_Track_System/pages/admin/create.html?status=error");
        exit();
    }

    $stmt->close();
}
function updateBookCount($conn) {
    $result = $conn->query("SELECT COUNT(*) AS cnt FROM books");
    $row = $result->fetch_assoc();
    $total = (int)$row['cnt'];
    // If count_items table is empty, insert a row
    $conn->query("INSERT INTO count_items (total_books) SELECT $total WHERE NOT EXISTS (SELECT 1 FROM count_items)");
    // Otherwise, update the row
    $conn->query("UPDATE count_items SET total_books = $total");
}

$conn->close();
?>
