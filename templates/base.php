<?php require 'ti.php' ?>
<?php require '../../libConfig.php' ?>
<?php
try {
    $con = new PDO("mysql:dbname=$db;host=$host", $username, $password);
} catch(PDOException $e) {
    echo $e->getMessage();
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
            <?php startblock('header') ?>
            <?php endblock() ?>
            <a id='logout' href=''>Log out</a>
        </div>

        <div class='container'>
            <div class='menu'>
                <ul>
                    <a href='search.php'><li> Search </li> </a>
                    <a href='personal.php'><li> Personal Page </li> </a>
                    <a href='notifications.php'><li> Notifications </li> </a>
                    <a href=''><li> Settings </li> </a>
                </ul>
            </div>

            <div class='content'>
                <?php startblock('content') ?>
                <?php endblock() ?>
            </div>
        </div>
    </body>
</html>
