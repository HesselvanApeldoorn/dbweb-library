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
                if(!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
                    header("Location: forgotPw.php?error=invalid&email={$_REQUEST['email']}");
                } else {
                    $sql = "select count(*) from User where email=?";
                    $q = $con->prepare($sql);
                    $q->execute(array($_REQUEST['email']));
                    if($q->fetchColumn()==0) { #email doens't exist in the database
                        header("Location: forgotPw.php?error=not_existing&email={$_REQUEST['email']}");
                    } else {#new pass will be created and sent to the user
                        $newPass = substr(base64_encode(rand(1000000000,9999999999)),0,10);
                        $sql = "update User set password=? where email=?";
                        $q = $con->prepare($sql);
                        $q->execute(array(hash('sha512',$newPass),$_REQUEST['email']));
                        $sql = "select user_name from User where email=?";
                        $q = $con->prepare($sql);
                        $q->execute(array($_REQUEST['email']));
                        send_mail($newPass,$q->fetchColumn());
                        header("Location: login.php?newpass=1&email={$_REQUEST['email']}");
                    }
                }
            } else {
                echo "<div class='accountContainer'>";
                    echo "<div class='account'>";
                        echo "<div class='accountHeader'>
                                <h2>Retrieve password</h2>
                              </div>";
                        echo "<div class='accountContent'>";
                            if(isset($_REQUEST['error'])) {
                                if($_REQUEST['error']=='invalid') {
                                    echo "<div style='color: red' class='error'>Invalid email</div>";
                                } elseif($_REQUEST['error']=='not_existing') {
                                    echo "<div style='color: red' class='error'>Email doesn't exist</div>";
                                }
                            }
                            echo "Fill in the email address to which the new password must be send";
                            echo "<form method = 'post'>";
                                if(isset($_REQUEST['email'])) {
                                    echo "Email: <input type = 'text' name = 'email' value='{$_REQUEST['email']}'/> <br/>";
                                } else {
                                    echo "Email: <input type = 'text' name = 'email'/> <br/>";
                                }
                                echo "<input type = 'submit' name = 'submit' value = 'Send mail'> <br/>";
                            echo "</form>";
                        echo "</div>";
                    echo "</div>";
                echo "</div>";
            }
        ?>
    </body>
</html>

<?php
function send_mail($newPass, $user_name) {
    $message = "Dear $user_name,\n\nThis is your new password:\n$newPass\n\n";
    $url = str_replace(curPageName(),"",curPageUrl());

    $message .= "Now back to the library:\n". $url . "\n\n Kind regards,\n\n The libdev team";
    mail($_REQUEST['email'], 'New password', $message, 'From:no-reply@libDev.com');
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