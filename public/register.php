<?php

define('BANK_APP', TRUE);

require_once "../app/user.php";

// process form
if (isset($_POST['submit'])) {
  $email = $_POST['email'];
  $firstname = $_POST['firstname'];
  $lastname = $_POST['lastname'];
  $password = $_POST['password'];
  $confirmPassword = $_POST['confirm_password'];
  $usertype = $_POST['usertype'];
  
  $register = createUser($usertype, $email, $password, $confirmPassword, $firstname, $lastname);

  if (!empty($register->msg)) {
    $showMsg = $register->msg;
  }
}

// include header
$pageTitle = "Register";
include("header.php");

?>

<h3>Register</h3>
<form class="pure-form pure-form-aligned" method="post" action="<?php $_SERVER['PHP_SELF']; ?>">
  <fieldset>
    <div class="pure-control-group">
      <label>First name</label>
      <input name="firstname" type="text" placeholder="First name">
    </div>

    <div class="pure-control-group">
      <label>Last name</label>
      <input name="lastname" type="text" placeholder="Last name">
    </div>

    <div class="pure-control-group">
      <label>Email</label>
      <input name="email" type="email" placeholder="Email">
    </div>

    <div class="pure-control-group">
      <label>User type</label>
      <select name="usertype">
        <option value="C">Client</option>
        <option value="E">Employee</option>
      </select>
    </div>

    <div class="pure-control-group">
      <label>Password</label>
      <input name="password" type="password" placeholder="Password">
    </div>

    <div class="pure-control-group">
      <label>Confirm password</label>
      <input name="confirm_password" type="password" placeholder="Type password again">
    </div>

    <div class="pure-controls">
      <button type="submit" name="submit" class="pure-button pure-button-primary">Submit</button>
    </div>

    <div class="g-recaptcha" data-sitekey="6Ldi-BETAAAAAN_yWLFXc-M6_9YNj5K141pNpI4t"></div>
    
  </fieldset>
</form>

<?php 

// include footer
include("footer.php"); 

?>