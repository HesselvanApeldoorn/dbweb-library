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
    </head>
    <body>
        <div class='header'>
            <?php startblock('header') ?>
            <?php endblock() ?>
            <a href=''>Log out</a>
        </div>

        <div class='container'>
            <div class='menu'>
                <ul>
                    <a href='../search.php'><li> Search </li> </a>
                    <a href=''><li> Personal library </li> </a>
                    <a href=''><li> Notifications </li> </a>
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