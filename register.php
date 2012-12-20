<html>
    <head>
        <?php
            error_reporting(-1);
            ini_set("display_errors", 1);
            session_start();
            require_once("../../libConfig.php"); //$db=databaseName, $host=hostName, $user=userName, $pass=password.
        ?>
        <link href="static/css/base.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?php 
            try { //Setting up the database connection
                $con = new PDO("mysql:dbname=$db;host=$host", $username, $password);
            } catch (PDOException $e) {
                echo "Could not connect to database.";
                die();
            }
            if($_SERVER['REQUEST_METHOD']=='POST') {
                 if(!isset($_REQUEST['user_name']) || !isset($_REQUEST['password']) || !isset($_REQUEST['email']) || $_REQUEST['email']==''|| $_REQUEST['user_name']=='' || $_REQUEST['password']=='') {
                    echo "All the fields are required, <a href='register.php'> retry</a>";
                } else if($_REQUEST['password']!=$_REQUEST['rePassword']) {
                    echo "You didn't enter the same password twice, please <a href='register.php'> retry</a>";
                } else {
                    $sql = "select count(*) from User where email=?";
                    $q = $con->prepare($sql);
                    $q->execute(array($_REQUEST['email']));
                    if($q->fetchColumn()!=0) {
                       echo "Email already exists, <a href='register.php'> retry</a>";
                    } else {
                        if(!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
                            echo "Invalid email address, <a href='register.php'> retry</a>";
                        } else {
                            $sql = "INSERT INTO User (email,user_name,password) VALUES (?,?,?)";
                            $q = $con->prepare($sql);
                            $q->execute(array($_REQUEST['email'],$_REQUEST['user_name'], hash("sha512",$_REQUEST['password'])));
                            $confirm_code = md5(uniqid(rand()));
                            $_SESSION['confirm_code'] = $confirm_code;
                            $_SESSION['regEmail'] = $_REQUEST['email'];
                            $_SESSION['confirmed'] = false;
                            send_mail($_REQUEST['email'], $confirm_code);
                            echo "A confirmation link has been sent to your email account";
                        }
                            
                    }
                }
            } else {
                echo "<div class='account'>";
                    echo "<div class='accountHeader'>
                            <h2>Registration</h2>
                          </div>";
                    echo "<div class='accountContent'>";
                        echo "<form method = 'post'>";
                        echo "Email: <input type = 'text' name = 'email'/> <br/>";
                        echo "Username: <input type = 'text' name = 'user_name'/> <br/>";
                        echo "Password: <input type = 'password' name = 'password'/> <br/>";
                        echo "Retype password: <input type = 'password' name = 'rePassword'/> <br/>";
                        echo "<input type = 'submit' name = 'submit' value = 'Register'> <br/>
                            </form>";
                    echo "</div>";
                echo "</div>";
            }
        ?>
    </body>
</html>

<?php
function send_mail() {
    $message = "Dear {$_REQUEST['user_name']},\n\nTo activate your library account, please click on this link:\n";
    $url = str_replace(curPageName(),"",curPageUrl());
    
    $message .= $url . 'activate.php?email=' . urlencode($_REQUEST['email']) . "&confirm_code={$_SESSION['confirm_code']} \n\n Kind regards,\n\n The libdev team";
    mail($_REQUEST['email'], 'Registration Confirmation', $message, 'From:no-reply@libDev.com');

}

function curPageURL() {
 $pageURL = 'http://';
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

function curPageName() {
 return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
}
?>