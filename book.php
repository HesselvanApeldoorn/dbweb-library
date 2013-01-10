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
        
        #Delete every category related to the current document
        $sql = "delete from DocCategory where docID=?";
        $query = $con->prepare($sql);
        $query->execute(array($paperDoc['docID']));
        
        #insert new categories to the current document
        $categories = $_REQUEST['category'];
        foreach($categories as $category) {
            $sql = "insert into DocCategory values(?,?)";
            $query = $con->prepare($sql);
            $query->execute(array($paperDoc['docID'], $category));
        }
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
        $categories = $_REQUEST['category'];
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
        $sql = "select *, PaperDoc.email as PDemail, ElectronicDocCopies.email as EDemail from Document left join PaperDoc on Document.docID=PaperDoc.docID
                left join ElectronicDocCopies on Document.docID=ElectronicDocCopies.docID where Document.docID=?";
        $query = $con->prepare($sql);
        $query->execute(array($_GET['book']));
        echo "<div class='main'>";
            echo "<div class='blockHeader'> <h2>Document: {$book['document_name']}</h2></div>";
            echo "<div class='blockContent'>";
                if ($query->rowCount()==0) {
                    echo "Document not found";
                } else {
                    $ownBook = False;
                    foreach($query as $bookRow) {
                        if ($bookRow['EDemail']==$_SESSION['email'] || $bookRow['PDemail']==$_SESSION['email']) {
                            $ownBook = True;
                        }
                    }
                    $query = $con->prepare($sql);
                    $query->execute(array($_GET['book']));
                    $book = $query->fetch();
                    if (!$ownBook and !$book['visible']) {
                        echo "You are not allowed to view this document.";
                    } else {
                        echo "<form method='post'>";
                            echo "<h4>Name<hr/></h4>";
                            if ($ownBook) {
                                echo "<input type='text' name='document_name' value='{$book['document_name']}'/>";
                            } else {
                                echo $book['document_name'];
                            }
                            echo "<h4>Author<hr/></h4>";
                            if ($ownBook) {
                                echo "<input type='text' name='author' value='{$book['author']}'/>";
                            } else {
                                echo $book['author'];
                            }
                            echo "<h4>Description<hr/></h4>";
                            if ($ownBook) {
                                echo "<textarea name='description'>{$book['description']}</textarea>";
                            } else {
                                echo $book['description'];
                            }
                            echo "<h4>ISBN<hr/></h4>";
                            if ($ownBook) {
                                echo "<input type='text' name='isbn' value='{$book['isbn']}'/>";
                            } else {
                                echo $book['isbn'];
                            }
                            $sql = "select * from PaperDoc where docID =?";
                            $query = $con->prepare($sql);
                            $query->execute(array($_GET['book']));
                            $paperDoc = $query->fetch();
                            $sql = "select * from DocCategory where docID=?";
                            $query = $con->prepare($sql);
                            $query->execute(array($_GET['book']));
                            echo "<h4>Categories<hr/></h4>";
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
                                          $checkString = 'checked';
                                        } else {
                                          $checkString = '';
                                        }
                                        if($ownBook) { #If the book is your own, you are allowed to modify the checkboxes
                                            $ownBookString = '';
                                        } else {
                                            $ownBookString = 'disabled';
                                        }
                                        echo "<td><input type = 'checkbox' $checkString name = 'category[]' value='{$categories[$i]}' $ownBookString />$categories[$i]</td>";
                                    }
                                echo "</tr>";
                            echo "</table>";
                            if ($ownBook) {
                                echo "<h4>Visibility<hr/></h4>";
                                if ($book['visible']) {
                                    $visChecked = 'checked';
                                    $notVisChecked = '';
                                } else {
                                    $visChecked = '';
                                    $notVisChecked = 'checked';
                                }
                                echo "<input type = 'radio' $visChecked name = 'visible' value='visible' >visible<br/>";
                                echo "<input type = 'radio' $notVisChecked name = 'visible' value='notvisible' >not visible";
                            }
                            if (isset($paperDoc['docID'])) { #paper doc
                                echo "<h4>Quality<hr/></h4>";
                                echo "Current: ";
                                $options = array("","","","");
                                $values = array("new","good","decent","poor");
                                for($i=0;$i<sizeof($options);$i++) {
                                    if($paperDoc['state']==$values[$i]) {
                                        $options[$i] =  "selected='selected'";
                                    }
                                }
                                if ($ownBook) {
                                    echo "<select name='state'>";
                                        echo "<option $options[0] value='new'>new</option>";
                                        echo "<option $options[1] value='good'>good</option>";
                                        echo "<option $options[2] value='decent'>decent</option>";
                                        echo "<option $options[3] value='poor'>poor</option>";
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
                                        $distChecked = 'checked';
                                        $notDistChecked = '';
                                    } else {
                                        $distChecked = '';
                                        $notDistChecked = 'checked';
                                    }
                                    echo "<input type = 'radio' $distChecked name = 'distributable'value='distributable' />distributable<br/>";
                                    echo "<input type = 'radio' $notDistChecked name = 'distributable' value='notdistributable' />not distributable";
                                }
                                echo "<h4>Extension<hr/></h4>";
                                echo $electronicDoc['extension'];
                                echo "<h4>Size<hr/></h4>";
                                echo $electronicDoc['size']." Bytes";
                            }
                            if (!isset($paperDoc['docID'])) { #electronic doc, downloadble
                                echo "<h4>Content<hr/></h4>";
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