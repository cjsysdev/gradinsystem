<!DOCTYPE html>
<html>

<head>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>

<body>
    <div class="container-fluid text-center pt-6">
        <h3>Student Login</h3>

        <form action="login" method="post">
            <div class="container">
                <input class="col-3" type="text" id="username" class="fadeIn second" name="username" placeholder="username">
                <input class="col-3" type="password" id="password" class="fadeIn third" name="password" placeholder="password">
            </div>
            <input class="row-4" type="submit" class="fadeIn fourth" value="Log In">
        </form>

        <div id="formFooter">
            <a class="underlineHover" href="#">Create Account</a>
        </div>
    </div>


</body>

</html>