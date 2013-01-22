<?php require 'ti.php' ?>
<?php require '../../libConfig.php' ;
              error_reporting(-1);
            ini_set("display_errors", 1);
?>
<?php
session_start();
if (isset($_SESSION['LAST_ACTIVITY']) && (time()-$_SESSION['LAST_ACTIVITY'] > 604800)) { #week of inactivity
    session_unset();     // unset $_SESSION variable for the runtime 
    session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp 
if  (!isset($_SESSION["email"])) {
    header("location:login.php");
}
try {
    $con = new PDO("mysql:dbname=$db;host=$host", $username, $password);
} catch(PDOException $e) {
        echo "Could not connect to database.";
        die();
}
?>
<html>
    <head>
         <link href="../static/css/base.css" rel="stylesheet" type="text/css">
        <?php startblock('scripts') ?>
        <?php endblock() ?>
    </head>
    <body>
        <div class='header'>
        <?php
            $sql = "select user_name from User where email = ?";
            $q = $con->prepare($sql);
            $q->execute(array($_SESSION['email']));
            $name_of_user =  $q->fetchColumn();
            echo "Welcome, $name_of_user, you are here: "; ?>
            <?php startblock('header') ?>
            <?php endblock() ?>
            <a id='logout' href='logout.php'>Log out</a>
        </div>
        <div class='confirm'>
            <?php if (isset($_SESSION['confirm'])) {
                echo $_SESSION['confirm'];
                unset($_SESSION['confirm']);
            } ?>
        </div>

        <div class='container'>
            <div class='menu'>
                <ul>
                    <a href='search.php'><li> Search </li> </a>
                    <a href='personal.php'><li> Personal Page </li> </a>
                    <a href='notifications.php'><li> Notifications </li> </a>
                    <a href='settings.php'><li> Settings </li> </a>
                </ul>
            </div>

            <div class='content'>
                <?php startblock('content') ?>
                <?php endblock() ?>
            </div>
        </div>
    </body>
</html>
