<?php require 'templates/base.php' ?>
<?php
$id    = $_GET['id'];
$sql = "SELECT content, extension, size FROM ElectronicDoc WHERE docID = ?";
$query = $con->prepare($sql);
$query->execute(array($id));
$fileInfo = $query->fetch();

$sql = "SELECT * FROM Document WHERE docID = ?";
$query = $con->prepare($sql);
$query->execute(array($id));
$fileName = $query->fetch();

header("Content-length: {$fileInfo['size']}");
header("Content-type: {$fileInfo['extension']}");
header("Content-Disposition: attachment; filename={$fileName['document_name']}");
exit;
?>