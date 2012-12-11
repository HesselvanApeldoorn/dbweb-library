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
        });
        $(function() {
            $("#datepicker2").datepicker();
        });
    </script>
<?php endblock() ?>
<?php startblock('header');
        echo "Welcome, Cagri, you are here: <a href='index.php'>Home</a> &raquo; <a href='personal.php'>Personal Page</a> &raquo; <a href='loanings.php'>Loanings</a> &raquo; Borrow";
endblock() ?>
<?php startblock('content'); ?>

	<?php
    echo "<div class='main'>";
        echo "<div class='blockHeader'><h2>Loanings</h2></div><br>";
        echo "<div class='blockContent'>";
            if(!isset($_REQUEST['borrow'])) {

                $sql = "select * from PaperDoc";
                $query = $con->prepare($sql);
                $query->execute();
                echo "<h4>Document</h4>";
                echo "<select name='document'>";
                foreach($query as $book) {
                        $sql = "select * from Document where docID=?";
                        $query = $con->prepare($sql);
                        $query->execute(array(str_replace('"','',$book['docID'])));
                        $document = $query->fetch;
                        echo "<option value='{$book['docID']}' >{$document['document_name']}</option>";
                }
                echo "</select>";
                echo "<hr/>";
               
                echo "<form>";
                    echo "<p style='float:left'>Start date: <input type='text' name='start' id='datepicker' value='".date("m/d/Y")."'/>";
                    echo "<p style='float:left'>End date: <input type='text' name='end' id='datepicker2' value='".date("m/d/Y",strtotime("+7 day"))."'/>";
                    echo "<input type='submit' name='borrow' value='Borrow' />";
                echo "</form";
            } else {
                echo "Start date: " . $_REQUEST['start'];
                echo "<br/>End date: ".$_REQUEST['end'];
                if($_REQUEST['start'] >= $_REQUEST['end']) {
                    echo "<p>Your start date is after the end date.</p>";
                }
            }
        echo "</div>";  
    echo "</div>";
endblock() ?>