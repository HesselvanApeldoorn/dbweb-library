<?php require 'templates/base.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php startblock('header');
        echo "Home";
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
            echo "<h3><a href='personalLibrary.php'>Personal library</a><hr/></h3>";
            $query = $con->prepare("select Document.* from Document
                join PaperDoc on (Document.docID=PaperDoc.docID) where PaperDoc.email='{$_SESSION['email']}' union
                select Document.* from Document
                join ElectronicDocCopies on (Document.docID=ElectronicDocCopies.docID) where ElectronicDocCopies.email='{$_SESSION['email']}' LIMIT 10");
            $query->execute();
            if($query->rowCount()>0) {
                echo "<table>";
                    echo "<th> Name</th>";
                    echo "<th> Author</th>";
                    echo "<tfoot align='right'>
                            <tr >
                                <td colspan='4'> <a href='personalLibrary.php'>See more...</a></td>
                            </tr>
                        </tfoot>";
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
                        echo "</tr>";
                    }
                echo "</table>";
            } else { //no documents
                echo "There are no documents currently.";
            }
            echo "<h3><a href='loanings.php'>Books you lent</a><hr/></h3>";
            $query = $con->prepare("select * from Loaning,User where User.email=Loaning.toUser and Loaning.fromUser='{$_SESSION['email']}' limit 5"); //TODO where user=?
            $query->execute();
            if($query->rowCount()>0) {
                echo "<table>";
                    echo "<th>Book</th>";
                    echo "<th>Lent to</th>";
                    echo "<th>Start date</th>";
                    echo "<th>end date</th>";
                    echo "<tfoot align='right'>
                            <tr >
                                <td colspan='4'> <a href='loanings.php'>See more...</a></td>
                            </tr>
                        </tfoot>";

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
                        echo "<td>{$loaning['user_name']}</td>";
                        echo "<td>{$loaning['start_date']}</td>";
                        echo "<td>{$loaning['end_date']}</td></tr>";
                    }
                echo "</table>";
            } else {//no loanings
                echo "There are no loanings currently.";
            }
            echo "<h3><a href='loanings.php'>Books you borrowed</a><hr/></h3>";
            $query = $con->prepare("select * from Loaning,User where User.email=Loaning.fromUser and Loaning.toUser='{$_SESSION['email']}' limit 5"); //TODO where user=?
            $query->execute();
            if($query->rowCount()>0) {
                echo "<table>";
                    echo "<th>Book</th>";
                    echo "<th>Borrowed from</th>";
                    echo "<th>Start date</th>";
                    echo "<th>end date</th>";
                    echo "<tfoot align='right'>
                            <tr >
                                <td colspan='4'> <a href='loanings.php'>See more...</a></td>
                            </tr>
                        </tfoot>";

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
                        echo "<td>{$loaning['user_name']}</td>";
                        echo "<td>{$loaning['start_date']}</td>";
                        echo "<td>{$loaning['end_date']}</td></tr>";
                    }
                echo "</table>";
            } else {//no loanings
                echo "There are no loanings currently.";
            }
        echo "</div>";
    echo "</div>";
    echo "<div class='notifications'>";
        echo "<div class='blockHeader'> <h2>Notifications</h2></div>";
        echo "<div class='blockContent'>";
            $query = $con->prepare("select * from Notification where email='{$_SESSION['email']}' order by notify_date DESC limit 5");
            $query->execute();
            if($query->rowCount()>0) {
                foreach($query as $notification) {
                    echo "<div class='notification'>";
                        echo "<strong>{$notification['notify_date']}</strong><hr/>";
                        echo $notification['message'];
                    echo "</div>";
                }
                echo "<a style='float:right' href='notifications.php'>See more...</a>";
            } else { //no notifications
                echo "There are no notifications currently.";
            }
        echo "</div>";
    echo "</div>";
endblock() ?>
