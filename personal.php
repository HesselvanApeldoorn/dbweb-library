<?php require 'templates/base.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php startblock('header');
        echo "Welcome, Cagri, you are here: <a href='index.php'>Home</a> &raquo; Personal page";
endblock() ?>
<?php startblock('content');
  echo "<div class='main'>";
        echo "<div class='blockHeader'> <h2>Personal Page</h2></div>";
        echo "<div class='blockContent'>";
            echo "<h3><a href='personalLibrary.php'>Personal library</a><hr/></h3>";
            $query = $con->prepare("select * from Document limit 10");
            $query2 = $con->prepare("select count(*) from Document");
            $query->execute();
            $query2->execute();
            if($query->rowCount()>0) {
                echo "<table>";
                    echo "<th> Name</th>";
                    echo "<th> Author</th>";
                    echo "<th> Description </th>";
                    echo "<th> ISBN(optional)</th>";
                    if($query2->fetchColumn()>10) {
                        echo "<tfoot align='right'> 
                                <tr >
                                    <td colspan='4'> <a href='personalLibrary.php'>See more...</a></td>
                                </tr>
                            </tfoot>";
                    }
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
                        $desc = "<td>".substr($document['description'],0,20);
                        if(strlen($document['description'])>20) {
                            $desc = $desc . "...";
                        }
                        echo $desc ."</td>";
                        echo "<td>{$document['isbn']}</td>";
                        echo "</tr>";
                    }
                echo "</table>";
            } else { //no documents
                echo "There are no documents currently.";
            }
            echo "<h3><a href='loanings.php'>Books you lent out</a><hr/></h3>";
            $query = $con->prepare("select * from Loaning limit 5"); //TODO where user=?
            $query2 = $con->prepare("select count(*) from Loaning"); //TODO where user=?
            $query->execute();
            $query2->execute();
            if($query->rowCount()>0) {
                echo "<table>";
                    echo "<th>Book</th>";
                    echo "<th>Lent from</th>";
                    echo "<th>Start date</th>";
                    echo "<th>end date</th>";
                    if($query2->fetchColumn()>5) {
                        echo "<tfoot align='right'> 
                                <tr>
                                    <td colspan='4'> <a href='loanings.php'>See more...</a></td>
                                </tr>
                            </tfoot>";
                    }

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
                        echo "<tr id='$idvar' ><td><a href='book.php?book={$document['docID']}'>{$docName['document_name']}</a></td>";
                        echo "<td>{$loaning['fromUser']}</td>";
                        echo "<td>{$loaning['start_date']}</td>";
                        echo "<td>{$loaning['end_date']}</td></tr>";
                    }
                echo "</table>";
            } else { //no loanings
                echo "There are no loanings currently.";
            }

            echo "<h3><a href='loanings.php'>Books you borrowed</a><hr/></h3>";
            $query = $con->prepare("select * from Loaning  limit 5"); //TODO where user=?
            $query2 = $con->prepare("select count(*) from Loaning"); //TODO where user=?
            $query->execute();
            $query2->execute();
            if($query->rowCount()>0) {
                echo "<table>";
                    echo "<th>Book</th>";
                    echo "<th>Lent from</th>";
                    echo "<th>Start date</th>";
                    echo "<th>end date</th>";
                    if($query2->fetchColumn()>5) {
                        echo "<tfoot align='right'> 
                                <tr >
                                    <td colspan='4'> <a href='loanings.php'>See more...</a></td>
                                </tr>
                            </tfoot>";
                    }

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
                        echo "<tr id='$idvar' ><td><a href='book.php?book={$loaning['docID']}'>{$docName['document_name']}</a></td>";
                        echo "<td>{$loaning['fromUser']}</td>";
                        echo "<td>{$loaning['start_date']}</td>";
                        echo "<td>{$loaning['end_date']}</td></tr>";
                    }
                echo "</table>";
            } else {//no loanings
                echo "There are no loanings currently.";
            }
        echo "</div>";
    echo "</div>";

endblock() ?>
