<?php
session_start();

// Database connection
$host = "localhost";
$db   = "library_db";  // Your database
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Determine if this is a registration form (has last_name field)
    if (isset($_POST['last_name'])) {
        // REGISTRATION
        $last_name = trim($_POST['last_name']);
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name'] ?? '');
        $role = trim($_POST['role']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validation
        if (empty($last_name) || empty($first_name) || empty($role) || empty($email) || empty($password) || empty($confirm_password)) {
            die("All required fields must be filled.");
        }

        if ($password !== $confirm_password) {
            die("Passwords do not match.");
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM admin_librarian_acc WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            die("Email is already registered.");
        }
        $stmt->close();

        // Insert new account
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admin_librarian_acc (last_name, first_name, middle_name, role, email, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $last_name, $first_name, $middle_name, $role, $email, $hashed_password);

        if ($stmt->execute()) {
            // Redirect back to login with success message
            header("Location: ../pages/register_admin_librarian.html?success=1");
            exit();
        } else {
            die("Error: " . $stmt->error);
        }

        $stmt->close();

    } elseif (isset($_POST['email']) && isset($_POST['password'])) {
        // LOGIN
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, password, role FROM admin_librarian_acc WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $hashed_password, $role);
            if ($stmt->fetch() && $hashed_password !== null && password_verify($password, $hashed_password)) {
                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['role'] = $role;

                // Redirect based on role
                if ($role === 'admin') {
                    header("Location: ../pages/admin/admin_dashb.php");
                    exit();
                } elseif ($role === 'librarian') {
                    header("Location: ../pages/librarian/librarian_dashb.html");
                    exit();
                } else {
                    die("Invalid role.");
                }
            } else {
                header("Location: ../pages/sign_in_admin_librarian.html?error=wrongpassword");
                exit();
            }
        } else {
            header("Location: ../pages/sign_in_admin_librarian.html?error=nouser");
            exit();
        }

        $stmt->close();
    } else {
        die("Invalid form submission.");
    }
}

$conn->close();
?>
