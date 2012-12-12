<?php require 'templates/base.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php startblock('header');
        echo "Welcome, Cagri, you are here: <a href='index.php'>Home</a> &raquo; <a href='personal.php'>Personal Page</a> &raquo; Personal Library";
endblock() ?>
<?php startblock('content');
  echo "<div class='main'>";
        echo "<div class='blockHeader'> <h2>Personal Library</h2></div>";
        echo "<div class='blockContent'>";
            $query = $con->prepare("select * from Document");
            $query->execute();
            if($query->rowCount()>0) {
                echo "<table>";
                    echo "<th> Name</th>";
                    echo "<th> Author</th>";
                    echo "<th> Description </th>";
                    echo "<th> ISBN(optional)</th>";
                    $i=0;
                    foreach($query as $document) {
                        $i = $i+1;
                        $i=$i%2;
                        $idvar = 'even';
                        if ($i==0) {
                            $idvar='odd';
                        }
                        echo "<tr id='$idvar' ><td><a href='book.php?book={$document['docID']}'>{$document['document_name']}</a></td>";
                        echo "<td>{$document['author']}</td>";
                        echo "<td>{$document['description']}</td>";
                        echo "<td>{$document['isbn']}</td>";
                        echo "</tr>";
                    }
                echo "</table>";
            } else { //No documents
                echo "There are no documents currently.";
            }
            echo "<hr/><form>";
                echo "<input type='submit' value='Upload new Document' />";
            echo "</form";

        echo "</div>";
    echo "</div>";

endblock() ?>
