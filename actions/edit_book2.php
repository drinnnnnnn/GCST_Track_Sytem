<?php
// Database configuration
require_once __DIR__ . '/../config/db_connect.php';
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$imagePath = isset($imagePath) ? $imagePath : "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bookId = $_POST['book_id'];
    $bookName = $_POST['book_name'];
    $bookCategory = $_POST['book_category'];
    $bookAuthor = $_POST['book_author'];
    $bookDescription = $_POST['book_description'];
    $quantity = $_POST['quantity'];
    $quantity = (int)$_POST['quantity']; // Ensure quantity is an integer
    $fineValue = (float)$_POST['fine_value']; // Ensure fine value is a float

    // Handle file upload if a new image is provided
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
            $imagePath = ""; // fallback if move fails
        }
    }


if ($bookId > 0) {
    if ($imagePath !== "") {
        $stmt = $conn->prepare("UPDATE books SET book_name=?, book_category=?, book_author=?, book_description=?, book_image=?, quantity=?, fine_value=? WHERE book_id=?");
        $stmt->bind_param("ssssssdi", $bookName, $bookCategory, $bookAuthor, $bookDescription, $imagePath, $quantity, $fineValue, $bookId);
    } else {
        $stmt = $conn->prepare("UPDATE books SET book_name=?, book_category=?, book_author=?, book_description=?, quantity=?, fine_value=? WHERE book_id=?");
        $stmt->bind_param("ssssidi", $bookName, $bookCategory, $bookAuthor, $bookDescription, $quantity, $fineValue, $bookId);
    }

    if ($stmt->execute()) {
        header("Location: /GCST_Track_System/pages/admin/edit_book.html?book_id=$bookId&status=edited");
        exit();
    } else {
        header("Location: /GCST_Track_System/pages/admin/edit_book.html?book_id=$bookId&status=error");
        exit();
    }
}
}

$conn->close();
?>
