<?php require 'templates/base.php';            error_reporting(-1);
            ini_set("display_errors", 1); ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php startblock('header');
        echo "Welcome, Cagri, you are here: <a href='index.php'>Home</a> &raquo; Search";
endblock() ?>
<?php startblock('content');
    echo "<div class='main'>";
        echo "<div class='blockHeader'><h2>Search</h2></div><br>";
        echo "<div class='blockContent'>";
            echo "<div class='search_bar'>";
                echo "<form method='post'>";
                    echo "<input type='text' name='searchText' size='100%'>";
                   
                    if(!isset($_REQUEST['searchCriteria'])) {
                        $selected = "all";
                    } else {
                        $selected = $_REQUEST['searchCriteria'];
                    }    
                    $options = array("","","","", "","");
                    $values = array("all","document_name", "author", "isbn", "description", "category");
                    for($i=0;$i<sizeof($options);$i++) {
                        if($selected==$values[$i]) {
                            $options[$i] =  "selected='selected'";
                        }
                    }
                    echo "<select name='searchCriteria'>    
                            <option value='all' $options[0]>All</selected>
                            <option value='document_name' $options[1]>Name</option>
                            <option value='author' $options[2]>Author</option>
                            <option value='isbn' $options[3]>ISBN</option>
                            <option value='description' $options[4]>Description</option>
                            <option value='category' $options[5]>Category</option>
                        </select>";
                    echo "<input type='submit' name='searchButton' value='Find'>";
                echo "</form>";
            echo "</div>";
            if(isset($_REQUEST['searchButton'])) {
                if(!isset($_REQUEST['searchCriteria'])) {
                    $selected = 'all';
                } else {
                    $selected = $_REQUEST['searchCriteria'];
                }
                if($selected!='all' && $selected!='category' && preg_match("/^(document_name|author|isbn|description)$/", $selected, $match)) {
                    $crit = $match[1];     
                    $sql =  "select * from Document where $crit like ?";
                } else if($selected=='all') {
                    $sql =  "select * from Document where concat(document_name, author, description, ifnull(isbn, '')) like ?";
                } else if($selected=='category') {
                    $sql = "select * from Document inner join (SELECT docID from DocCategory where category like ?) as CAT on Document.docID=CAT.docID";
                } else {
                    echo "Hacking attempt";
                }
                $q = $con->prepare($sql);
                $q->execute(array("%{$_REQUEST['searchText']}%"));
                if($q->rowCount()>0) {
                    echo "<table>"; 
                        echo "<th> Name</th>";
                        echo "<th> Author</th>";
                        echo "<th> Description </th>";
                        echo "<th> ISBN(optional)</th>";
                        $i=0;
                        foreach($q as $row) { 
                            $i = $i+1;
                            $i=$i%2;
                            $idvar = 'even';
                            if ($i==0) {
                                $idvar='odd';
                            }
                            echo "<tr id='$idvar' ><td><a href='book.php?book={$row['docID']}'>{$row['document_name']}</a></td>";
                            echo "<td>{$row['author']}</td>";
                            $desc = "<td>".substr($row['description'],0,20);
                            if(strlen($row['description'])>20) {
                                $desc = $desc . "..."; 
                            } 
                            echo $desc ."</td>";
                            echo "<td>{$row['isbn']}</td>";
                            echo "</tr>";
                        }
                    echo "</table>";
                } else {
                    echo "There are no matches in the database for: {$_REQUEST['searchText']}.";
                }
            }
        echo "</div>";
    echo "</div>";

endblock(); ?>
