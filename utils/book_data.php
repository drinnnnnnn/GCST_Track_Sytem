<?php
// Database connection
$connection = mysqli_connect("localhost", "root", "", "library_db", 3306);
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Collect POST data safely
$BOOKname = $_POST['book_name'];
$BOOKcategory = $_POST['book_category'];
$BOOKauthor = $_POST['book_author'];

// Handle the file upload
$image_path = null; // default null if no file uploaded

if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] == UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/'; // Folder to save images (adjust path as needed)
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // create folder if not exists
    }

    $fileTmpPath = $_FILES['book_image']['tmp_name'];
    $fileName = basename($_FILES['book_image']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Allowed image extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileExt, $allowedExtensions)) {
        // Generate a unique file name to avoid overwriting
        $newFileName = uniqid('book_', true) . '.' . $fileExt;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Save relative path to DB, e.g. "uploads/book_xxx.jpg"
            $image_path = 'uploads/' . $newFileName;
        } else {
            die("Error moving the uploaded file.");
        }
    } else {
        die("Unsupported file type. Allowed: jpg, jpeg, png, gif.");
    }
}

// Prepare SQL with image path included
$sql = "INSERT INTO books (book_name, book_category, book_author, book_image) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($connection, $sql);

if ($stmt === false) {
    die("MySQL prepare error: " . mysqli_error($connection));
}

// Bind parameters including image path (can be null)
mysqli_stmt_bind_param($stmt, "ssss", $BOOKname, $BOOKcategory, $BOOKauthor, $image_path);

// Execute
if (mysqli_stmt_execute($stmt)) {
    echo "New record created successfully";
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}

// Cleanup
mysqli_stmt_close($stmt);
mysqli_close($connection);
?>
