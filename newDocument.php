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
    $_SESSION['docType'] = $_REQUEST['docType'];
    if(!isset($_REQUEST['category']) || count($_REQUEST['category'])==0) {
        $_SESSION['error'] = 'category';
        header("location:newDocument.php");
        return 0;
    } elseif($_REQUEST['docType']=='paper' && (!isset($_REQUEST['document_name']) || $_REQUEST['document_name']=='' || ctype_space($_REQUEST['document_name']))) {#invalid document name
        $_SESSION['error'] = 'document_name';
        header("location:newDocument.php");
        return 0;
    }
    #ADD DOCUMENT
    if ($_REQUEST['visible']=='visible') {
        $visible=1;
    } else {
        $visible=0;
    }

    if ($_REQUEST['docType']=='paper') {  # paper document
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
        if($_FILES['content']['size'] > 0 and $_FILES['content']['size'] < 20000000) {
            $fileName = $_FILES['content']['name'];
            $tmpName  = $_FILES['content']['tmp_name'];
            $fileSize = $_FILES['content']['size'];
            $fileType = $_FILES['content']['type'];

            $allowed_exts = array('', '7z','aiff','asf','avi','bmp','csv','doc','fla','flv','gif','gz','gzip','jpeg','jpg','mid','mov','mp3','mp4',
                'mpc','mpeg','mpg','odp','ods','odt','pdf','png','ppt','pxd','qt','ram','rar','rm','rmi','rmvb','rtf','sdc','sitd','swf','sxc',
                'sxw','tar','tgz','tif','tiff','txt','vsd','wav','wma','wmv','xls','xml','zip');
            $ext = end(explode('.', $fileName));
            if (!in_array($ext,$allowed_exts)) {
                $_SESSION['error'] = 'false_extension';
                header("location:newDocument.php");
                return 0;
            }
            $fp      = fopen($tmpName, 'r');
            $content = fread($fp, filesize($tmpName));

        } else { #there is no file
            $_SESSION['error'] = 'no_document';
            header("location:newDocument.php");
            return 0;
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

    unsetSessionVar(); #the session variables aren't needed anymore, because the document is made
    header("location:personalLibrary.php");
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
                    if(isset($_SESSION['author'])) {
                        $author = $_SESSION['author'];
                    } else {
                        $author = '';
                    }
                    echo "<input type='text' name='author' value='$author'/>";
                    echo "<h4>Description<hr/></h4>";
                    if(isset($_SESSION['description'])) {
                        $description = $_SESSION['description'];
                    } else {
                        $description = '';
                    }
                    echo "<textarea name='description'>$description</textarea>";
                    echo "<h4>ISBN<hr/></h4>";
                    if(isset($_SESSION['isbn'])) {
                        $isbn = $_SESSION['isbn'];
                    } else {
                        $isbn = '';
                    }
                    echo "<input type='text' name='isbn' value='$isbn'/>";
                    echo "<h4>Categories<hr/></h4>";
                    if(isset($_SESSION['error']) && $_SESSION['error']=='category') {
                        echo "<div style='color: red' class='error'>At least one category has to be chosen</div>";
                        unset($_SESSION['error']);
                    }
                    $categories = array("action","fantasy","fiction","romance", "comedy","adventure", "non-fiction", "education", "religious","detective");
                    echo "<table border='0'>";
                        echo "<tr>";
                            for($i=0;$i<sizeof($categories);$i++) {
                                if ($i%5==0) {
                                    echo "</tr><tr>";
                                }
                                if(!isset($_SESSION['category'])) { # no previous user input-> all checkboxes are unchecked
                                    echo "<td><input type = 'checkbox' name = 'category[]' value='{$categories[$i]}' />$categories[$i]</td>";
                                } else { # user has made an error -> Get all categories previously filled in
                                    $checked=False;
                                    foreach($_SESSION['category'] as $category) {
                                        if($categories[$i] == $category) { //if user has made an error get the previous categories
                                            $checked = True;
                                        }
                                    }
                                    if($checked) {
                                      $checkString = 'checked';
                                    } else {
                                      $checkString = '';
                                    }
                                    echo "<td><input type = 'checkbox' $checkString name = 'category[]' value='{$categories[$i]}' />$categories[$i]</td>";
                                }
                            }
                        echo "</tr>";
                    echo "</table>";

                    echo "<h4>Visibility<hr/></h4>";
                    if(isset($_SESSION['visible'])) { #get info out previous entry
                        if($_SESSION['visible']=='visible') {
                            $visible = 1;
                        } else {
                            $visible = 0;
                        }
                    } else { #no previous info=default is visible
                        $visible = 1;
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

                    echo "<h4>Type of document<hr/></h4>";
                    if(isset($_SESSION['docType'])) {
                        if($_SESSION['docType']=='paper') { # previous was paper
                            $paperChecked = 'checked';
                            $electronicChecked = '';
                            $paperDisplay = 'block'; #paper on
                            $electronicDisplay = 'none';
                        } else { # previous was electronic
                            $paperChecked = '';
                            $electronicChecked = 'checked';
                            $paperDisplay = 'none';
                            $electronicDisplay = 'block'; #electronic on
                        }
                    } else { # no previous info = default settings
                        $paperChecked = 'checked';
                        $electronicChecked = '';
                        $paperDisplay = 'block';
                        $electronicDisplay = 'none';
                    }
                    echo "<input type = 'radio' $paperChecked name = 'docType' onclick='enable(1);' value='paper' />paper<br/>";
                    echo "<input type = 'radio' $electronicChecked name = 'docType' onclick='enable(2);' value='electronic' />electronic<br/>";
                    echo "<div id='paper' style='display: $paperDisplay'>";
                        echo "<h4>Name<hr/></h4>";
                        if(isset($_SESSION['error']) && $_SESSION['error']=='document_name') {
                            echo "<div style='color: red' class='error'>Invalid document name</div>";
                            unset($_SESSION['error']);
                        }
                        if(isset($_SESSION['document_name'])) {
                            $document_name = $_SESSION['document_name'];
                        } else {
                            $document_name = '';
                        }
                        echo "<input type='text' name='document_name' value='$document_name'/>";
                        echo "<h4>Quality<hr/></h4>";
                        if(isset($_SESSION['state'])) {
                            $state = $_SESSION['state'];
                        } else {
                            $state = '';
                        }
                        $options = array("","","","");
                        $values = array("new","good","decent","poor");
                        for($i=0;$i<sizeof($options);$i++) {
                            if($state==$values[$i]) {
                                $options[$i] =  "selected='selected'";
                            }
                        }
                        echo "<select name='state'>";
                            echo "<option $options[0] value='new'>new</option>";
                            echo "<option $options[1] value='good'>good</option>";
                            echo "<option $options[2] value='decent'>decent</option>";
                            echo "<option $options[3] value='poor'>poor</option>";
                        echo "</select>";
                        echo "<p><input type='submit' value='Add Document'/></p>";
                    echo "</div>";
                    echo "<div id='electronic' style='display: $electronicDisplay'>";
                        echo "<h4>Distributable<hr/></h4>";
                        if(isset($_SESSION['distributable'])) { #get info out previous entry
                            if($_SESSION['distributable']=='distributable') {
                                $distributable= 1;
                            } else {
                                $distributable = 0;
                            }
                        } else { #no previous info=default
                            $distributable = 1;
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
                        echo "<h4>Content<hr/></h4>";
                        if(isset($_SESSION['error']) && $_SESSION['error']=='no_document') {
                            echo "<div style='color: red' class='error'>You did not upload a file</div>";
                            unset($_SESSION['error']);
                        } elseif (isset($_SESSION['error']) && $_SESSION['error']=='false_extension') {
                            echo "<div style='color: red' class='error'>This extension is not allowed</div>";
                            unset($_SESSION['error']);
                        }
                        echo "<input type='hidden' name='MAX_FILE_SIZE' value='20000000'>";
                        echo "<input type='file' name='content' id='file'><br>";
                        echo "<p><input type='submit' value='Add Document'/></p>";
                    echo "</div>";
                echo "</form>";
                unsetSessionVar(); #unset session variables. They're not needed here and user could've closed the page.

            echo "</div>";
        echo "</div>";
    endblock();
}

#unsets the session variables used for storing info when the document isn't stored yet
function unsetSessionVar() {
    unset($_SESSION['document_name']);
    unset($_SESSION['author']);
    unset($_SESSION['description']);
    unset($_SESSION['isbn']);
    unset($_SESSION['category']);
    unset($_SESSION['visible']);
    unset($_SESSION['state']);
    unset($_SESSION['distributable']);
    unset($_SESSION['docType']);
    unset($_SESSION['false_extension']);
}

?>