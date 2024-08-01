<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body {
      display: flex;
    }
    .sidebar {
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      width: 250px;
      background-color: #343a40;
      padding-top: 20px;
      transition: all 0.3s;
    }
    .sidebar.collapsed {
      width: 0;
      overflow: hidden;
    }
    .sidebar a {
      padding: 10px 15px;
      text-decoration: none;
      font-size: 18px;
      color: #ffffff;
      display: block;
    }
    .sidebar a:hover {
      background-color: #007bff;
      color: #ffffff;
    }
    .content {
      margin-left: 250px;
      padding: 20px;
      transition: margin-left 0.3s;
      flex-grow: 1;
    }
    .content.collapsed {
      margin-left: 0;
    }
    .navbar-custom {
      background-color: #007bff;
      transition: margin-left 0.3s;
    }
  </style>
</head>
<body>
  <div class="sidebar" id="sidebar">
    <h4 class="text-center text-light">Admin Panel</h4>
    <a href="#dashboard">Dashboard</a>
    <a href="#users">Users</a>
    <a href="#settings">Settings</a>
    <a href="#reports">Reports</a>
    <a href="#logout">Logout</a>
  </div>

  <div class="content" id="content">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
      <div class="container-fluid">
        <button class="navbar-toggler" type="button" id="sidebarToggle" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="#">Admin</a>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item">
              <a class="nav-link" href="#">Profile</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Settings</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid">
      <h2>Dashboard</h2>
      <p>Welcome to the admin panel. Use the sidebar to navigate through the different sections.</p>
      <!-- Add more admin panel content here -->
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#sidebarToggle').on('click', function() {
        $('#sidebar').toggleClass('collapsed');
        $('#content').toggleClass('collapsed');
      });
    });
  </script>
</body>
</html>
