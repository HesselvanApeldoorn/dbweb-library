<html>
    <head>
        <?php
            error_reporting(-1);
            ini_set("display_errors", 1);
            session_start();
            require_once("../../libConfig.php"); //$db=databaseName, $host=hostName, $user=userName, $pass=password.
            if(!isset($_SESSION['confirmed'])) {
                header("location:login.php");
            }
        ?>
        <link href="static/css/base.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?php 
            if($_SESSION['confirmed']==false && $_SESSION['regEmail']==$_GET['email'] && $_SESSION['confirm_code']==$_GET['confirm_code']) {
                $_SESSION['confirmed'] = true;
                echo "Your account has been activated succesfully! <a href=login.php>Login</a>";
            } else {
                echo "You've already confirmed your account";
            }
        ?>
    </body>
</html>
