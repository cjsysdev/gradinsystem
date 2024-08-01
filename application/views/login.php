<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Form</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .login-form {
      width: 100%;
      max-width: 380px;
      margin: 50px auto;
      padding: 15px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .login-form .form-control {
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="login-form">
      <form>
        <h2 class="text-center">Sign In</h2>
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Username" required>
        </div>
        <div class="form-group">
          <input type="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="form-group">
          <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
