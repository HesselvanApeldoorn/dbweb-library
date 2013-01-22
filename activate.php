<?php
    require '../../libConfig.php';
    session_start();
    try {
        $con = new PDO("mysql:dbname=$db;host=$host", $username, $password);
    } catch(PDOException $e) {
        echo "Could not connect to database.";
        die();
    }
    if (isset($_SESSION['confirm_time']) && (time()-$_SESSION['confirm_time'] > 3600)) { #hour of inactivity
        $sql = "delete from User where email=?";
        $query =$con->prepare($sql);
        $query->execute(array($_SESSION['regEmail']));
        session_unset();     // unset $_SESSION variable for the runtime 
        session_destroy();   // destroy session data in storage

    }
    require_once("../../libConfig.php"); //$db=databaseName, $host=hostName, $user=userName, $pass=password.
    if(!isset($_SESSION['confirmed'])) {
        header("location:login.php");
    }
?>
<html>
    <head>
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
