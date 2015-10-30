<?php

define('BANK_APP', TRUE);

require_once "../app/user.php";
require_once "../app/transaction.php";

startSession();

// process form
if (isset($_POST['submit'])) {
  $recipient = $_POST['recipient'];
  $amount = $_POST['amount'];
  $tan = $_POST['tan'];
  $sender = getAccountById(getAuthUser()->userid)->id;
  
  $transaction = createTransaction($sender, $recipient, $amount, $tan);
  if ($transaction->value) {
    header("Location: "."view_transactions.php");
  } 

  if (!empty($transaction->msg)) {
    $showMsg = $transaction->msg;
  }
}

// process file
if (isset($_POST['file'])) {

}

// include header
$pageTitle = "Create Transaction";
include("header.php");

?>

<h3>Create Transaction</h3>
<form class="pure-form pure-form-aligned" method="post" action="<?php $_SERVER['PHP_SELF']; ?>">
  <fieldset>
    <div class="pure-control-group">
      <label>Recipient Account</label>
      <input name="recipient" type="text" placeholder="Recipient account">
    </div>

    <div class="pure-control-group">
      <label>Amount</label>
      <input name="amount" type="text" placeholder="Amount">
    </div>

    <div class="pure-control-group">
      <label>Tan</label>
      <input name="tan" type="text" placeholder="TAN">
    </div>

    <div class="pure-controls">
      <button type="submit" name="submit" class="pure-button pure-button-primary">Submit</button>
    </div>
  </fieldset>
</form>
<div class="divider"></div>
<form class="pure-form pure-form-aligned" method="post" action="<?php $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
<fieldset>
    <div class="pure-control-group">
      <label>Transaction file</label>
      <input name="file" type="file">
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