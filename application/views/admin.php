<?php $this->load->view('header') ?>

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


<?php $this->load->view('footer') ?>

