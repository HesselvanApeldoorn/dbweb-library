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
echo $fileInfo['content'];
exit;
?>