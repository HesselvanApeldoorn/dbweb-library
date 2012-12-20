<?php require '../../libConfig.php' ?>
<?php
    session_start();
    if  (isset($_SESSION["user"])) {
        header("location:index.php");
    }
    try {
        $con = new PDO("mysql:dbname=$db;host=$host", $username, $password);
    } catch(PDOException $e) {
        echo $e->getMessage();
    }
?>
<html>
    <head>
    </head>
    <body>

    <div>
        <?php
         if ($_SERVER['REQUEST_METHOD']=='POST') {
                $sql = "select count(*) from User where user_name=? AND password=?";
                $query = $con->prepare($sql);
                $query->execute(array( $_REQUEST['username'], hash('sha512', $_REQUEST['password'])));
                $correct_account = $query->fetchColumn();
                if ($correct_account ==0) {
                    echo "incorrect account credentials.<br/> <a href='login.php'>Retry</a>";
                    echo hash('sha512', $_REQUEST['password']);
                } else {
                    $_SESSION['user']=$_REQUEST['username'];
                    header("Location: index.php");
                }
        } else {
            echo "
            <div class='account'>
                <form method='post'>
                    <div class='username'>
                        Username: <input type='text' name='username' id='username'>
                    </div>
                    <div class='password'>
                        Password: <input type='password' name='password' id='password'>
                    </div>
                    <input type='submit' name='login' value='login'>
                </form>
                Not a registered user yet?
                <a href='register.php'>Register here</a>
            </div>";
        }
        ?>

    </div>
    </body>
</html>
