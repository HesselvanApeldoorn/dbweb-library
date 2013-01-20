<?php require 'templates/base.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php
if ($_SERVER['REQUEST_METHOD']=='POST') {
    if(isset($_REQUEST['discard'])) {
        header("location:personalLibrary.php");
    } elseif(isset($_REQUEST['delete'])) { # confirm delete document
        $sql = "select document_name from Document where docID=?";
        $query = $con->prepare($sql);
        $query->execute(array($_GET['book']));
        $name = $query->fetch();
        startblock('header');
            echo "<a href='index.php'>Home</a> &raquo; <a href='personalLibrary.php'>Personal Library</a> &raquo; <a href='book.php?book={$_GET['book']}'>{$name['document_name']}</a> &raquo; Delete document";
        endblock();
        startblock('content');
            echo "<div class='main'>";
                echo "<div class='blockHeader'> <h2>Delete: {$name['document_name']}</h2></div>";
                echo "<div class='blockContent'>";
                    echo "You have requested to delete {$name['document_name']}. Are you really really really sure this is what you want?";
                    echo "<form method='post'>";
                        echo "<input type='hidden' name='book' value='{$_GET['book']}'/>";
                        echo "<input type='submit' name='confirmDelete' value='Yes, delete {$name['document_name']}'/>";
                        echo "<input type='submit' name='refuteDelete' value='No, I was mistaken'/>";
                    echo "</form>";
                echo "</div>";
            echo "</div>";
        endblock();
    } else if(isset($_REQUEST['refuteDelete'])) { # don't delete document
        header("location:book.php?book={$_GET['book']}");
    } elseif(isset($_REQUEST['confirmDelete'])) { # delete document
        $sql = "select * from PaperDoc where docID =?";
        $query = $con->prepare($sql);
        $query->execute(array($_GET['book']));
        $paperDoc = $query->fetch();
        if (isset($paperDoc['docID'])) {
            $sql = "delete from DocCategory where docID=?";
            $query = $con->prepare($sql);
            $query->execute(array($paperDoc['docID']));

            $sql ="delete from Loaning where docID=?";
            $query = $con->prepare($sql);
            $query->execute(array($paperDoc['docID']));

            $sql ="delete from PaperDoc where docID=?";
            $query = $con->prepare($sql);
            $query->execute(array($paperDoc['docID']));

            $sql ="delete from Document where docID=?";
            $query = $con->prepare($sql);
            $query->execute(array($paperDoc['docID']));
        } else { # Electronic document
            $sql = "delete from ElectronicDocCopies where docID=? and email=?";
            $query = $con->prepare($sql);
            $query->execute(array(str_replace('"','',$_REQUEST['book']), $_SESSION['email']));

            $sql = "select count(*) from ElectronicDocCopies where docID=?";
            $query = $con->prepare($sql);
            $query->execute(array(str_replace('"','',$_REQUEST['book'])));
            if ($query->fetchColumn()==0) { # single user
                $sql = "delete from ElectronicDoc where docID=?";
                $query = $con->prepare($sql);
                $query->execute(array(str_replace('"','',$_REQUEST['book'])));
                $sql = "delete from DocCategory where docID=?";
                $query = $con->prepare($sql);
                $query->execute(array(str_replace('"','',$_REQUEST['book'])));
                $sql = "delete from Document where docID=?";
                $query = $con->prepare($sql);
                $query->execute(array(str_replace('"','',$_REQUEST['book'])));
            }
        }
        header("location:personalLibrary.php");
    } else {

        #ERROR HANDLING
        #set session variables so the user doesn't have to fill these in when there is an error. unset them when info is stored or user closes the page with unsetSessionVar()
        $_SESSION['document_name'] = $_REQUEST['document_name'];
        $_SESSION['author'] = $_REQUEST['author'];
        $_SESSION['description'] = $_REQUEST['description'];
        $_SESSION['isbn'] = $_REQUEST['isbn'];
        if(!isset($_REQUEST['category'])) { # if category is empty it has to be specified as array, else it will go wrong when checking on categories later
            $_SESSION['category'] = array("");
        } else {
            $_SESSION['category'] = $_REQUEST['category'];
        }
        $_SESSION['visible'] = $_REQUEST['visible'];
        $_SESSION['state'] = $_REQUEST['state'];
        $_SESSION['distributable'] = $_REQUEST['distributable'];

        #invalid document name
        if(!isset($_REQUEST['document_name']) || $_REQUEST['document_name']=='' || ctype_space($_REQUEST['document_name'])) {
            $_SESSION['error'] = 'document_name';
            header("location:book.php?book={$_REQUEST['book']}");
            return 0;
        } elseif(!isset($_REQUEST['category']) || count($_REQUEST['category'])==0) {
            $_SESSION['error'] = 'category';
            header("location:book.php?book={$_REQUEST['book']}");
            return 0;
        }

        #UPDATE DOCUMENT
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
            $sql = "select count(*) from ElectronicDocCopies where docID=?";
            $query = $con->prepare($sql);
            $query->execute(array(str_replace('"','',$_GET['book'])));
            if ($query->fetchColumn()<=1) { # single user
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

        unsetSessionVar(); #unset session variables. They're not needed, the info is stored

        header("location:personalLibrary.php");
    }
} else { //method is GET
    $sql = "select *, PaperDoc.email as PDemail, ElectronicDocCopies.email as EDemail from Document left join PaperDoc on Document.docID=PaperDoc.docID left join ElectronicDocCopies on Document.docID=ElectronicDocCopies.docID where Document.docID=?";
    $query2 = $con->prepare($sql);
    $query2->execute(array($_GET['book']));
    $book = $query2->fetch();
    startblock('header');
        echo "<a href='index.php'>Home</a> &raquo; <a href='personalLibrary.php'>Personal Library</a> &raquo; {$book['document_name']}";
    endblock();

    startblock('content');

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
                    if (!$ownBook and !$book['visible']) {
                        echo "You are not allowed to view this document.";
                    } else {
                        echo "<form method='post'>";
                            echo "<h4>Name<hr/></h4>";
                            if ($ownBook) {
                                if(isset($_SESSION['error']) && $_SESSION['error']=='document_name') {
                                    echo "<div style='color: red' class='error'>Invalid document name</div>";
                                    unset($_SESSION['error']);
                                }
                                if(isset($_SESSION['document_name'])) {
                                    $document_name = $_SESSION['document_name'];
                                } else {
                                    $document_name = $book['document_name'];
                                }
                                echo "<input type='text' name='document_name' value='$document_name'/>";
                            } else {
                                echo $book['document_name'];
                            }
                            echo "<h4>Author<hr/></h4>";
                            if ($ownBook) {
                                if(isset($_SESSION['author'])) {
                                    $author = $_SESSION['author'];
                                } else {
                                    $author = $book['author'];
                                }
                                echo "<input type='text' name='author' value='$author'/>";
                            } else {
                                echo $book['author'];
                            }
                            echo "<h4>Description<hr/></h4>";
                            if ($ownBook) {
                                if(isset($_SESSION['description'])) {
                                    $description = $_SESSION['description'];
                                } else {
                                    $description = $book['description'];
                                }
                                echo "<textarea name='description'>$description</textarea>";
                            } else {
                                echo $book['description'];
                            }
                            echo "<h4>ISBN<hr/></h4>";
                            if ($ownBook) {
                                if(isset($_SESSION['isbn'])) {
                                    $isbn = $_SESSION['isbn'];
                                } else {
                                    $isbn = $book['isbn'];
                                }
                                echo "<input type='text' name='isbn' value='$isbn'/>";
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

                            if(isset($_SESSION['error']) && $_SESSION['error']=='category') {
                                echo "<div style='color: red' class='error'>At least one category has to be chosen</div>";
                                unset($_SESSION['error']);
                            }

                            $categories = array("action","fantasy","fiction","romance", "comedy","adventure", "non-fiction", "education", "religious","detective");
                            echo "<table border='0'>";
                                echo "<tr>";
                                    if(isset($_SESSION['category'])) {
                                        $q = $_SESSION['category'];
                                    } else {
                                        $q= $query->fetchAll();
                                    }
                                    for($i=0;$i<sizeof($categories);$i++) {
                                        if ($i%5==0) {
                                            echo "</tr><tr>";
                                        }
                                        $checked=False;
                                        foreach($q as $category) {
                                            if(isset($_SESSION['category']) && $categories[$i] == $category) { //if user has made an error get the previous categories
                                                $checked = True;
                                            } elseif(!isset($_SESSION['category']) && $categories[$i] == $category['category']) {
                                                $checked = True;
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
                                if(isset($_SESSION['visible'])) { #get info out previous entry
                                    if($_SESSION['visible']=='visible') {
                                        $visible = 1;
                                    } else {
                                        $visible = 0;
                                    }
                                } else { #get info out the table
                                    $visible = $book['visible'];
                                }
                                if ($visible) {
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

                                if(isset($_SESSION['state'])) {
                                    $state = $_SESSION['state'];
                                } else {
                                    $state = $paperDoc['state'];
                                }
                                echo "Current: ";
                                $options = array("","","","");
                                $values = array("new","good","decent","poor");
                                for($i=0;$i<sizeof($options);$i++) {
                                    if($state==$values[$i]) {
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
                                } else {
                                    echo $paperDoc['state'];
                                }
                                echo "<h4>Loaning<hr/></h4>";
                                $sql = "select * from Loaning where docID=? and start_date <= CURRENT_TIMESTAMP
                                    and end_date >= CURRENT_TIMESTAMP";
                                $query = $con->prepare($sql);
                                $query->execute(array($_GET['book']));
                                if ($query->rowCount()>=1) {
                                    echo "<font color='red'>This book has been lent out.</font>";
                                } else {
                                    if($ownBook) {
                                        echo "<a href='lendDocument.php?docID={$_GET['book']}'>lend Book to someone</a>";
                                    } else {
                                        echo "<a href='borrowDocument.php?docID={$_GET['book']}'>borrow Book </a>";
                                    }
                                }
                            } else { #electronic doc
                                $sql = "select * from ElectronicDoc where docID =?";
                                $query = $con->prepare($sql);
                                $query->execute(array($_GET['book']));
                                $electronicDoc = $query->fetch();
                                if ($ownBook) {
                                    echo "<h4>Distributable</h4>";
                                    if(isset($_SESSION['distributable'])) { #get info out previous entry
                                        if($_SESSION['distributable']=='distributable') {
                                            $distributable= 1;
                                        } else {
                                            $distributable = 0;
                                        }
                                    } else { #get info out the table
                                        $distributable = $electronicDoc['distributable'];
                                    }

                                    if ($distributable) {
                                        $distChecked = 'checked';
                                        $notDistChecked = '';
                                    } else {
                                        $distChecked = '';
                                        $notDistChecked = 'checked';
                                    }
                                    echo "<input type = 'radio' $distChecked name = 'distributable' value='distributable' />distributable<br/>";
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
                        if ($ownBook) {
                            echo "<form method='post' >";
                                echo "<input type='submit' name='delete' value='Delete Document'/>";
                            echo "</form>";
                        }
                        unsetSessionVar(); #unset session variables. They're not needed here and user could've closed the page.
                    }
                }
            echo "</div>";
        echo "</div>";
    endblock();
}

#unsets the session variables used for storing info when the document isn't updated yet
function unsetSessionVar() {
    unset($_SESSION['document_name']);
    unset($_SESSION['author']);
    unset($_SESSION['description']);
    unset($_SESSION['isbn']);
    unset($_SESSION['category']);
    unset($_SESSION['visible']);
    unset($_SESSION['state']);
    unset($_SESSION['distributable']);
}

?>