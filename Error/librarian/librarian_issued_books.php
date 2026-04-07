<?php
session_start();

// Block access if not logged in OR not librarian
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'librarian') {
    header("Location: http://localhost/GCST_Track_System/pages/sign_in_admin_librarian.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Issued Books - Librarian</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="http://localhost/GCST_Track_System/styles/admin_css/admin_navbar.css">
<link rel="stylesheet" href="http://localhost/GCST_Track_System/styles/admin_dashboard.css">

<style>
html, body {
    margin: 0;
    padding: 0;
    height: 100vh;
    overflow: hidden;
    background: linear-gradient(to bottom, #334155, #0f172a);
    font-family: 'Poppins', sans-serif;
}

.main-content {
    flex: 1;
    padding: 30px;
}

.content-box {
    background: #ffffff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    margin-left: 40px;
    width: 1100px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background: #f0f0f0;
}
</style>
</head>

<body>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">
  <div>
    <div class="logo-container">
      <img src="http://localhost/GCST_Track_System/assets/img_icon/granby_logo.png" class="logo-img">
      <h3 class="logo-title">GCST Library</h3>
    </div>

    <nav class="nav-menu">
      <a href="librarian_dashb.html" class="nav-link {{dashboard}}">
        <img src="http://localhost/GCST_Track_System/assets/sidebar_img/dashbc.png" class="icon">
        Dashboard
      </a>

      <a href="librarian_issued_books.html" class="nav-link active">
        <img src="http://localhost/GCST_Track_System/assets/adb_icon/nonheart.png" class="icon">
        Issued Books
      </a>

      <a href="librarian_non-returned.html" class="nav-link {{nonreturned}}">
        <img src="http://localhost/GCST_Track_System/assets/adb_icon/nonret.png" class="icon">
        Non-Returned
      </a>

      <a href="librarian_request.html" class="nav-link {{requests}}">
        <img src="http://localhost/GCST_Track_System/assets/adb_icon/pointbk.png" class="icon">
        Requests
      </a>

      <a href="#" class="nav-link logout" onclick="confirmLogout(event)">
        <img src="http://localhost/GCST_Track_System/assets/sidebar_img/outv.png" class="icon">
        Logout
      </a>
    </nav>
  </div>
</aside>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">
  <div class="content-box">
    <h1>Issued Books</h1>

    <table>
      <thead>
        <tr>
          <th>Issued No.</th>
          <th>Book Name</th>
          <th>Category</th>
          <th>Student Name</th>
          <th>Issue Date</th>
          <th>Returned Date</th>
        </tr>
      </thead>
      <tbody id="issued-tbody">
        <tr>
          <td colspan="6">Loading...</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- ===== LOAD DATA ===== -->
<script>
function loadIssuedBooks() {
    fetch('/GCST_Track_System/utils/issued_books.php', {
    credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
      const tbody = document.getElementById('issued-tbody');
      tbody.innerHTML = '';

      if (Array.isArray(data) && data.length > 0) {
        data.forEach(row => {
          tbody.innerHTML += `
            <tr>
              <td>${row.issued_id}</td>
              <td>${row.book_name}</td>
              <td>${row.book_category}</td>
              <td>${row.student_name}</td>
              <td>${row.date_student_received_book}</td>
              <td>${row.book_returned_date ?? '-'}</td>
            </tr>
          `;
        });
      } else {
        tbody.innerHTML = `<tr><td colspan="6">No issued books found.</td></tr>`;
      }
    });
}
window.addEventListener('DOMContentLoaded', loadIssuedBooks);
</script>

<!-- ===== LOGOUT CONFIRM ===== -->
<a href="/GCST_Track_System/logout.php"
   class="nav-link logout"
   onclick="return confirm('Are you sure you want to logout?')">

</body>
</html>
