<?php require 'templates/base.php';
			error_reporting(-1);
            ini_set("display_errors", 1); ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php startblock('header');
        echo "<a href='index.php'>Home</a> &raquo; <a href='personal.php'>Personal Page</a> &raquo; Loanings";
endblock() ?>
<?php startblock('content');
    echo "<div class='main'>";
        echo "<div class='blockHeader'><h2>Loanings</h2></div>";
        echo "<div class='blockContent'>";
            echo "<h3>Books you lent<hr/></h3>";
            $query = $con->prepare("select * from Loaning,User where User.email=Loaning.toUser and Loaning.fromUser='{$_SESSION['email']}' limit 5"); //TODO where user=?
            $query->execute();
            if($query->rowCount()>0) {
                echo "<table>";
                    echo "<th>Book</th>";
                    echo "<th>Lent to</th>";
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
                        echo "<td>{$loaning['user_name']}</td>";
                        echo "<td>{$loaning['start_date']}</td>";
                        echo "<td>{$loaning['end_date']}</td></tr>";
                    }
                echo "</table>";
            } else { // empty table Loaning
                echo "There are no loanings currently.";
            }
            echo "<form action='lendDocument.php'>";
                echo "<input type='submit' value='Lend a book' />";
            echo "</form>";
            echo "<h3>Books you borrowed<hr/></h3>";
            $query = $con->prepare("select * from Loaning,User where User.email=Loaning.fromUser and Loaning.toUser='{$_SESSION['email']}' limit 5"); //TODO where user=?
            $query->execute();
            if($query->rowCount()>0) {
                echo "<table>";
                    echo "<th>Book</th>";
                    echo "<th>Borrowed from</th>";
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
                        echo "<td>{$loaning['user_name']}</td>";
                        echo "<td>{$loaning['start_date']}</td>";
                        echo "<td>{$loaning['end_date']}</td></tr>";
                    }
                echo "</table>";
            } else {
                echo "There are no loanings currently.";
            }
            echo "<form action='borrowDocument.php'>";
                echo "<input type='submit' value='Borrow a book' />";
            echo "</form>";
        echo "</div>";
    echo "</div>";
endblock() ?>