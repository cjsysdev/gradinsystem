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

        /*
     * --------------------------------------------
     * Login Form CSS
     * --------------------------------------------
     */

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

        /*
     * --------------------------------------------
     * Dashboard
     * --------------------------------------------
     */

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

        .profile-section h5,
        .profile-section p {
            margin: 0;
        }

        .nav-btns,
        .category-btns,
        .total-section {
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

        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>

<body>