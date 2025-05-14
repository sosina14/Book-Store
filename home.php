<?php
session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.html");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Bookstore Login</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css"/>
</head>
<body>
  <div class="login-container d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div class="card shadow-lg p-4" style="width: 100%; max-width: 500px;">
      <h2 class="text-center mb-4 text-primary">Welcome to Bookstore <br> እንኳን ወደ መጽሐፍ መደብር በሰላም መጡ</h2>
      <form id="loginForm">
        <div class="mb-3">
          <label for="name" class="form-label">Full Name -> ሙሉ ስም</label>
          <input type="text" id="name" class="form-control" placeholder="Your name" required />
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email address -> ሜል አድራሻ</label>
          <input type="email" id="email" class="form-control" placeholder="example@mail.com" required />
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password -> የይለፍ ቃልኢ</label>
          <input type="password" id="password" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="role" class="form-label">Select Role -> ሚና ይምረጡ</label>
          <select id="role" class="form-select" required>
            <option value="">Choose a role...</option>
            <option value="admin">Admin [አስተዳዳሪ]</option>
            <option value="author">Author[ደራሲ]</option>
            <option value="customer">Customer[ደንበኛ]</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary w-100 mt-3">
          <i class="fas fa-sign-in-alt me-1"></i> Enter Bookstore
        </button>
      </form>
    </div>
  </div>

  <script>
    document.getElementById('loginForm').addEventListener('submit', function (e) {
      e.preventDefault();

      const name = document.getElementById('name').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const role = document.getElementById('role').value;

      if (name && email && password && role) {
        // Redirect to the Bookstore page
        window.location.href = "index.html";
      } else {
        alert("Please fill in all fields correctly.");
      }
    });
  </script>
</body>
</html>

