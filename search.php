<?php require 'templates/base.php';            error_reporting(-1);
            ini_set("display_errors", 1); ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php startblock('header');
        echo "<a href='index.php'>Home</a> &raquo; Search";
endblock() ?>
<?php startblock('content');
    echo "<div class='main'>";
        echo "<div class='blockHeader'><h2>Search</h2></div><br>";
        echo "<div class='blockContent'>";
            /* Search bar */
            echo "<div class='search_bar'>";
                echo "<form method='post'>";
                    echo "<input type='text' name='searchText' value='Search for books' onfocus='if(this.value == \"Search for books\") { this.value = \"\"; }' onblur='if(this.value == \"\") { this.value = \"Search for books\"; }'>";

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
            /* Handling searches */
            if(isset($_REQUEST['searchButton'])) {
                if(!isset($_REQUEST['searchCriteria'])) { //This is needed if the user searches from the home page
                    $selected = 'all';
                } else {
                    $selected = $_REQUEST['searchCriteria'];
                }
                if($selected!='all' && $selected!='category' && preg_match("/^(document_name|author|isbn|description)$/", $selected, $match)) { //the user searches on (name||author||isbn||description)
                    $crit = $match[1];
                    $sql =  "select *, GROUP_CONCAT(category separator ', ') as categoryConcat from Document inner join DocCategory on Document.docID=DocCategory.docID where $crit like ? AND visible = 1 group by Document.docID";
                } else if($selected=='all') { // the user searches on everything
                    $sql =  "select * from Document inner join (select docID, GROUP_CONCAT(category separator ', ') as categoryConcat from DocCategory group by docID) as CAT on Document.docID=CAT.docID  where concat(categoryConcat, document_name, author, description, ifnull(isbn, '')) like ? AND visible = 1;";
                } else if($selected=='category') {// the user searches on category
                    $sql = "select * from Document inner join (select docID, GROUP_CONCAT(category separator ', ')  as categoryConcat from DocCategory group by docID) as CAT on Document.docID=CAT.docID where CAT.categoryConcat like ? AND visible=1";
                } else {
                    echo "Hacking attempt";
                }
                $q = $con->prepare($sql); // Group concat is used so multiple categories are shown in the same row.
                $q->execute(array("%{$_REQUEST['searchText']}%"));
                /* Show search results */
                if($q->rowCount()>0) {
                    echo $q->rowCount() . " search results.<br/>";
                    echo "<table>";
                        echo "<th> Name</th>";
                        echo "<th> Author</th>";
                        echo "<th> Category</th>";
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
                            echo "<td>{$row['categoryConcat']}</td>";
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
