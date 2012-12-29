<?php
if(isset($_GET['id']))
{
	// if id is set then get the file with the id from database

	$id    = $_GET['id'];
	$query = "SELECT content " .
	         "FROM ElectronicDoc WHERE id = '$id'";

	$result = mysql_query($query) or die('Error, query failed');
	list($content) = mysql_fetch_array($result);

	//header("Content-length: $size");
	//header("Content-type: $type");
	$name="downloaded file";
	header("Content-Disposition: attachment; filename=$name");
	echo $content;

	exit;
}

?>