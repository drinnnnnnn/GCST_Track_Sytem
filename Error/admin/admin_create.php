<?php
session_start();

// If not logged in OR not admin, redirect
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: http://localhost/GCST_Track_System/pages/sign_in_admin_librarian.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Book - Admin</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

  <!-- External CSS -->
  <link rel="stylesheet" href="http://localhost/Library-Management-Sytem/styles/admin_css/admin_navbar.css">
  <link rel="stylesheet" href="http://localhost/Library-Management-Sytem/styles/admin_create.css">

  <!-- Page-specific CSS -->
  <style>
    body {
      font-family: "Poppins", sans-serif;
    }

    .main-content {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .form-container {
      background: #fff;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      width: 960px;
      display: flex;
      gap: 40px;
      margin: 15px 0 0 45px;
      height: 570px;
    }

    .form-left,
    .form-right {
      flex: 1;
    }

    .form-left {
      max-width: 400px;
      text-align: center;
    }

    .form-container img {
      width: 100px;
      margin-bottom: 20px;
      object-fit: contain;
    }

    .form-container h2 {
      margin-bottom: 20px;
      color: #333;
    }

    .form-container input,
    .form-container select,
    .form-right textarea {
      width: 94%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .form-right textarea {
      min-height: 340px;
      resize: vertical;
    }

    .form-container button {
      width: 100%;
      padding: 12px;
      background-color: #3976e6;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      margin-top: 15px;
    }

    .form-container button:hover {
      background-color: #2896d6;
    }

    /* Messages */
    .message {
      font-size: 16px;
      font-weight: bold;
      margin-top: 20px;
      padding: 10px;
      border-radius: 6px;
      text-align: center;
      transition: opacity 1s ease-out;
    }

    .success-msg {
      color: #1a7f1a;
      background: #e6ffe6;
      border: 1px solid #b2e6b2;
    }

    .error-msg {
      color: #b30000;
      background: #ffeaea;
      border: 1px solid #ffb3b3;
    }

    .fade-out {
      opacity: 0;
    }
  </style>
</head>

<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div>
      <div class="logo-container">
        <img src="http://localhost/Library-Management-Sytem/assets/img_icon/granby_logo.png" alt="Logo" class="logo-img">
        <h3 class="logo-title">GCST Library</h3>
      </div>

      <nav class="nav-menu">
        <a href="admin_dashb.php" class="nav-link">
          <img src="http://localhost/Library-Management-Sytem/assets/sidebar_img/dashbc.png" alt="Dashboard Icon" class="icon">
          Dashboard
        </a>
        <a href="admin_create.php" class="nav-link active">
          <img src="http://localhost/Library-Management-Sytem/assets/sidebar_img/addgrey.png" alt="Dashboard Icon" class="icon">
          Create
        </a>
        <a href="admin_books.php" class="nav-link">
          <img src="http://localhost/Library-Management-Sytem/assets/sidebar_img/bookpen.png" alt="Dashboard Icon" class="icon">
          Books
        </a>
        <a href="/GCST_Track_System/pages/admin/logout.php" class="nav-link logout" onclick="return confirm('Are you sure you want to logout?')">
          <img src="http://localhost/Library-Management-Sytem/assets/sidebar_img/outv.png" alt="Dashboard Icon" class="icon">
          Logout
        </a>
      </nav>
    </div>
  </aside>

<!-- Main Content -->
<div class="main-content">
  <form action="http://localhost/Library-Management-Sytem/utils/book_data2.php" method="POST" enctype="multipart/form-data">
    <div class="form-container">

      <!-- Left -->
      <div class="form-left">
        <img id="previewImage" src="http://localhost/Library-Management-Sytem/assets/img_bg/book_details.png">
        <h2>Enter Book Details</h2>

        <input type="text" name="book_name" placeholder="Enter Book Name" required>

        <select name="book_category" id="categorySelect" required>
          <option value="" disabled selected>Select Book Category</option>
        </select>

        <input type="text" name="book_author" placeholder="Enter Book Author" required>
        <input type="file" name="book_image" id="bookImageInput" accept="image/*">
        <input type="number" name="quantity" placeholder="Enter Quantity" min="1" required>
      </div>

      <!-- Right -->
      <div class="form-right">
        <textarea name="book_description" placeholder="About the Book (Detailed Description)"></textarea>
        <input type="number" name="fine_value" placeholder="Enter Fine Value (₱)" step="0.01" min="0" required>
        <button type="submit">Add Book</button>
        <div id="message" class="message"></div>
      </div>

    </div>
  </form>
</div>

<!-- Scripts -->
<script>
/* Populate Categories */
async function populateCategories() {
  const res = await fetch('/Library-Management-Sytem/utils/categories_api.php', {
    credentials: 'same-origin'
    });
  const categories = await res.json();
  const select = document.getElementById('categorySelect');

  select.innerHTML = '<option value="" disabled selected>Select Book Category</option>';
  categories.forEach(cat => {
    const opt = document.createElement('option');
    opt.value = cat.name;
    opt.textContent = cat.name;
    select.appendChild(opt);
  });
}
populateCategories();

/* Image Preview */
const fileInput = document.getElementById('bookImageInput');
const previewImage = document.getElementById('previewImage');
const defaultImage = previewImage.src;

fileInput.addEventListener('change', () => {
  const file = fileInput.files[0];
  if (!file) return previewImage.src = defaultImage;

  const reader = new FileReader();
  reader.onload = e => previewImage.src = e.target.result;
  reader.readAsDataURL(file);
});

/* Success / Error Message */
const params = new URLSearchParams(window.location.search);
const status = params.get("status");
const messageDiv = document.getElementById("message");

if (status === "success") {
  messageDiv.textContent = "✅ Book added successfully.";
  messageDiv.classList.add("success-msg");
} else if (status === "error") {
  messageDiv.textContent = "❌ Failed to add book.";
  messageDiv.classList.add("error-msg");
}

if (status) {
  setTimeout(() => {
    messageDiv.classList.add("fade-out");
    setTimeout(() => messageDiv.style.display = "none", 1000);
  }, 4000);
}
</script>

/* Logout */
<a href="/GCST_Track_System/pages/admin/logout.php"
   class="nav-link logout"
   onclick="return confirm('Are you sure you want to logout?')">

</body>
</html>
