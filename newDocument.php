<?php
if ($_SERVER['REQUEST_METHOD']=='POST') {
    require '../../libConfig.php';
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




    if ($_REQUEST['visible']=='visible') {
        $visible=1;
    } else {
        $visible=0;
    }

    $sql = "select * from PaperDoc where docID =?";
    $query = $con->prepare($sql);
    $query->execute(array($_GET['book']));
    $paperDoc = $query->fetch();
    if ($_REQUEST['docType']=='paper') {
        $sql = "insert into Document (author,description,document_name,visible,isbn) values (?,?,?,?,?)";
        $query = $con->prepare($sql);
        $query->execute(array($_REQUEST['author'],$_REQUEST['description'],$_REQUEST['document_name'],$visible,$_REQUEST['isbn']));
        $docID = $con->lastInsertId();
        $sql = "insert into PaperDoc (docID, state, email) values (?, ?,?)";
        $query = $con->prepare($sql);
        $query->execute(array($docID, $_REQUEST['state'], $_SESSION['email']));

        #insert categories to the new document
        $categories = $_REQUEST['category'];
        foreach($categories as $category) {
            $sql = "insert into DocCategory values(?,?)";
            $query = $con->prepare($sql);
            $query->execute(array($docID, $category));
        }
    } else { # electronic Document
        if ($_REQUEST['distributable']=='distributable') {
            $distributable=1;
        } else {
            $distributable=0;
        }

        # handle file
        if($_FILES['content']['size'] > 0) {
            $fileName = $_FILES['content']['name'];
            $tmpName  = $_FILES['content']['tmp_name'];
            $fileSize = $_FILES['content']['size'];
            $fileType = $_FILES['content']['type'];

            $fp      = fopen($tmpName, 'r');
            $content = fread($fp, filesize($tmpName));
            $content = addslashes($content);
            if(!get_magic_quotes_gpc())
            {
                 $fileName = addslashes($fileName);
            }
            fclose($fp);
            header("Content-Disposition: attachment; filename=$fileName"); 
            header("Content-length: $fileSize"); 
            header("Content-type: $fileType"); 
            echo $content; 

        } else {
            echo "Something went wrong with your uploaded file. <a href='#'>Retry?</a>";
        }

        $sql = "insert into Document (author, description, document_name, visible, isbn) values(?, ?, ?, ?, ?)";
        $query = $con->prepare($sql);
        $query->execute(array($_REQUEST['author'],$_REQUEST['description'],$fileName,$visible,$_REQUEST['isbn']));
        $docID = $con->lastInsertId();

        $sql = "insert into ElectronicDocCopies (docID,email) values (?,?)";
        $query = $con->prepare($sql);
        $query->execute(array($docID, $_SESSION['email']));

        $sql = "insert into ElectronicDoc (docID, distributable, extension, size, content) values(?,?,?,?,?)";
        $query = $con->prepare($sql);
        $query->execute(array($docID, $distributable, $fileType, $fileSize, $content));
        $categories = $_POST['category'];
        foreach($categories as $category) {
            $sql = "insert into DocCategory values(?,?)";
            $query = $con->prepare($sql);
            $query->execute(array($docID, $category));
        }
    }
    //header("location:personalLibrary.php");
} else { //method is GET
    echo '<link href="static/css/base.css" rel="stylesheet" type="text/css">';
    require 'templates/base.php';
    startblock('scripts');
        echo "<script> function enable(n) {
          if (n==1) {
            document.getElementById('electronic').style.display ='none';
            document.getElementById('paper').style.display ='block';
          } else if(n==2) {
            document.getElementById('paper').style.display ='none';
            document.getElementById('electronic').style.display ='block';
          }
        } </script>";
    endblock();
    startblock('header');
        echo "<a href='index.php'>Home</a> &raquo; <a href='personalLibrary.php'>Personal Library</a> &raquo; New Document";
    endblock();
    startblock('content');
        echo "<div class='main'>";
            echo "<div class='blockHeader'> <h2>Add a new document</h2></div>";
            echo "<div class='blockContent'>";
                echo "<form method='post' enctype='multipart/form-data'>";
                    echo "<h4>Author<hr/></h4>";
                    echo "<input type='text' name='author' value=''/>";
                    echo "<h4>Description<hr/></h4>";
                    echo "<textarea name='description'></textarea>";
                    echo "<h4>ISBN<hr/></h4>";
                    echo "<input type='text' name='isbn' />";
                    echo "<h4>Categories<hr/></h4>";
                    $categories = array("action","fantasy","fiction","romance", "comedy","adventure", "non-fiction", "education", "religious","detective");
                    echo "<table border='0'>";
                        echo "<tr>";
                            for($i=0;$i<sizeof($categories);$i++) {
                                if ($i%5==0) {
                                    echo "</tr><tr>";
                                }
                                echo "<td><input type = 'checkbox' name = 'category[]' value='{$categories[$i]}' />$categories[$i]</td>";
                            }
                        echo "</tr>";
                    echo "</table>";
                    echo "<h4>Visibility<hr/></h4>";
                    echo "<input type = 'radio' checked name = 'visible' value='visible' >visible<br/>";
                    echo "<input type = 'radio' name = 'visible' value='notvisible' >not visible";
                    echo "<h4>type of document<hr/></h4>";
                    echo "<input type = 'radio' name = 'docType' onclick='enable(1);' value='paper' />paper<br/>";
                    echo "<input type = 'radio' name = 'docType' onclick='enable(2);' value='electronic' />electronic<br/>";
                    echo "<div id='paper' style='display: none'>";
                        echo "<h4>Name<hr/></h4>";
                        echo "<input type='text' name='document_name' value=''/>";
                        echo "<h4>Quality<hr/></h4>";
                        echo "<select name='state'>";
                            echo "<option value='new'>new</option>";
                            echo "<option value='good'>good</option>";
                            echo "<option value='decent'>decent</option>";
                            echo "<option value='poor'>poor</option>";
                        echo "</select>";
                        echo "<p><input type='submit' value='Add Document'/></p>";
                    echo "</div>";
                    echo "<div id='electronic' style='display: none'>";
                        echo "<h4>Distributable<hr/></h4>";
                        echo "<input type = 'radio' name = 'distributable' value='distributable' />distributable<br/>";
                        echo "<input type = 'radio' name = 'distributable' value='notdistributable' />not distributable";
                        echo "<h4>Content<hr/></h4>";
                        echo "<input type='hidden' name='MAX_FILE_SIZE' value='20000000'>";
                        echo "<input type='file' name='content' id='file'><br>";
                        echo "<p><input type='submit' value='Add Document'/></p>";
                    echo "</div>";
                echo "</form>";
            echo "</div>";
        echo "</div>";
    endblock();
}
?>