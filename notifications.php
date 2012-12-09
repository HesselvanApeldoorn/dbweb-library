<?php require 'templates/base.php';
			error_reporting(-1);
            ini_set("display_errors", 1); ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php startblock('header');
        echo "Welcome, Cagri, you are here: <a href='index.php'>Home</a> &raquo; Notifications";
endblock() ?>
<?php startblock('content');
    echo "<div class='main'>";
        echo "<div class='blockHeader'><h2>Notifications</h2></div><br>";
        echo "<div class='blockContent'>";
        $sql = "select * from Notification";
        $query = $con->prepare($sql);
        $query->execute();
        echo "<div class='blockContent'>";
            foreach($query as $notification) {
                echo "<div class='notification'>";
                echo "<strong>Notification:</strong><br>";
                echo $notification['message'];
                echo "</div>";
            }
        echo "</div>";
    echo "</div>";
endblock() ?>
