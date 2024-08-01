<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .dashboard {
      max-width: 400px;
      margin: 20px auto;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      background-color: #ffffff;
    }
    .profile-section {
      text-align: center;
      margin-bottom: 20px;
    }
    .profile-section img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      margin-bottom: 10px;
    }
    .profile-section h5, .profile-section p {
      margin: 0;
    }
    .nav-btns, .category-btns, .total-section {
      margin-bottom: 15px;
    }
    .btn-custom {
      width: 100%;
      margin: 5px 0;
      border-radius: 5px;
    }
    .total-section .btn-total {
      width: 100%;
      border-radius: 5px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="dashboard">
      <div class="profile-section">
        <img src="..\assets\user.png" alt="Profile Picture">
        <h5><strong>John Colson</strong></h5>
        <p>BSIS - 1</p>
      </div>
      <div class="nav-btns">
        <button class="btn btn-outline-secondary btn-custom">Dashboard</button>
        <button class="btn btn-secondary btn-custom">Overview</button>
      </div>
      <div class="category-btns row">
        <div class="col-6">
          <button class="btn btn-outline-secondary btn-custom">Activities<br>10%</button>
        </div>
        <div class="col-6">
          <button class="btn btn-outline-secondary btn-custom">PT<br>40%</button>
        </div>
        <div class="col-6">
          <button class="btn btn-outline-secondary btn-custom">Major Exam<br>30%</button>
        </div>
        <div class="col-6">
          <button class="btn btn-outline-secondary btn-custom">Quizzes<br>20%</button>
        </div>
      </div>
      <div class="total-section">
        <button class="btn btn-secondary btn-total">Total</button>
      </div>
      <div class="text-center">
        <button class="btn btn-outline-secondary">Logout</button>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
