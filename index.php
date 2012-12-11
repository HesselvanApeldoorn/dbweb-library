<?php require 'templates/base.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php startblock('header');
        echo "Welcome, Cagri, you are here: Home";
endblock() ?>
<?php startblock('content');
    echo "<div class='library'>";
        echo "<div class='blockHeader'> <h2>Library</h2></div><br>";
        echo "<div class='blockContent'>";
            echo "<div class='search_bar'>";
                echo "<form method='post' action= 'search.php?'>";
                echo "<input type='text' class='search_bar' name='searchText' value='Search for books' onfocus='if(this.value == \"Search for books\") { this.value = \"\"; }' onblur='if(this.value == \"\") { this.value = \"Search for books\"; }'>";
                echo "<input type='submit' name='searchButton' value='Find'>";
                echo "</form>";
            echo "</div>";
            echo "<div class='personal_library'>";
                echo "<hr/><h4>Personal library</h4>";
                echo "<table border='1'>";
                    echo "<th> Name</th>";
                    echo "<th> Author</th>";
                    $query = $con->prepare('select * from Document');
                    $query->execute();
                    foreach($query as $document) {
                        echo "<tr id='$idvar' ><td><a href='book.php?book={$document['docID']}'>{$document['document_name']}</a></td>";
                        echo "<td>{$document['author']}</td></tr>";
                    }
                echo "</table>";
            echo "</div>";
            echo "<div class='loaning'>";
                echo "<hr/><h4>Loaning</h4>";
                echo "<table border='1'>";
                    echo "<th>Book</th>";
                    echo "<th>Lent from</th>";
                    echo "<th>Start date</th>";
                    echo "<th>end date</th>";
                    $query = $con->prepare('select * from Loaning');
                    $query->execute();
                    foreach($query as $loaning) {
                        $query2 = $con->prepare("select * from Document where docID={$loaning['docID']}");
                        $query2->execute();
                        $docName = $query2->fetch();
                        echo "<tr id='$idvar' ><td><a href='book.php?book={$document['docID']}'>{$document['document_name']}</a></td>";
                        echo "<td>".substr($loaning['fromUser'],0,10)."...</td>";
                        echo "<td>{$loaning['start_date']}</td>";
                        echo "<td>{$loaning['end_date']}</td></tr>";
                    }
                echo "</table>";
            echo "</div>";
        echo "</div>";
    echo "</div>";
    echo "<div class='notifications'>";
        echo "<div class='blockHeader'> <h2>Notifications</h2></div>";
        $query = $con->prepare('select * from Notification');
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
