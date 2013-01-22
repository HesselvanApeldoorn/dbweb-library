<?php require 'templates/base.php'
?>
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
                
                send_mailPasswordChange($user_name);
                
                header("location:settings.php?appliedPassword=updated_password");
            }
        }
    } elseif(isset($_REQUEST['submitDelete'])) { # the user wants to delete his/her account
        startblock('header');
            echo "<a href='index.php'>Home</a> &raquo; <a href='settings.php'>Settings</a> &raquo; Delete account";
        endblock(); 
        startblock('content');
            echo "<div class='main'>";
                echo "<div class='blockHeader'> <h2>Delete your account</h2></div>";
                echo "<div class='blockContent'>";
                    $sql = "select count(*) from User where email=? AND password=?";
                    $query = $con->prepare($sql);
                    $query->execute(array($_SESSION['email'], hash('sha512', $_REQUEST['password_delete'])));
                    if ($query->fetchColumn()==0) { #password-email combination does not exist in the database
                        header("location:settings.php?deletePassword=incorrect");
                    } else { #check and set new password                    
                        echo "You have requested to delete your account. Are you really really really sure this is what you want?";
                        echo "<form method='post'>";
                            echo "<input type='submit' name='submitConfirmDelete' value='Yes, delete my account'/>";
                            echo "<input type='submit' name='submitRefuteDelete' value='No, I was mistaken'/>";
                        echo "</form>";
                    }
                echo "</div>";
            echo "</div>";
        endblock();
    } elseif(isset($_REQUEST['submitConfirmDelete'])) {
        #Send a mail telling the account has been deleted
        $sql = "select user_name from User where email = ?";
        $query = $con->prepare($sql);
        $query->execute(array($_SESSION['email']));
        $user_name =  $query->fetchColumn();
        send_mailDeleteAccount($user_name);
        
        ##delete all references to the user before deleting the account##
        #delete preferred categories
        $sql = "delete from PreferredCategory where email=?";
        $query =$con->prepare($sql);
        $query->execute(array($_SESSION['email']));

        #delete electronic documents
        $sql = "select * from ElectronicDocCopies where email=?";
        $query =$con->prepare($sql);
        $query->execute(array($_SESSION['email']));
        
        foreach($query as $row) {
            $sql = "delete from ElectronicDocCopies where docID={$row['docID']} and email=?";
            $query2 = $con->prepare($sql);
            $query2->execute(array($_SESSION['email']));

            $sql = "select * from ElectronicDocCopies where docID={$row['docID']}";
            $query2 =$con->prepare($sql);
            $query2->execute();

            if($query2->rowCount()==0) {  #If there is no copy the document can be destroyed            
                $sql = "delete from ElectronicDoc where docID={$row['docID']}";
                $query3 =$con->prepare($sql);
                $query3->execute(); # delete from electronicdoc

                $sql = "delete from Document where docID={$row['docID']}";
                $query3 =$con->prepare($sql);
                $query3->execute();#delete from document
            }
        }
        
        #delete paper documents
        $sql = "delete from Loaning where fromUser=?";
        $query =$con->prepare($sql);
        $query->execute(array($_SESSION['email'])); # delete loanings

        $sql = "delete from Notification where email=?";
        $query =$con->prepare($sql);
        $query->execute(array($_SESSION['email'])); # delete notifications

        $sql = "select * from PaperDoc where email=?";
        $query =$con->prepare($sql);
        $query->execute(array($_SESSION['email']));
        foreach($query as $row) {
            echo "Doc id verwijdert: ".$row['docID'];
            $sql = "delete from DocCategory where docID={$row['docID']}";
            $query2 =$con->prepare($sql);
            $query2->execute();

            $sql = "delete from PaperDoc where docID={$row['docID']}";
            $query2 =$con->prepare($sql);
            $query2->execute();

            $sql = "delete from Document where docID={$row['docID']}";
            $query2 =$con->prepare($sql);
            $query2->execute();
        }

        #at last delete the user
        $sql = "delete from User where email=?";
        $query =$con->prepare($sql);
        $query->execute(array($_SESSION['email']));

        #logging out en redirecting to login with a message
        session_destroy(); 
        header("location:login.php?deleted=succes");

    } elseif(isset($_REQUEST['submitRefuteDelete'])) {
        header("location:index.php");
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
                    $categories = array("action","fantasy","fiction","romance", "comedy","adventure", "non-fiction", "education", "religious","detective");
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
                if(isset($_REQUEST['deletePassword']) && $_REQUEST['deletePassword']=='incorrect') {
                    echo "<div style='color: red' class='error'>The password is incorrect</div>";   
                }
                echo "<form method='post'>";
                    echo "<td>Enter password: </td><td><input type=password name='password_delete'/></td>";
                    echo "<input type='submit' name='submitDelete' value='Delete Account'/>";
                echo "</form>";

          echo "</div>";
      echo "</div>";

    endblock(); 
}

function send_mailPasswordChange($user_name) {
    $message = "Dear $user_name, \n\nYour password has been changed. If you ever lose your password you can ask for a new password on the following link:\n";

    $currentUrl = curPageURL() ."?"; #add a ? to the end otherwise the code below doesn't work if the user has made no error 
    $page = substr($currentUrl, 0, strpos($currentUrl,"?"));
    $url = str_replace(curPageName(),"",$page);

    $message .= $url . 'forgotPw.php?email='. urlencode($_SESSION['email'])." \n\n Kind regards,\n\n The libdev team";
    mail($_SESSION['email'], 'Password change', $message, 'From:no-reply@libDev.com');
}

function send_mailDeleteAccount($user_name) {
    $message = "Dear $user_name, \n\nYou have deleted your account. If you ever regret this decision, you can register again at:\n";

    $currentUrl = curPageURL() ."?"; #add a ? to the end otherwise the code below doesn't work if the user has made no error 
    $page = substr($currentUrl, 0, strpos($currentUrl,"?"));
    $url = str_replace(curPageName(),"",$page);

    $message .= $url . 'register.php?error=no_error&email='. urlencode($_SESSION['email']).'&user_name='.urlencode($user_name)." \n\n Kind regards,\n\n The libdev team";
    mail($_SESSION['email'], 'Account deletion', $message, 'From:no-reply@libDev.com');
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