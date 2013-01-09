<?php require 'templates/base.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php
if ($_SERVER['REQUEST_METHOD']=='POST' and isset($_REQUEST['discard'])) {
    header("location:personalLibrary.php");
} elseif ($_SERVER['REQUEST_METHOD']=='POST') {
    if ($_REQUEST['visible']=='visible') {
        $visible=1;
    } else {
        $visible=0;
    }

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
        if ($_REQUEST['distributable']=='distributable') {
            $distributable=1;
        } else {
            $distributable=0;
        }

        # Get current information stored in the document
        $sql = "select * from ElectronicDoc where docID=?";
        $query = $con->prepare($sql);
        $query->execute(array($_GET['book']));
        $electronicDoc = $query->fetch();

        # Insert the changed document as a new document
        $sql = "insert into Document (author, description, document_name, visible, isbn) values(?, ?, ?, ?, ?)";
        $query = $con->prepare($sql);
        $query->execute(array($_REQUEST['author'],$_REQUEST['description'],$_REQUEST['document_name'],$visible,$_REQUEST['isbn']));
        $docID = $con->lastInsertId();

        # Link user to the proper document and unlink from old document
        $sql = "update ElectronicDocCopies set docID=? where docID=? and email='{$_SESSION['email']}'";
        $query = $con->prepare($sql);
        $query->execute(array($docID, str_replace('"','',$_GET['book'])));

        #Insert the changed document as a new electronic document
        $sql = "insert into ElectronicDoc values(?,?,?,?,?)";
        $query = $con->prepare($sql);
        $query->execute(array($docID, $distributable,$electronicDoc['extension'], $electronicDoc['content'], $electronicDoc['size']));
        $categories = $_POST['category'];
        foreach($categories as $category) {
            $sql = "insert into DocCategory values(?,?)";
            $query = $con->prepare($sql);
            $query->execute(array($docID, $category));
        }

        # Delete old document if this was the single user that possessed it.
        $sql = "select count(*) from Document where docID=?";
        $query = $con->prepare($sql);
        $query->execute(array(str_replace('"','',$_GET['book'])));
        if ($query->rowCount()<=1) { # single user
            $sql = "delete from ElectronicDoc where docID=?";
            $query = $con->prepare($sql);
            $query->execute(array(str_replace('"','',$_GET['book'])));
            $sql = "delete from DocCategory where docID=?";
            $query = $con->prepare($sql);
            $query->execute(array(str_replace('"','',$_GET['book'])));
            $sql = "delete from Document where docID=?";
            $query = $con->prepare($sql);
            $query->execute(array(str_replace('"','',$_GET['book'])));
        }
    }
    header("location:personalLibrary.php");
} else { //method is GET
    startblock('header');
        echo "<a href='index.php'>Home</a> &raquo; <a href='personalLibrary.php'>Personal Library</a> &raquo; Document";
    endblock();
    startblock('content');
        $sql = "select * from Document left join PaperDoc on Document.docID=PaperDoc.docID
                left join ElectronicDocCopies on Document.docID=ElectronicDocCopies.docID where Document.docID=?";
        $query = $con->prepare($sql);
        $query->execute(array($_GET['book']));
        echo "<div class='main'>";
            echo "<div class='blockHeader'> <h2>Document: {$book['document_name']}</h2></div>";
            echo "<div class='blockContent'>";
                if (False) {
                    echo "Document not found";
                } else {
                    $ownBook = False;
                    foreach($query as $bookRow) {
                        if ($bookRow['email']==$_SESSION['email']) {
                            $ownBook = True;
                        }
                    }
                    $sql = "select * from Document left join PaperDoc on Document.docID=PaperDoc.docID
                    left join ElectronicDocCopies on Document.docID=ElectronicDocCopies.docID where Document.docID=?";
                    $query = $con->prepare($sql);
                    $query->execute(array($_GET['book']));
                    $book = $query->fetch();
                    if (!$ownBook and !$book['visible']) {
                        echo "You are not allowed to view this document.";
                    } else {
                        echo "<form method='post'>";
                            echo "<hr/>";
                            echo "<h4>Name</h4>";
                            if ($ownBook) {
                                echo "<input type='text' name='document_name' value='{$book['document_name']}'/>";
                            } else {
                                echo $book['document_name'];
                            }
                            echo "<hr/>";
                            echo "<h4>Author</h4>";
                            if ($ownBook) {
                                echo "<input type='text' name='author' value='{$book['author']}'/>";
                            } else {
                                echo $book['author'];
                            }
                            echo "<hr/>";
                            echo "<h4>Description</h4>";
                            if ($ownBook) {
                                echo "<textarea name='description'>{$book['description']}</textarea>";
                            } else {
                                echo $book['description'];
                            }
                            echo "<hr/>";
                            echo "<h4>ISBN</h4>";
                            if ($ownBook) {
                                echo "<input type='text' name='isbn' value='{$book['isbn']}'/>";
                            } else {
                                echo $book['isbn'];
                            }
                            echo "<hr/>";
                            $sql = "select * from PaperDoc where docID =?";
                            $query = $con->prepare($sql);
                            $query->execute(array($_GET['book']));
                            $paperDoc = $query->fetch();
                            $sql = "select * from DocCategory where docID=?";
                            $query = $con->prepare($sql);
                            $query->execute(array($_GET['book']));
                            echo "<h4>Categories</h4>";
                            $categories = array("action","fantasy","fiction","romance", "comedy","adventure", "non-fiction", "education");
                            echo "<table border='0'>";
                                echo "<tr>";
                                    $q= $query->fetchAll();
                                    for($i=0;$i<sizeof($categories);$i++) {
                                        if ($i%5==0) {
                                            echo "</tr><tr>";
                                        }
                                        $checked=False;
                                        foreach($q as $category) {
                                            if($categories[$i] == $category['category']) {
                                                $checked=True;
                                            }
                                        }
                                        if($checked) {
                                            if ($ownBook) { #  If the book is your own, you are allowed to modify the checkboxes
                                                echo "<td><input type = 'checkbox' checked name = 'category[]' value='{$categories[$i]}' />$categories[$i]</td>";
                                            } else {
                                                echo "<td><input type = 'checkbox' checked name = 'category[]' value='{$categories[$i]}' disabled />$categories[$i]</td>";
                                            }
                                        } else {
                                            if($ownBook) {
                                                echo "<td><input type = 'checkbox' name = 'category[]' value='{$categories[$i]}' />$categories[$i]</td>";
                                            } else {
                                                echo "<td><input type = 'checkbox' name = 'category[]' value='{$categories[$i]}' disabled />$categories[$i]</td>";
                                            }
                                        }
                                    }
                                echo "</tr>";
                            echo "</table>";
                            if ($ownBook) {
                                echo "<hr/>";
                                echo "<h4>Visibility</h4>";
                                if ($book['visible']) {
                                    echo "<input type = 'radio' checked name = 'visible' value='visible' >visible<br/>";
                                    echo "<input type = 'radio' name = 'visible' value='notvisible' >not visible";
                                } else {
                                    echo "<input type = 'radio' name = 'visible' value='visible' >visible<br/>";
                                    echo "<input type = 'radio' checked name = 'visible' value='notvisible' >not visible";
                                }
                            }
                            echo "<hr/>";
                            if (isset($paperDoc['docID'])) { #paper doc
                                echo "<h4>Quality</h4>";
                                echo "Current: ".$paperDoc['state']."</br>";
                                if ($ownBook) {
                                    echo "<select name='state'>";
                                        echo "<option value='new'>new</option>";
                                        echo "<option value='good'>good</option>";
                                        echo "<option value='decent'>decent</option>";
                                        echo "<option value='poor'>poor</option>";
                                    echo "</select>";
                                }
                            } else { #electronic doc
                                $sql = "select * from ElectronicDoc where docID =?";
                                $query = $con->prepare($sql);
                                $query->execute(array($_GET['book']));
                                $electronicDoc = $query->fetch();
                                if ($ownBook) {
                                    echo "<h4>Distributable</h4>";
                                    if ($electronicDoc['distributable']) {
                                        echo "<input type = 'radio' checked name = 'distributable'value='distributable' />distributable<br/>";
                                        echo "<input type = 'radio' name = 'distributable' value='notdistributable' />not distributable";
                                    } else {
                                        echo "<input type = 'radio' name = 'distributable' value='distributable' />distributable<br/>";
                                        echo "<input type = 'radio' checked name = 'distributable' value='notdistributable' />not distributable";
                                    }
                                    echo "<hr/>";
                                }
                                echo "<h4>Extension</h4>";
                                echo $electronicDoc['extension'];
                                echo "<hr/>";
                                echo "<h4>Size</h4>";
                                echo $electronicDoc['size']." Bytes";
                            }
                            if (!isset($paperDoc['docID'])) { #electronic doc, downloadble
                                echo "<hr/>";
                                echo "<h4>Content</h4>";
                                $id=$_GET['book'];
                                if ($ownBook or $electronicDoc['distributable']) {
                                    echo "Download: <a href='download.php?id=".$id."'>{$book['document_name']}</a>";
                                } else {
                                    echo "This document is not open for distribution.";
                                }
                            }
                            echo "<hr/>";
                            if ($ownBook) {
                                echo "<input type='submit' name='apply' value='Apply changes'/>";
                                echo "<input type='submit' name='discard' value='Discard changes'/>";
                            }
                        echo "</form>";
                    }
                }
            echo "</div>";
        echo "</div>";
    endblock();
}
?>