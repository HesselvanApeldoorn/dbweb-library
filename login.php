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
                    } else {
                        $_SESSION['email']=$_REQUEST['email'];
                        header("Location: index.php");
                    }
                } else {
                    header("Location: login.php?activate=not_activated&email={$_REQUEST['email']}");
                }
            } else {
                echo "
                <div class='accountContainer'>
                    <div class='account'>
                        <div class='accountHeader'>
                            <h2>Log in</h2>
                        </div>
                        <div class='accountContent'>";
                            if(isset($_REQUEST['activate']) && $_REQUEST['activate']=='success') {
                                echo "<div style='color: red' class='error'>Your account has been activated succesfully</div>";   
                            } else if(isset($_REQUEST['activate']) && $_REQUEST['activate']=='already_confirmed') {
                                echo "<div style='color: red' class='error'>Your account is already activated</div>";   
                            } else if(isset($_REQUEST['activate']) && $_REQUEST['activate']=='not_activated') {
                                echo "<div style='color: red' class='error'>Your account has not been activated yet. You should've received an activation email</div>";   
                            } else if(isset($_REQUEST['newpass']) && $_REQUEST['newpass']==1) {
                                echo "<div style='color: red' class='error'>Your new password is sent to your email address</div>";
                            } else if (isset($_REQUEST['email'])) {
                                echo "<div style='color: red' class='error'>Incorrect account credentials</div>";
                            }
                            echo "<form method='post'>";
                                echo "<table id='nonborder'";
                                    echo "<tr>";
                                    if(isset($_REQUEST['email'])) {
                                        echo "<td>E-mail:</td><td> <input type='text' name='email' id='email' value='{$_REQUEST['email']}' /></td> ";
                                    } else {
                                        echo "<td>E-mail:</td><td> <input type='text' name='email' id='email'></td>";
                                    }
                                    echo "</tr>";
                                    echo "<tr>
                                        <td>Password:</td><td> <input type='password' name='password' id='password'</td>
                                    </tr>";
                                    echo "<tr><td><input type='submit' name='login' id='login' value='login' /></td></tr>";
                                echo "</table>";
                            echo "</form>";
                        echo "</div>";
                    echo "</div>
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
