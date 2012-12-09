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
    </script>
<?php endblock() ?>
<?php startblock('header');
        echo "Welcome, Cagri, you are here: <a href='index.php'>Home</a> &raquo; Notifications";
endblock() ?>
<?php startblock('content'); ?>

	<?php
    echo "<div class='main'>";
        echo "<div class='blockHeader'><h2>Loanings</h2></div><br>";
        echo "<div class='blockContent'>";
        $sql = "select * from PaperDoc";
        $query = $con->prepare($sql);
        $query->execute();
        echo "<h4>Document</h4>";
        echo "<select name='document'>";
        foreach($query as $book) {
        	$sql = "select * from Document where docID=?";
	        $query = $con->prepare($sql);
	        $query->execute(array($book['docID']));
	        $document = $query->fetch;
	        echo "<option value='{$book['docID']}' >{$document['document_name']}</option>";
        }
        echo "</select>";
        echo "<hr/>";
        echo "<h4>Start Date</h4>";
		echo "<p>Date: <input type='text' id='datepicker' /></p>";
		echo "<h4>End Date</h4>";
		echo "<p>Date: <input type='text' id='datepicker' /></p>";
    echo "</div>";
endblock() ?>