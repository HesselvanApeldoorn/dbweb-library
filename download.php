<?php require '../../libConfig.php' ?>
<?php
session_start();
if  (!isset($_SESSION["email"])) {
    header("location:login.php");
}
try {
    $con = new PDO("mysql:dbname=$db;host=$host", $username, $password);
} catch(PDOException $e) {
        echo "Could not connect to database.";
        die();
}
?>

<?php

$sql = "SELECT * FROM ElectronicDocCopies WHERE docID = ? and email = ?";
$query = $con->prepare($sql);
$query->execute(array($id, $_SESSION["email"]));
$ownDoc = $query->fetch();

$id    = $_GET['id'];
$sql = "SELECT content, extension, size, distributable FROM ElectronicDoc WHERE docID = ?";
$query = $con->prepare($sql);
$query->execute(array($id));
$fileInfo = $query->fetch();

$sql = "SELECT * FROM Document WHERE docID = ?";
$query = $con->prepare($sql);
$query->execute(array($id));
$fileName = $query->fetch();
if($query->rowCount()==0 or !$fileInfo['distributable']) {
	echo "You are not allowed to download this document!";
	die();
	exit;
} else {

	header("Content-length: {$fileInfo['size']}");
	header("Content-type: {$fileInfo['extension']}");
	header("Content-Disposition: attachment; filename={$fileName['document_name']}");
	echo $fileInfo['content'];
	exit;
}
?>

