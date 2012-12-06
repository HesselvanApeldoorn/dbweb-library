<?php require 'templates/base.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php 
if ($_SERVER['REQUEST_METHOD']=='POST') {
    $sql = "insert into Document values(13,'ok','ok','ok',1,'23-45')";
    $query = $con->prepare($sql);
    $query->execute();
    header("location:library.php");
} else {
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
                    echo "<input type='text' value='{$book['document_name']}'/>";
                    echo "<hr/>";
                    echo "<h4>Author</h4>";
                    echo "<input type='text' value='{$book['author']}'/>";
                    echo "<hr/>";
                    echo "<h4>Description</h4>";
                    echo "<textarea>{$book['description']}</textarea>";
                    echo "<hr/>";
                    echo "<h4>Visibility</h4>";
                    if ($book['visible']) {
                        echo "<input type = 'radio' checked name = 'visible'>visible<br/>";
                        echo "<input type = 'radio' name = 'visible'>not visible";
                    } else {
                        echo "<input type = 'radio' name = 'visible'>visible<br/>";
                        echo "<input type = 'radio' checked name = 'visible'>not visible";
                    }
                    echo "<hr/>";
                    echo "<h4>ISBN</h4>";
                    echo "<input type='text' value='{$book['isbn']}'/>";
                    echo "<hr/>";
                    $sql = "select * from PaperDoc where docID =?";
                    $query = $con->prepare($sql);
                    $query->execute(array($_GET['book']));
                    $paperDoc = $query->fetch();
                    if (isset($paperDoc['docID'])) {
                        echo "<h4>Quality</h4>";
                        echo $paperDoc['state'];
                    } else {
                        $sql = "select * from ElectronicDoc where docID =?";
                        $query = $con->prepare($sql);
                        $query->execute(array($_GET['book']));
                        $electronicDoc = $query->fetch();
                        echo "<h4>Distributable</h4>";
                        if ($electronicDoc['distributable']) {
                            echo "<input type = 'radio' checked name = 'distributable'>distributable<br/>";
                            echo "<input type = 'radio' name = 'distributable'>not distributable";
                        } else {
                            echo "<input type = 'radio' name = 'distributable'>distributable<br/>";
                            echo "<input type = 'radio' checked name = 'distributable'>not distributable";
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