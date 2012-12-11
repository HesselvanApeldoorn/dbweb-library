<?php require 'templates/base.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php
if ($_SERVER['REQUEST_METHOD']=='POST') {
    if ($_REQUEST['visible']=='visible') {
        $visible=1;
    } else {
        $visible=0;
    }
    $sql = "select count(*) from Document";
    $query = $con->prepare($sql);
    $query->execute();
    $docID= $query->fetchColumn()+1;

    $sql = "select * from PaperDoc where docID =?";
    $query = $con->prepare($sql);
    $query->execute(array($_GET['book']));
    $paperDoc = $query->fetch();
    if (isset($paperDoc['docID'])) {
        $sql = "update Document set author=?, description=?, document_name=?, visible=?, isbn=? where docID=?";
        $query = $con->prepare($sql);
        $query->execute(array($_REQUEST['author'],$_REQUEST['description'],$_REQUEST['document_name'],$visible,$_REQUEST['isbn'], $_GET['book']));
        $sql = "update PaperDoc set state=? where docID=?";
        $query = $con->prepare($sql);
        $query->execute(array($_REQUEST['state'], $_GET['book']));
    } else {
        echo $_REQUEST['distributable'];
        if ($_REQUEST['distributable']=='distributable') {
            $distributable=1;
        } else {
            $distributable=0;
        }
        $sql = "select * from Document where docID=?";
        $query = $con->prepare($sql);
        $query->execute(array("{$_GET['book']}"));

        $a = (int) $_GET['book'];
        echo $a;

        $sql = "delete from Document where docID=6";
        $query = $con->prepare($sql);
        $query->execute();

        $sql = "delete from Document where docID=?";
        $query = $con->prepare($sql);
        $query->bindParam(1, $a, PDO::PARAM_INT);
        $query->execute();

        $sql = "insert into Document values(?, ?, ?, ?, ?, ?)";
        $query = $con->prepare($sql);
        $query->execute(array("$docID,'{$_REQUEST['author']}','{$_REQUEST['description']}','{$_REQUEST['document_name']}',$visible,'{$_REQUEST['isbn']}'"));

        $sql = "update ElectronicDocCopies set docID=? where docID=?";
        $query = $con->prepare($sql);
        $query->execute(array("$docID, ${_GET['book']}"));

        $sql = "update ElectronicDoc set docID=?, distributable=? where docID=?";
        $query = $con->prepare($sql);
        $query->execute(array("$docID, $distributable, {$_GET['book']}"));

    }
    echo "done";
    //header("location:library.php");
} else { //method is GET
    startblock('header');
        echo "Welcome, Cagri, you are here: <a href='index.php'>Home</a> &raquo; <a href='library.php'>Library</a> &raquo; Document";
    endblock();
    startblock('content');
        $sql = "select * from Document where docID=?";
        $query = $con->prepare($sql);
        $query->execute(array($_GET['book']));
        $book = $query->fetch();
        echo "<div class='main'>";
            echo "<div class='blockHeader'> <h2>Document: {$book['document_name']}</h2></div>";
            echo "<div class='blockContent'>";
                echo "<form method='post'>";
                    echo "<hr/>";
                    echo "<h4>Name</h4>";
                    echo "<input type='text' name='document_name' value='{$book['document_name']}'/>";
                    echo "<hr/>";
                    echo "<h4>Author</h4>";
                    echo "<input type='text' name='author' value='{$book['author']}'/>";
                    echo "<hr/>";
                    echo "<h4>Description</h4>";
                    echo "<textarea name='description'>{$book['description']}</textarea>";
                    echo "<hr/>";
                    echo "<h4>Visibility</h4>";
                    if ($book['visible']) {
                        echo "<input type = 'radio' checked name = 'visible' value='visible' >visible<br/>";
                        echo "<input type = 'radio' name = 'visible' value='notvisible' >not visible";
                    } else {
                        echo "<input type = 'radio' name = 'visible' value='visible' >visible<br/>";
                        echo "<input type = 'radio' checked name = 'visible' value='notvisible' >not visible";
                    }
                    echo "<hr/>";
                    echo "<h4>ISBN</h4>";
                    echo "<input type='text' name='isbn' value='{$book['isbn']}'/>";
                    echo "<hr/>";
                    $sql = "select * from PaperDoc where docID =?";
                    $query = $con->prepare($sql);
                    $query->execute(array($_GET['book']));
                    $paperDoc = $query->fetch();
                    echo "<hr/>";
                    echo "<h4>Categories</h4>";
                    echo $electronicDoc['content'];
                    if (isset($paperDoc['docID'])) {
                        echo "<h4>Quality</h4>";
                        echo "Current: ".$paperDoc['state']."</br>";
                        echo "<select name='state'>";
                            echo "<option value='new'>new</option>";
                            echo "<option value='good'>good</option>";
                            echo "<option value='decent'>decent</option>";
                            echo "<option value='poor'>poor</option>";
                        echo "</select>";
                    } else {
                        $sql = "select * from ElectronicDoc where docID =?";
                        $query = $con->prepare($sql);
                        $query->execute(array($_GET['book']));
                        $electronicDoc = $query->fetch();
                        echo "<h4>Distributable</h4>";
                        if ($electronicDoc['distributable']) {
                            echo "<input type = 'radio' checked name = 'distributable'value='distributable' />distributable<br/>";
                            echo "<input type = 'radio' name = 'distributable' value='notdistributable' />not distributable";
                        } else {
                            echo "<input type = 'radio' name = 'distributable' value='distributable' />distributable<br/>";
                            echo "<input type = 'radio' checked name = 'distributable' value='notdistributable' />not distributable";
                        }
                        echo "<hr/>";
                        echo "<h4>Content</h4>";
                        echo $electronicDoc['content'];
                    }
                    echo "<hr/>";
                    echo "<input type='submit' value='Apply changes'/>";
                    echo "<input type='submit' value='Discard changes'/>";
                echo "</form>";
            echo "</div>";
        echo "</div>";
    endblock();
}
?>