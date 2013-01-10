<?php require 'templates/base.php' ?>
<link href="static/css/base.css" rel="stylesheet" type="text/css">
<?php 
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_REQUEST['applyCategory'])) {
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
    header("location:settings.php?appliedCategory=1");
} else if($_SERVER['REQUEST_METHOD']=='POST' && isset($_REQUEST['applyPassword'])) {
    $sql = "select count(*) from User where email=? AND password=?";
    $query = $con->prepare($sql);
    $query->execute(array($_SESSION['email'], hash('sha512', $_REQUEST['oldPassword'])));
    $correct_password = $query->fetchColumn();
    if ($correct_password ==0) {
        header("location:settings.php?appliedPassword=incorrect");
    } else {
        #check and set new password
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
              if(isset($_REQUEST['appliedCategory']) && $_REQUEST['appliedCategory']==1) {
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
                  echo "<input type='submit' name='applyCategory' value='Apply changes'/>";
              echo "</form>";
              
              echo "<h3>Change Password<hr/></h3>";
              if(isset($_REQUEST['appliedPassword']) && $_REQUEST['appliedPassword']=='incorrect') {
                  echo "<div style='color: red' class='error'>The current password is incorrect</div>";   
              } else if(isset($_REQUEST['appliedPassword']) && $_REQUEST['appliedPassword']=='invalid') {
                  echo "<div style='color: red' class='error'>Password should be at least 8 characters long, contain at least 1 capital, 1 lower case letter and 1 digit. Special characters aren't allowed</div>";
              } else if(isset($_REQUEST['appliedPassword']) && $_REQUEST['appliedPassword']=='different_password') {
                  echo "<div style='color: red' class='error'>Passwords are different</div>";
              }
              echo "<form method='post'>";
                  echo "<table id='nonborder'";
                      echo "<tr>";
                          echo "<td>Current password: </td><td><input type=password name=oldPassword/></td>";
                      echo "</tr>";   
                      echo "<tr>";
                          echo "<td>New password: </td><td><input type=password name=newPassword/></td>";
                      echo "</tr>";
                      echo "<tr>";
                          echo "<td>Retype password: </td><td><input type=password name=retypePassword/></td>";
                      echo "</tr>";
                  echo "</table>";
                  echo "<input type='submit' name='applyPassword' value='Apply changes'/>";
              echo "</form>";
          echo "</div>";
      echo "</div>";

    endblock(); 
}
?>
