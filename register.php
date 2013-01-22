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
                unset($_SESSION['error']);
                $sql = "select count(*) from User where email=?";
                $q = $con->prepare($sql);
                $q->execute(array($_REQUEST['email']));
                if($q->fetchColumn()!=0) {#email already exists
                    $_SESSION['error'] = 'existing_email';
                    header("Location: register.php?email={$_REQUEST['email']}&user_name={$_REQUEST['user_name']}");
                    return 0;
                } elseif (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) { #validate email
                    $_SESSION['error'] = 'invalid_email';
                    header("Location: register.php?email={$_REQUEST['email']}&user_name={$_REQUEST['user_name']}");
                    return 0;
                } elseif (!isset($_REQUEST['user_name']) || $_REQUEST['user_name']=='' || ctype_space($_REQUEST['user_name'])) {
                    $_SESSION['error'] = 'invalid_username';
                    header("Location: register.php?email={$_REQUEST['email']}&user_name={$_REQUEST['user_name']}");
                    return 0;
                } elseif (!passRequirements($_REQUEST['password'])) { #password requirements
                    $_SESSION['error'] = 'invalid_password';
                    header("Location: register.php?email={$_REQUEST['email']}&user_name={$_REQUEST['user_name']}");
                    return 0;
                } elseif($_REQUEST['password']!=$_REQUEST['rePassword']) { #the password is not typed twice
                    $_SESSION['error'] = 'different_password';
                    header("Location: register.php?email={$_REQUEST['email']}&user_name={$_REQUEST['user_name']}");
                    return 0;
                } else {
                    $sql = "INSERT INTO User (email,user_name,password) VALUES (?,?,?)";
                    $q = $con->prepare($sql);
                    $q->execute(array($_REQUEST['email'],$_REQUEST['user_name'], hash("sha512",$_REQUEST['password'])));
                    $confirm_code = md5(uniqid(rand()));
                    $_SESSION['confirm_code'] = $confirm_code;
                    $_SESSION['regEmail'] = $_REQUEST['email'];
                    $_SESSION['confirmed'] = false;
                    $_SESSION['confirm_time'] = time();
                    send_mail();
                    echo "A confirmation link has been sent to your email account";
                }    
            } else {
                echo "<div class='accountContainer'>";
                    echo "<div class='account'>";
                        echo "<div class='accountHeader'>
                                <h2>Registration</h2>
                              </div>";
                        echo "<div class='accountContent'>";
                            if(isset($_SESSION['error'])) {
                                if($_SESSION['error']=='existing_email') {
                                    echo "<div style='color: red' class='error'>Email already exists</div>";
                                } elseif($_SESSION['error']=='invalid_email') {
                                    echo "<div style='color: red' class='error'>Invalid email</div>";
                                } elseif($_SESSION['error']=='invalid_username') {
                                    echo "<div style='color: red' class='error'>Invalid username</div>";
                                } elseif($_SESSION['error']=='invalid_password') {
                                    echo "<div style='color: red' class='error'>Password should be at least 8 characters long, contain at least 1 capital, 1 lower case letter and 1 digit. Special characters aren't allowed</div>";
                                } elseif($_SESSION['error']=='different_password') {
                                    echo "<div style='color: red' class='error'>Passwords are different</div>";
                                }
                                unset($_SESSION['error']);
                            }
                            echo "<form method = 'post'>";
                                echo "<table id='nonborder'";
                                        echo "<tr id='nonborder'>";
                                            if(isset($_REQUEST['email'])) {
                                                echo "<td>Email:</td><td> <input type = 'text' name = 'email' value='{$_REQUEST['email']}' /> <br/></td>";
                                            } else {
                                                echo "<td>Email:</td><td> <input type = 'text' name = 'email'/> <br/></td>";
                                            }
                                        echo "</tr>";
                                        echo "<tr>";
                                            if(isset($_REQUEST['user_name'])) {
                                                echo "<td>Username:</td><td> <input type = 'text' name = 'user_name' value='{$_REQUEST['user_name']}'/> <br/></td>";
                                            } else {
                                                echo "<td>Username:</td><td> <input type = 'text' name = 'user_name'/> <br/></td>";
                                            }
                                        echo "</tr>";
                                        echo "<tr>";
                                            echo "<td>Password:</td><td> <input type = 'password' name = 'password'/> <br/></td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                            echo "<td>Retype password:</td><td> <input type = 'password' name = 'rePassword'/> <br/></td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                            echo "<td><input type = 'submit' name = 'submit' value = 'Register'> <br/></td>";
                                        echo "</tr>";
                                echo "</table>";
                            echo "</form>";
                        echo "</div>";
                    echo "</div>";
                echo "</div>";
            }
        ?>
    </body>
</html>

<?php
function send_mail() {
    $message = "Dear {$_REQUEST['user_name']},\n\nTo activate your library account, please click on this link:\n";
    $currentUrl = curPageURL() ."?"; #add a ? to the end otherwise the code below doesn't work if the user has made no error 
    $page = substr($currentUrl, 0, strpos($currentUrl,"?"));
    $url = str_replace(curPageName(),"",$page);

    $message .= $url . 'activate.php?email=' . urlencode($_REQUEST['email']) . "&confirm_code={$_SESSION['confirm_code']} \n This link will be active for one hour. When the link expires you have to register again. \n\n Kind regards,\n\n The libdev team";
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

function passRequirements($password) {
    if(strlen($password)<8) {
        return false;
    } else {
        $upper=false;
        $lower=false;
        $digit=false;
        for($i=0; $i<strlen($password); $i++) {
            if (ctype_upper($password[$i])) {
                $upper=true;
            } else if (ctype_lower($password[$i])) {
                $lower=true;
            } else if (ctype_digit($password[$i])) {
                $digit=true;
            } else { // Special characters not allowed
                return false;
            }
        }
        if($digit and $lower and $upper) {
            return true;
        }
        return false;
    }
}
?>