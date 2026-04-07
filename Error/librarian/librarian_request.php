<?php
session_start();

// If not logged in OR not librarian, redirect
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
  <title>Requests - Librarian</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="http://localhost/GCST_Track_System/styles/admin_css/librarian_navbar.css" />
 
  <style>
      html{
      height: 100%;
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f0f2f5;
      overflow: auto; 
    }

body {
  margin: 0;
  display: flex;
  height: 100vh;
  background: linear-gradient(to bottom, #334155, #0f172a);
  color: #000000;
  font-family: 'Poppins', sans-serif;
  overflow-y: auto; 
}

.logo-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 2.5rem;
}

.nav-menu {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  color: #1f2937;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.2s;
}

.nav-link:hover {
  background-color: #dbeafe;
  color: #1d4ed8;
}

.nav-link.active {
  background-color: #bfdbfe;
  color: #1e3a8a;
  font-weight: 600;
  border-left: 4px solid #2563eb;
}

.nav-link.logout {
  color: #dc2626;
  font-weight: 600;
}

.nav-link.logout:hover {
  background-color: #fee2e2;
  color: #b91c1c;
}

.icon {
  width: 1.25rem;
  height: 1.25rem;
}


.main-content {
  flex: 1;
  overflow-y: hidden;
  padding-top: 1rem;
  padding-right: 2rem;
  padding-bottom: 2rem;
  padding-left: 2rem;
  margin-left: 15rem;
  height: 100vh;
  display: flex;
  overflow-x: hidden;
  margin-bottom: 30px;
}
    
.content-box {

     margin-top: 10px;
     margin-right: 30px;
     margin-bottom: 80px;
     margin-left: 26px;
      padding: 30px;
      
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      min-height: calc(100vh - 40px);
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      width: 93%;
      height: fit-content;

}
    table {
      width: 1000px;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background-color: #e1e8eb;
    }
    input[type="number"] {
      width: 60px;
      padding: 4px;
    }
    select {
      padding: 5px;
      margin-left: 5px;
    }
    button {
      padding: 6px 12px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    button:hover {
      background-color: #0056b3;
    }
    /* Delete button */
    .delete-btn {
  background-color: #dc3545;
  color: white;
  padding: 6px 10px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.delete-btn:hover {
  background-color: #a71d2a;
}

  </style>
</head>
<body>

<!-- Sidebar-->
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

      <a href="librarian_issued_books.html" class="nav-link {{issued}}">
        <img src="http://localhost/GCST_Track_System/assets/adb_icon/nonheart.png" class="icon">
        Issued Books
      </a>

      <a href="librarian_non-returned.html" class="nav-link {{nonreturned}}">
        <img src="http://localhost/GCST_Track_System/assets/adb_icon/nonret.png" class="icon">
        Non-Returned
      </a>

      <a href="librarian_request.html" class="nav-link active">
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

  <div class="main-content">
    <div class="content-box">
      <h2>Book Request Management</h2>
      <table>
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Book Requested</th>
            <th>Borrow Duration</th>
            <th>Ready for Pickup</th>
            <th>Received</th>
            <th>Reject</th>

          </tr>
        </thead>
      </table>
    </div>
  </div>

<script>
window.addEventListener('DOMContentLoaded', () => {
  function loadRequests() {
    fetch('/GCST_Track_System/utils/utils/get_all_requests.php', {
    credentials: 'same-origin'
    })

    .then(res => res.json())
    .then(data => {
       const tbody = document.querySelector('table tbody');
       tbody.innerHTML = '';
       // Show only pending and ready to pick up requests for admin action
       data.filter(req => req.status === 'pending' || req.status === 'ready to pick up').forEach(req => {
       tbody.innerHTML += `
      <tr>
        <td>${req.student_id}</td>
        <td>${req.name}</td>
        <td>${req.book_name}</td>
        <td>
          <input type="number" min="1" value="7">
          <select>
            <option>Days</option>
            <option>Hours</option>
            <option>Weeks</option>
          </select>
        </td>
        <td>
          ${req.status === 'pending' ? `<button class="ready-btn" data-id="${req.request_id}">Mark as Ready</button>` : ''}
        </td>
        <td>
          ${req.status === 'ready to pick up' ? `<button class="approve-btn" data-id="${req.request_id}">Mark as Received</button>` : ''}
        </td>
        <td><button class="delete-btn" data-id="${req.request_id}">✖</button></td>
      </tr>
    `;
  });

  // Helper to fade out and remove a row
  function fadeOutRow(btn) {
    const row = btn.closest('tr');
    row.style.transition = 'opacity 0.5s';
    row.style.opacity = 0;
    setTimeout(() => row.remove(), 500);
  }

  // Mark as Ready (no fade out, just update status)
document.querySelectorAll('.ready-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    fetch('http://localhost/GCST_Track_System/utils/update_request_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ request_id: btn.dataset.id, status: 'ready to pick up' })
    }).then(() => {
      loadRequests(); // <--- refresh table after marking as ready
      btn.disabled = true;
      btn.textContent = 'Marked as Ready';
    });
  });
});

// Mark as Received (no fade out, just update status)
document.querySelectorAll('.approve-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const row = btn.closest('tr');
    const durationInput = row.querySelector('input[type="number"]');
    const unitSelect = row.querySelector('select');
    const duration = parseInt(durationInput.value, 10);
    const unit = unitSelect.value;

    fetch('http://localhost/GCST_Track_System/utils/mark_as_received.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        request_id: btn.dataset.id,
        duration,
        unit
      })
    })
    .then(res => res.json())
    .then(res => {
      if (res.success) {
        loadRequests(); // <--- refresh table after marking as received
        btn.disabled = true;
        btn.textContent = 'Received';
      } else {
        alert('Failed to mark as received: ' + (res.message || 'Unknown error.'));
      }
    });
  });
});

// Delete (Reject) (no fade out, just update status)
document.querySelectorAll('.delete-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    fetch('http://localhost/GCST_Track_System/utils/update_request_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ request_id: btn.dataset.id, status: 'rejected' })
    }).then(() => {
      loadRequests(); // <--- refresh table after rejecting
      btn.disabled = true;
      btn.textContent = 'Rejected';
    });
  });
});

});
}

  loadRequests(); // Initial load of requests
})
</script>

<!--Logout Confirmation-->
<script>
function confirmLogout(event) {
    event.preventDefault();

    const confirmAction = confirm("Are you sure you want to logout?");

    if (confirmAction) {
        // Clear session storage if used
        localStorage.clear();

        // Redirect to logout handler or login page
        window.location.href = "http://localhost/GCST_Track_System/pages/sign_in_admin_librarian.html";
    }
}
</script>

</body>
</html>
