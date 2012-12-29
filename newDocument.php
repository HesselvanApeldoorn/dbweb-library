<?php require 'templates/base.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php
if ($_SERVER['REQUEST_METHOD']=='POST') {
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
    } else { # electronic Document
        if ($_REQUEST['distributable']=='distributable') {
            $distributable=1;
        } else {
            $distributable=0;
        }
        $sql = "insert into Document (author, description, document_name, visible, isbn) values(?, ?, ?, ?, ?)";
        $query = $con->prepare($sql);
        $query->execute(array($_REQUEST['author'],$_REQUEST['description'],$_REQUEST['document_name'],$visible,$_REQUEST['isbn']));
        $docID = $con->lastInsertId();

        $sql = "insert into ElectronicDocCopies (docID,email) values (?,?)";
        $query = $con->prepare($sql);
        $query->execute(array($docID, $_SESSION['email']));

        # handle file
        if($_FILES['content']['size'] > 0) {
            $fileName = $_FILES['content']['name'];
            $tmpName  = $_FILES['content']['tmp_name'];
            $fileSize = $_FILES['content']['size'];
            $fileType = $_FILES['content']['type'];

            $fp      = fopen($tmpName, 'r');
            $content = fread($fp, filesize($tmpName));
            $content = addslashes($content);
            fclose($fp);
            if(!get_magic_quotes_gpc())
            {
                $fileName = addslashes($fileName);
            }
        }


        $sql = "insert into ElectronicDoc (docID, distributable, extension, content) values(?,?,?,?)";
        $query = $con->prepare($sql);
        $query->execute(array($docID, $distributable,$_REQUEST['extension'], $content));
        $categories = $_POST['category'];
        foreach($categories as $category) {
            $sql = "insert into DocCategory values(?,?)";
            $query = $con->prepare($sql);
            $query->execute(array($docID, $category));
        }
    }
    header("location:personalLibrary.php");
} else { //method is GET
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
                    echo "<hr/>";
                    echo "<h4>Name</h4>";
                    echo "<input type='text' name='document_name' value=''/>";
                    echo "<hr/>";
                    echo "<h4>Author</h4>";
                    echo "<input type='text' name='author' value=''/>";
                    echo "<hr/>";
                    echo "<h4>Description</h4>";
                    echo "<textarea name='description'></textarea>";
                    echo "<hr/>";
                    echo "<h4>ISBN</h4>";
                    echo "<input type='text' name='isbn' />";
                    echo "<hr/>";
                    echo "<h4>Categories</h4>";
                    $categories = array("action","fantasy","fiction","romance", "comedy","adventure", "non-fiction", "education");
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
                    echo "<hr/>";
                    echo "<h4>Visibility</h4>";
                    echo "<input type = 'radio' checked name = 'visible' value='visible' >visible<br/>";
                    echo "<input type = 'radio' name = 'visible' value='notvisible' >not visible";
                    echo "<hr/>";
                    echo "<h4>type of document</h4>";
                    echo "<input type = 'radio' name = 'docType' onclick='enable(1);' value='paper' />paper<br/>";
                    echo "<input type = 'radio' name = 'docType' onclick='enable(2);' value='electronic' />electronic<br/>";
                    echo "<hr/>";
                    echo "<div id='paper' style='display: none'>";
                        echo "<h4>Quality</h4>";
                        echo "<select name='state'>";
                            echo "<option value='new'>new</option>";
                            echo "<option value='good'>good</option>";
                            echo "<option value='decent'>decent</option>";
                            echo "<option value='poor'>poor</option>";
                        echo "</select>";
                        echo "<hr/>";
                        echo "<input type='submit' value='Add Document'/>";
                    echo "</div>";
                    echo "<div id='electronic' style='display: none'>";
                        echo "<h4>Distributable</h4>";
                        echo "<input type = 'radio' name = 'distributable' value='distributable' />distributable<br/>";
                        echo "<input type = 'radio' name = 'distributable' value='notdistributable' />not distributable";
                        echo "<hr/>";
                        echo "<h4>Extension</h4>";
                        echo "<input type='text' name='extension' value=''/>";
                        echo "<hr/>";
                        echo "<h4>Content</h4>";
                        echo "<input type='hidden' name='MAX_FILE_SIZE' value='20000000'>";
                        echo "<input type='file' name='content' id='file'><br>";
                        echo "<hr/>";
                        echo "<input type='submit' value='Add Document'/>";
                    echo "</div>";
                echo "</form>";
            echo "</div>";
        echo "</div>";
    endblock();
}
?>