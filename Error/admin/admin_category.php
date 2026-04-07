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
  <title>Manage Category - Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="http://localhost/Library-Management-Sytem/styles/admin_css/admin_navbar.css" />
</head>

  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100vh;
      overflow: hidden;
      background: linear-gradient(to bottom, #334155, #0f172a);
      background-attachment: fixed;
      font-family: 'Poppins', sans-serif;
    }

    .form-container {
      display: flex;
      flex-wrap: nowrap; /* Prevent items from stacking */
      gap: 32px;
      justify-content: flex-start; /* Align items to the left */
      align-items: flex-start; /* Align items to the top */
      padding-left: 40px; /* Left margin for spacing from sidebar */
      margin-top: 40px; /* Top margin for spacing */
      margin-left: 100px;
    }

.form-section {
  background: #ffffff;
  padding: 28px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  width: 500px;
  min-width: 480px; /* Ensures a minimum width */
  max-width: 700px;
  min-width: 320px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  height: 400px;
}


    .form-section h2 {
      font-size: 1.25rem;
      font-weight: 600;
      color: #333;
      margin-bottom: 18px;
      text-align: center;
    }

    .input-group {
      margin-bottom: 18px;
    }

    .input-group input[type="text"] {
      width: 92%;
      padding: 10px 14px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 1rem;
      transition: border 0.2s;
    }

    .input-group input[type="text"]:focus {
      border-color: #4f8cff;
      outline: none;
    }

    .submit-btn {
      width: 100%;
      padding: 10px;
      background-color: #4f8cff;
      color: #fff;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.2s ease;
    }

    .submit-btn:hover {
      background-color: #357ae8;
    }

    .submit-btn.danger {
      background-color: #e74c3c;
    }

    .submit-btn.danger:hover {
      background-color: #c0392b;
    }

    .image-preview {
      width: 100px;
      height: 100px;
      background: #f1f1f1;
      border-radius: 8px;
      margin: 0 auto 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .image-preview img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }

.category-list-area {
  background: #ffffff;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  width: 100%;
  max-width: 340px;
  min-width: 240px; /* Optional: ensures a minimum width */
  display: flex;
  flex-direction: column;
  justify-content: top;
  height: 408px;
}

    .category-list-area h2 {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 16px;
      color: #333;
      
    }

    .category-list-scroll {
      max-height: 330px;
      overflow-y: auto;
    }

    .category-list-area ul {
      padding: 0;
      margin: 0;
      list-style: none;
    }

    .category-list-area li {
      display: flex;
      align-items: center;
      padding: 8px 0;
      border-bottom: 1px solid #ececec;
      font-size: 1rem;
      color: #444;
    }

    .category-list-area li:last-child {
      border-bottom: none;
    }

    .category-list-area input[type="checkbox"] {
      margin-right: 10px;
    }

    #addedMessage {
      text-align: center;
      color: green;
      margin-top: 10px;
      font-size: 0.95rem;
      font-weight: 500;
    }

    /* Scrollbar */
    .category-list-scroll::-webkit-scrollbar {
      width: 6px;
    }

    .category-list-scroll::-webkit-scrollbar-thumb {
      background-color: #ccc;
      border-radius: 4px;
    }

    .category-list-scroll::-webkit-scrollbar-track {
      background-color: #f1f1f1;
    }
  </style>
<body>
  
<aside class="sidebar">
    <div>
      <div class="logo-container">
        <img src="http://localhost/Library-Management-Sytem/assets/img_icon/granby_logo.png" alt="Logo" class="logo-img">
        <h3 class="logo-title">GCST Library</h3>
      </div>

      <nav class="nav-menu">
        <a href="admin_dashb.php" class="nav-link active">
          <img src="http://localhost/Library-Management-Sytem/assets/sidebar_img/dashbc.png" alt="Dashboard Icon" class="icon">
          Dashboard
        </a>
        <a href="admin_create.php" class="nav-link">
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
  
<div class="main-content">
    <div class="form-container">
        <div class="form-section">
            <div class="image-preview">
                <img id="previewImage" src="http://localhost/Library-Management-Sytem/assets/img_bg/book_details.png" alt="Book Image" />
            </div>
            <h2>Add Book Category</h2>
            <form action="http://localhost/Library-Management-Sytem/utils/book_data2.php" method="POST" enctype="multipart/form-data" id="addCategoryForm">
                <div class="input-group">
                    <input type="text" id="bookCategory" name="book_category" placeholder="Enter Book Category" required />
                </div>
                <button type="submit" class="submit-btn">Add Category</button>
            </form>
            <div id="addedMessage" style="display:none; text-align:center; color:green; margin-top:10px;">Category added!</div>
            <button id="deleteSelectedBtn" class="submit-btn" style="margin-top:12px;background:#e74c3c;">Delete Selected</button>
        </div>
        <div class="category-list-area">
            <h2>Existing Categories</h2>
            <div class="category-list-scroll">
                <ul id="categoryList">
                    <!-- Categories will be loaded here -->
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
  async function loadCategories() {
      const ul = document.getElementById('categoryList');
      ul.innerHTML = '';
      const res = await fetch('/Library-Management-Sytem/utils/categories_api.php', {
      credentials: 'same-origin'
      });
      const categories = await res.json();
      categories.forEach(cat => {
          const li = document.createElement('li');
          li.style.display = 'flex';
          li.style.alignItems = 'center';
          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.value = cat.category_id;
          checkbox.style.marginRight = '10px';
          li.appendChild(checkbox);
          const span = document.createElement('span');
          span.textContent = cat.name;
          li.appendChild(span);
          ul.appendChild(li);
      });
  }

document.getElementById('addCategoryForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const input = document.getElementById('bookCategory');
    const value = input.value.trim();
    if (value) {
        const res = await fetch('/Library-Management-Sytem/utils/categories_api.php', {
            method: 'POST', 
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({name: value})
        });
        if (res.ok) {
            loadCategories();
            const msg = document.getElementById('addedMessage');
            msg.style.display = 'block';
            setTimeout(() => { msg.style.display = 'none'; }, 1500);
        }
        input.value = '';
    }
});

// Delete selected categories
document.getElementById('deleteSelectedBtn').addEventListener('click', async function() {
    const checkboxes = document.querySelectorAll('#categoryList input[type="checkbox"]:checked');
    for (const cb of checkboxes) {
        await fetch('/Library-Management-Sytem/utils/delete_category.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({category_id: cb.value})
        });
    }
    loadCategories();
});

loadCategories();
</script>

  <script>
    const fileInput = document.getElementById('bookImageInput');
    const previewImage = document.getElementById('previewImage');
    const defaultImage = '/assets/img_bg/book_details.png'; // Default image path

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

</body>
</html>
