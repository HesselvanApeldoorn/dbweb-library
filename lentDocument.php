<?php require 'templates/base.php';
                        error_reporting(-1);
            ini_set("display_errors", 1); ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php startblock('scripts') ?>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" />
    <script src="http://code.jquery.com/jquery-1.8.3.js"></script>
    <script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
    <link rel="stylesheet" href="/resources/demos/style.css" />
    <script>
        $(function() {
            $("#datepicker").datepicker();
            $("#datepicker").datepicker("option", "dateFormat","yy-mm-dd");
            $("#datepicker").datepicker("option", "showAnim", "slideDown");

        });
        $(function() {
            $("#datepicker2").datepicker();
            $("#datepicker2").datepicker("option", "dateFormat","yy-mm-dd");
            $("#datepicker2").datepicker("option", "showAnim", "slideDown");
        });
    </script>
<?php endblock() ?>
<?php startblock('header');
    echo "Welcome, Cagri, you are here: <a href='index.php'>Home</a> &raquo; <a href='personal.php'>Personal Page</a> &raquo; <a href='loanings.php'>Loanings</a> &raquo; Lent";
endblock() ?>
<?php startblock('content');
    echo "<div class='main'>";
        echo "<div class='blockHeader'><h2>Lent a document</h2></div><br>";
        echo "<div class='blockContent'>";
            if ($_SERVER['REQUEST_METHOD']=='POST') {
                if($_REQUEST['start'] >= $_REQUEST['end']) {
                    echo "Start date: " . $_REQUEST['start'];
                    echo "<br/>End date: ".$_REQUEST['end'];
                    echo "<p>Your start date is after the end date.</p>";
                    echo "<a href=''>Retry</a>"; 
                } else {
                    $sql = "insert into Loaning (docID, start_date, end_date, fromUser, toUser) values(?,?,?,?,?)";
                    $query = $con->prepare($sql);
                    $query->execute(array($_REQUEST['docID'], $_REQUEST['start'], $_REQUEST['end'], "sample@hotmail.com", "sample2@hotmail.com"));
                    header("location:personalLibrary.php");                    
                }
            } else { # method is GET   
                $sql = "select * from PaperDoc";
                $query = $con->prepare($sql);
                $query->execute();
                if($query->rowCount()>0) {
                    echo "<form method='post'>";
                        echo "Document: <select name='docID'>";
                        foreach($query as $book) {
                                $sql = "select * from Document where docID=?";
                                $query2 = $con->prepare($sql);
                                $query2->execute(array(str_replace('"','',$book['docID'])));
                                $document = $query2->fetch();
                                echo "<option name='docID' value='{$document['docID']}' >{$document['document_name']}</option>";
                        }
                        echo "</select>";
                        echo "<hr/>";
                        echo "<div style='float:left'>Start date: <input type='date' name='start' id='datepicker' value='".date("m/d/Y")."'/>";
                        echo "&nbsp; &nbsp; &nbsp; &nbsp;End date: <input type='date' name='end' id='datepicker2' value='".date("m/d/Y",strtotime("+7 day"))."'/></div>";
                        echo "<br/><br/><input type='submit' name='lent' value='Lent'/>";
                    echo "</form";
                } else { //No docs to be lent
                    echo "There are no books to be lent currently.";
                }
            }
        echo "</div>";  
    echo "</div>";
endblock() ?>