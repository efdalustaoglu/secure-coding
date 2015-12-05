 
<?php

define('BANK_APP', TRUE);

require_once "../app/user.php";

// process form
if (isset($_POST['submit'])) {
  $email = $_POST['email'];
  
  $response = rememberPassword($email);
  
  if (!empty($response->msg)) {
    $showMsg = $response->msg;
  }
}

// include header
$pageTitle = "Forgot Password";
include("header.php");

?>

<h3>Forgot Password</h3>
<form class="pure-form pure-form-aligned" method="post" action="<?php $_SERVER['PHP_SELF']; ?>">
  <fieldset>
    <div class="pure-control-group">
      <label>Email</label>
      <input name="email" type="email" placeholder="Email">
    </div>

    <div class="pure-controls">
      <button type="submit" name="submit" class="pure-button pure-button-primary">Submit</button>
    </div>
  </fieldset>
</form>

<?php 

// include footer
include("footer.php"); 

?>