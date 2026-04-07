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
  <title>Edit Book - Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="http://localhost/GCST_Track_System/styles/admin_css/admin_navbar.css" />
  <link rel="stylesheet" href="http://localhost/GCST_Track_System/styles/admin_create.css" />
</head>
<style>
      .main-content {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .form-container {
      background: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      width: 960px; /* wider for side-by-side */
      max-width: 100;
      text-align: center;
      display: flex;
      gap: 40px;
      justify-content: center;
      align-items: flex-start;
      margin-left: 45px;
      margin-top: 15px;
      height: 600px; /* Adjusted height for better layout */
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

    .form-container input[type="text"],
    .form-container input[type="file"],
    .form-container input[type="number"]{
      width: 94%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .form-container button {
      width: 100%;
      padding: 12px;
      background-color: #3976e6;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      margin-top: 15px;
      cursor: pointer;
    }

    .form-container button:hover {
      background-color: #2896d6;
    }

.form-right textarea {
  height: auto !important;
  min-height: 340px;
  width: 93%;
  padding: 14px; 
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 1rem;
  resize: vertical;
}
  .form-right button {
  margin-top: 15px;
  width: 100%;
  padding: 12px;
  background-color: #3976e6;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
}

.form-right button:hover {
  background-color: #2896d6;
}

/*  Messages and Alerts */

.message, .fade-message {
  font-size: 16px;
  text-align: center;
  margin-top: 20px;
  font-weight: bold;
  padding: 10px 0;
  border-radius: 6px;
  background: #f5f5f5;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  opacity: 1;
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
  transition: opacity 1s ease-out;
}
</style>
<body>

<aside class="sidebar">
    <div>
      <div class="logo-container">
        <img src="http://localhost/GCST_Track_System/assets/img_icon/granby_logo.png" alt="Logo" class="logo-img">
        <h3 class="logo-title">GCST Library</h3>
      </div>

      <nav class="nav-menu">
        <a href="admin_dashb.php" class="nav-link active">
          <img src="http://localhost/GCST_Track_System/assets/sidebar_img/dashbc.png" alt="Dashboard Icon" class="icon">
          Dashboard
        </a>
        <a href="admin_create.php" class="nav-link">
          <img src="http://localhost/GCST_Track_System/assets/sidebar_img/addgrey.png" alt="Dashboard Icon" class="icon">
          Create
        </a>
        <a href="admin_books.php" class="nav-link">
          <img src="http://localhost/GCST_Track_System/assets/sidebar_img/bookpen.png" alt="Dashboard Icon" class="icon">
          Books
        </a>
        <a href="/GCST_Track_System/pages/admin/logout.php" class="nav-link logout" onclick="return confirm('Are you sure you want to logout?')">
          <img src="http://localhost/GCST_Track_System/assets/sidebar_img/outv.png" alt="Dashboard Icon" class="icon">
          Logout
        </a>
      </nav>
    </div>
  </aside>

  <div class="main-content">
    <form action="http://localhost/GCST_Track_System/utils/edit_book2.php" method="POST" enctype="multipart/form-data">
      <div class="form-container">
<div class="form-left">
  <img id="previewImage" src="http://localhost/GCST_Track_System/assets/img_bg/book_details.png" alt="Book Image" />
  <h2>Edit Book Details</h2>
  <input type="text" name="book_name" placeholder="Enter Book Name" required />
  
<select name="book_category" class="book_category" required style="width:400px; height:45px; padding:9px; border-radius:6px; border:1px solid #ccc; font: size 12px;;" id="categorySelect">
    <option value="" disabled selected>Select Book Category</option>
</select>
    <input type="text" name="book_author" placeholder="Enter Book Author" required />
    <input type="file" name="book_image" id="bookImageInput" accept="image/*" />
    <input type="number" name="quantity" placeholder="Enter Quantity" min="1" required />
  </div>

  <div class="form-right">
    <textarea name="book_description" placeholder="About the Book (Detailed Description)"></textarea>
    <input type="number" name="fine_value" placeholder="Enter Fine Value (₱)" step="0.01" min="0" required />
    <button type="submit">Edit Book</button>
     <div id="message" class="message"></div>
  </div>
</div>

  <script>
    const fileInput = document.getElementById('bookImageInput');
    const previewImage = document.getElementById('previewImage');
    const defaultImage = 'http://localhost/GCST_Track_System/assets/img_bg/book_details.png'; // Default image path

    fileInput.addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewImage.src = e.target.result;
        }
        reader.readAsDataURL(file);
      } else {
        previewImage.src = defaultImage;
      }
    });
  </script>


<script>
async function populateCategories(selectedCategory = null) {
    const res = await fetch('/GCST_Track_System/utils/categories_api.php', {
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

    // Set the selected category after populating the options
    if (selectedCategory) {
        select.value = selectedCategory;
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const params = new URLSearchParams(window.location.search);
    const bookId = params.get('book_id');

    if (!bookId) return;

    // Fetch book data for all fields
    try {
        const res = await fetch(`/GCST_Track_System/utils/get_book.php?book_id=${encodeURIComponent(bookId)}`, {
            credentials: 'same-origin'
        });
        if (!res.ok) throw new Error('Failed to fetch book data');
        const book = await res.json();

        // Autofill form fields with fetched data
        document.querySelector('input[name="book_name"]').value = book.book_name || '';
        document.querySelector('input[name="book_author"]').value = book.book_author || '';
        document.querySelector('textarea[name="book_description"]').value = book.book_description || '';
        document.querySelector('input[name="quantity"]').value = book.quantity || '';
        document.querySelector('input[name="fine_value"]').value = book.fine_value || '';
        document.getElementById('previewImage').src = book.book_image && book.book_image !== ''
            ? book.book_image
            : 'http://localhost/GCST_Track_System/assets/img_bg/book_details.png';

        // Populate categories and set the selected category
        await populateCategories(book.book_category);

        // Add hidden input for book_id if not already present
        let hidden = document.querySelector('input[name="book_id"]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'book_id';
            document.querySelector('form').appendChild(hidden);
        }
        hidden.value = bookId;
    } catch (err) {
        document.getElementById('message').textContent = "❌ Failed to load book data.";
        document.getElementById('message').classList.add("error-msg");
    }
});
</script>

<script>
  const urlParams = new URLSearchParams(window.location.search);
  const status = urlParams.get("status");
  const messageDiv = document.getElementById("message");
  if (status === "edited") {
    messageDiv.textContent = "✅ Book edited successfully.";
    messageDiv.classList.add("success-msg");
  } else if (status === "error") {
    messageDiv.textContent = "❌ Failed to edit book. Please try again.";
    messageDiv.classList.add("error-msg");
  }
  if (status) {
    setTimeout(() => {
      messageDiv.classList.add("fade-out");
      setTimeout(() => {
        messageDiv.style.display = "none";
      }, 1000);
    }, 4000);
  }
</script>

</body>
</html>
