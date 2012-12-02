        <?php require 'templates/base.php' ?>
        <link href="static/css/base.css" rel="stylesheet" type="text/css">
    	<?php startblock('header');
    		echo "Home";
    	endblock() ?>
    	<?php startblock('content');
    		echo " <div class='library'>";
    			echo "Sample data";
    		echo "</div>";
    		echo "<div class='notifications'>";
    			echo "<div class='notifyHeader'> <h2>Notifications</h2></div>";
	    		$query = $con->prepare('select * from Notification');
	       		$query->execute();
				foreach($query as $notification) {
					echo "<div class='notification'>";
	            		echo $notification['message'];
	            	echo "</div>";
	            }
            echo "</div>";

		endblock() ?>