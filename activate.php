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
            if($_SESSION['confirmed']==false && $_SESSION['regEmail']==$_REQUEST['email'] && $_SESSION['confirm_code']==$_REQUEST['confirm_code']) {
                $_SESSION['confirmed'] = true;
                header("Location: login.php?activate=success&email={$_REQUEST['email']}");
            } else {
                header("Location: login.php?activate=already_confirmed&email={$_REQUEST['email']}");
            }
        ?>
    </body>
</html>
