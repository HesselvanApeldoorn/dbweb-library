<?php require 'templates/base.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php startblock('header');
        echo "Home";
endblock() ?>
<?php startblock('content');
    echo "<div class='library'>";
            echo "<div class='search_bar'>";
                echo "Search:<input type='text' name='dff_keyword' size='30' maxlength='50'>";
                echo "<input type='submit' value='Find'>";
            echo "</div>";
            echo "<div class='personal_library'>";
                echo "<table border='1'>"; 
                    echo "<th> Name</th>";
                    echo "<th> Author</th>";
                    $query = $con->prepare('select * from Document');
                    $query->execute();
                    foreach($query as $document) {
                        echo "<tr><td>{$document['document_name']}</td>";
                        echo "<td>{$document['author']}</td></tr>";
                    }
                echo "</table>";
            echo "</div>";
            echo "<div class='loaning'>";
            echo "</div>";
    echo "</div>";
    echo "<div class='notifications'>";
        echo "<div class='notifyHeader'> <h2>Notifications</h2></div>";
        $query = $con->prepare('select * from Notification');
        $query->execute();
        echo "<div class='notifyContent'>";
            foreach($query as $notification) {
                echo "<div class='notification'>";
                echo "<strong>Notification:</strong><br>";
                echo $notification['message'];
                echo "</div>";
            }
        echo "</div>";
    echo "</div>";

endblock() ?>