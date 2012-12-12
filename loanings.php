<?php require 'templates/base.php';
			error_reporting(-1);
            ini_set("display_errors", 1); ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php startblock('header');
        echo "Welcome, Cagri, you are here: <a href='index.php'>Home</a> &raquo; <a href='personal.php'>Personal Page</a> &raquo; Loanings";
endblock() ?>
<?php startblock('content');
    echo "<div class='main'>";
        echo "<div class='blockHeader'><h2>Loanings</h2></div>";
        echo "<div class='blockContent'>";
            echo "<h3>Books you lent out<hr/></h3>";
            $query = $con->prepare('select * from Loaning');
            $query->execute();
            if($query->rowCount()>0) {
                echo "<table>";
                    echo "<th>Book</th>";
                    echo "<th>Lent from</th>";
                    echo "<th>Start date</th>";
                    echo "<th>end date</th>";

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
                        echo "<tr id='$idvar'><td><a href='book.php?book={$loaning['docID']}'>{$docName['document_name']}</a></td>";
                        echo "<td>{$loaning['fromUser']}</td>";
                        echo "<td>{$loaning['start_date']}</td>";
                        echo "<td>{$loaning['end_date']}</td></tr>";
                    }
                echo "</table>";
            } else { // empty table Loaning
                echo "There are no loanings currently.";
            }
            echo "<form>";
                echo "<input type='submit' value='Lent a book' />";
            echo "</form";
            echo "<h3>Books you borrowed<hr/></h3>";
            $query = $con->prepare('select * from Loaning');
            $query->execute();
            if($query->rowCount()>0) {
                echo "<table>";
                    echo "<th>Book</th>";
                    echo "<th>Lent from</th>";
                    echo "<th>Start date</th>";
                    echo "<th>end date</th>";

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
                        echo "<tr id='$idvar'><td><a href='book.php?book={$loaning['docID']}'>{$docName['document_name']}</a></td>";
                        echo "<td>{$loaning['fromUser']}</td>";
                        echo "<td>{$loaning['start_date']}</td>";
                        echo "<td>{$loaning['end_date']}</td></tr>";
                    }
                echo "</table>"; 
            } else {
                echo "There are no loanings currently.";
            }
            echo "<form action='borrowDocument.php'>";
                echo "<input type='submit' value='Borrow a book' />";
            echo "</form";
        echo "</div>";
    echo "</div>";
endblock() ?>