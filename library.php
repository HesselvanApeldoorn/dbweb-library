<?php require 'templates/base.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php startblock('header');
        echo "Welcome, Cagri, you are here: <a href='index.php'>Home</a> &raquo; library";
endblock() ?>
<?php startblock('content');
  echo "<div class='main'>";
        echo "<div class='blockHeader'> <h2>Library</h2></div>";
        echo "<div class='blockContent'>";
            echo "<div class='personal_library'>";
                echo "<h4>Personal library</h4>";
                echo "<table>";
                    echo "<th> Name</th>";
                    echo "<th> Author</th>";
                    echo "<th> Description </th>";
                    echo "<th> ISBN(optional)</th>";
                    $query = $con->prepare('select * from Document');
                    $query->execute();
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
            echo "</div>";
            echo "<div class='loaning'>";
                echo "<hr/><h4>Books you lent out</h4>";
                echo "<table>";
                    echo "<th>Book</th>";
                    echo "<th>Lent from</th>";
                    echo "<th>Start date</th>";
                    echo "<th>end date</th>";
                    $query = $con->prepare('select * from Loaning');
                    $query->execute();
                    $i=0;
                    foreach($query as $loaning) {
                        $i = $i+1;
                        $i=$i%2;
                        $idvar = 'even';
                        if ($i==0) {
                            $idvar='odd';
                        }
                        $query2 = $con->prepare("select * from Document where docID={$loaning['docID']}");
                        $query2->execute();
                        $docName = $query2->fetch();
                        echo "<tr id='$idvar'><td>{$docName['document_name']}</td>";
                        echo "<td>{$loaning['fromUser']}</td>";
                        echo "<td>{$loaning['start_date']}</td>";
                        echo "<td>{$loaning['end_date']}</td></tr>";
                    }
                echo "</table>";

                echo "<hr/><h4>Books you borrowed</h4>";
                echo "<table>";
                    echo "<th>Book</th>";
                    echo "<th>Lent from</th>";
                    echo "<th>Start date</th>";
                    echo "<th>end date</th>";
                    $query = $con->prepare('select * from Loaning');
                    $query->execute();
                    $i=0;
                    foreach($query as $loaning) {
                        $i = $i+1;
                        $i=$i%2;
                        $idvar = 'even';
                        if ($i==0) {
                            $idvar='odd';
                        }
                        $query2 = $con->prepare("select * from Document where docID={$loaning['docID']}");
                        $query2->execute();
                        $docName = $query2->fetch();
                        echo "<tr id='$idvar'><td>{$docName['document_name']}</td>";
                        echo "<td>{$loaning['fromUser']}</td>";
                        echo "<td>{$loaning['start_date']}</td>";
                        echo "<td>{$loaning['end_date']}</td></tr>";
                    }
                echo "</table>";
            echo "</div>";
            echo "<hr/><form>";
                echo "<input type='submit' value='Upload new Document' />";
            echo "</form";
        echo "</div>";
    echo "</div>";

endblock() ?>
