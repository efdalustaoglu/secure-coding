<?php
$page_title = "Login";

// include header
include("header.php");
?>

<!-- content -->

<h3>Login</h3>
<form class="pure-form pure-form-aligned">
  <fieldset>
    <div class="pure-control-group">
      <label for="email">Email</label>
      <input id="email" type="email" placeholder="Email">
    </div>

    <div class="pure-control-group">
      <label for="password">Password</label>
      <input id="password" type="password" placeholder="Password">
    </div>

    <div class="pure-controls">
      <button type="submit" class="pure-button pure-button-primary">Submit</button>
    </div>
  </fieldset>
</form>

<?php 
// include footer
include("footer.php"); 
?>