<?php require '../../libConfig.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php
    session_start();
    if  (isset($_SESSION["email"])) {
        header("location:index.php");
    }
    try {
        $con = new PDO("mysql:dbname=$db;host=$host", $username, $password);
    } catch(PDOException $e) {
        echo "Could not connect to database.";
        die();
    }
?>
<html>
    <head>
    </head>
    <body>

        <div>
            <?php
            if ($_SERVER['REQUEST_METHOD']=='POST') {
               if(!isset($_SESSION['confirmed']) || $_SESSION['confirmed']==true) { 
                    unset($_SESSION['confirmed']);
                    $sql = "select count(*) from User where email=? AND password=?";
                    $query = $con->prepare($sql);
                    $query->execute(array( $_REQUEST['email'], hash('sha512', $_REQUEST['password'])));
                    $correct_account = $query->fetchColumn();
                    if ($correct_account ==0) {
                        header("Location: login.php?email={$_REQUEST['email']}");
                        echo "incorrect account credentials.<br/> <a href='login.php'>Retry</a>";
                    } else {
                        $_SESSION['email']=$_REQUEST['email'];
                        header("Location: index.php");
                    }
                }
            } else {
                echo "
                <div class='accountContainer'>
                    <div class='account'>
                        <div class='accountHeader'>
                            <h2>Log in</h2>
                        </div>
                        <div class='accountContent'>";
                            if (isset($_REQUEST['email'])) {
                                echo "<div style='color: red' class='error'>Incorrect account credentials</div>";
                            }
                            echo "<form method='post'>
                                <div class='email'>";
                                if(isset($_REQUEST['email'])) {
                                        echo "E-mail: <input type='text' name='email' id='email' value={$_REQUEST['email']}> ";
                                } else {
                                        echo "E-mail: <input type='text' name='email' id='email'>";
                                    }
                                echo "</div>";
                                echo "
                                <div class='password'>
                                    Password: <input type='password' name='password' id='password'>
                                </div>";
                                echo "<input type='submit' name='login' id='login' value='login' />
                            </form>
                        </div>
                    </div>
                    <div style='clear:both; float: right' >
                        <a href='register.php'>Register</a>
                        <a href='forgotPw.php'>Forgot password?</a>
                    </div>
                </div>";
            }
            ?>

        </div>
    </body>
</html>
