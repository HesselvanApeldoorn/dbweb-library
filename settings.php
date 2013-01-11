<?php require 'templates/base.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php 
if($_SERVER['REQUEST_METHOD']=='POST') { # a submit button has been clicked
    if(isset($_REQUEST['submitCategory'])) { # the user changed the preferred categories
        #Delete every category related to the current document
        $sql = "delete from PreferredCategory where email=?";
        $query = $con->prepare($sql);
        $query->execute(array($_SESSION['email']));
        
        #insert new categories to the current document
        $categories = $_REQUEST['category'];
        foreach($categories as $category) {
            $sql = "insert into PreferredCategory values(?,?)";
            $query = $con->prepare($sql);
            $query->execute(array($_SESSION['email'], $category));
        }
        header("location:settings.php?appliedCategory=updated");
    } elseif(isset($_REQUEST['submitPassword'])) { # the user changed the password
        $sql = "select count(*) from User where email=? AND password=?";
        $query = $con->prepare($sql);
        $query->execute(array($_SESSION['email'], hash('sha512', $_REQUEST['oldPassword'])));
        if ($query->fetchColumn()==0) { #password-email combination does not exist in the database
            header("location:settings.php?appliedPassword=incorrect");
        } else { #check and set new password
            if(!passRequirements($_REQUEST['newPassword'])) { #check if the password meets the requirements
                header("location:settings.php?appliedPassword=invalid");
            } elseif($_REQUEST['newPassword']!=$_REQUEST['retypePassword']) { # the same password is not typed twice
                header("location:settings.php?appliedPassword=different_password");
            } else { #Update password and mail the user
                #Password update
                $sql = "update User set password=? where email=?";
                $query = $con->prepare($sql);
                $query->execute(array(hash('sha512',$_REQUEST['newPassword']),$_SESSION['email']));
                
                #Send a mail telling the password is changed
                $sql = "select user_name from User where email = ?";
                $query = $con->prepare($sql);
                $query->execute(array($_SESSION['email']));
                $user_name =  $query->fetchColumn();
                send_mail($user_name);
                
                header("location:settings.php?appliedPassword=updated_password");
            }
        }
    }
} else {
    startblock('header');
        echo "<a href='index.php'>Home</a> &raquo; Settings";
    endblock(); 
    startblock('content');
        echo "<div class='main'>";
            echo "<div class='blockHeader'> <h2>Settings</h2></div>";
            echo "<div class='blockContent'>";
                echo "<h3>Preferred categories<hr/></h3>";
                if(isset($_REQUEST['appliedCategory']) && $_REQUEST['appliedCategory']=='updated') {
                    echo "<div style='color: red' class='error'>The settings are saved</div>";   
                }
                echo "<form method='post'>";
                    $query = $con->prepare("select category from PreferredCategory where email=?");
                    $query->execute(array($_SESSION['email']));
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
                                echo "<td><input type = 'checkbox' $checkString name = 'category[]' value='{$categories[$i]}' />$categories[$i]</td>";
                            }
                        echo "</tr>";
                    echo "</table>";
                    echo "<input type='submit' name='submitCategory' value='Apply changes'/>";
                echo "</form>";
                
                echo "<h3>Change Password<hr/></h3>";
                if(isset($_REQUEST['appliedPassword'])) {
                    if($_REQUEST['appliedPassword']=='incorrect') {
                        echo "<div style='color: red' class='error'>The current password is incorrect</div>";   
                    } elseif($_REQUEST['appliedPassword']=='invalid') {
                        echo "<div style='color: red' class='error'>Password should be at least 8 characters long, contain at least 1 capital, 1 lower case letter and 1 digit. Special characters aren't allowed</div>";
                    } elseif($_REQUEST['appliedPassword']=='different_password') {
                        echo "<div style='color: red' class='error'>Passwords are different</div>";
                    } elseif($_REQUEST['appliedPassword']=='updated_password') {
                        echo "<div style='color: red' class='error'>Your password is changed succesfully</div>";
                    }
                }
                echo "<form method='post'>";
                    echo "<table id='nonborder'";
                        echo "<tr>";
                            echo "<td>Current password: </td><td><input type=password name='oldPassword'/></td>";
                        echo "</tr>";   
                        echo "<tr>";
                            echo "<td>New password: </td><td><input type=password name='newPassword'/></td>";
                        echo "</tr>";
                        echo "<tr>";
                            echo "<td>Retype password: </td><td><input type=password name='retypePassword'/></td>";
                        echo "</tr>";
                    echo "</table>";
                    echo "<input type='submit' name='submitPassword' value='Apply changes'/>";
                echo "</form>";
                
                echo "<h3>Delete account<hr/></h3>";
                # all references to the current user have to be removed. Also the user has to enter his/her password before removing and asked: are you sure ...?

          echo "</div>";
      echo "</div>";

    endblock(); 
}

function send_mail($user_name) {
    $message = "Dear $user_name, \n\nYour password has been changed. If you ever lose your password you can ask for a new password on the following link:\n";
    $url = str_replace(curPageName(),"",curPageUrl());

    $message .= $url . 'forgotPw.php?email='. urlencode($_SESSION['email'])." \n\n Kind regards,\n\n The libdev team";
    mail($_SESSION['email'], 'Password change', $message, 'From:no-reply@libDev.com');
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