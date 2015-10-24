<?php

require_once "../app/user.php";

// process form
if (isset($_POST['submit'])) {
  $email = $_POST['email'];
  $password = $_POST['password'];
  
  $login = login($email, $password);
  if ($login->value) {
    header("Location: "."view_transactions.php");
  } else {
    $showMsg = $login->msg;
  }
}

// include header
$pageTitle = "Login";
include("header.php");

?>

<h3>Login</h3>
<form class="pure-form pure-form-aligned" method="post" action="<?php $_SERVER['PHP_SELF']; ?>">
  <fieldset>
    <div class="pure-control-group">
      <label>Email</label>
      <input name="email" type="email" placeholder="Email">
    </div>

    <div class="pure-control-group">
      <label>Password</label>
      <input name="password" type="password" placeholder="Password">
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