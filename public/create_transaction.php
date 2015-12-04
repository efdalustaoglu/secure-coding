<?php

define('BANK_APP', TRUE);

require_once "../app/user.php";
require_once "../app/transaction.php";

startSession(true);

//CSRF
if (!isset($_POST['submit']) && !isset($_POST['upload'])) {
  create_CSRF_token();
}

// process form
if (isset($_POST['submit']) && isset($_SESSION['CSRF_token']) && $_POST['CSRF_token'] == $_SESSION['CSRF_token']) {
  $recipient = $_POST['recipient'];
  $amount = $_POST['amount'];
  $tan = $_POST['tan'];
  get_db_credentials(getAuthUser()->usertype);
  $sender = selectAccountByUserId(getAuthUser()->userid)->ACCOUNT_NUMBER;
  
  $transaction = createTransaction($sender, $recipient, $amount, $tan);
  if ($transaction->value) {
    unset($_SESSION['CSRF_token']);
    header("Location: "."view_transactions.php");
  } 

  if (!empty($transaction->msg)) {
    $showMsg = $transaction->msg;
  }
}

// process file
if (isset($_POST['upload'])) {
  $upload = uploadTransactionFile();

  if ($upload->value) {
    // execute C program
    $program = realpath("../app/file_parser");
    $program_directory = substr($program, 0, strrpos($program, "/"));
    chdir($program_directory);
    $command = "./file_parser ".$upload->value." ".DB_USER." ".DB_PASSWORD." ".DB_NAME;
    $output = shell_exec($command);
    unlink($upload->value);
    
    if ((int)$output === 0) {
		$showMsg = "Transaction successful";
	} else {
		$showMsg = "Transaction failed with error code: ".$output;
	}

  } else {
    $showMsg = $upload->msg;
  }
}

// include header
$pageTitle = "Create Transaction";
include("header.php");

?>

<h3>Create Transaction</h3>
<form class="pure-form pure-form-aligned" method="post" action="<?php $_SERVER['PHP_SELF']; ?>">
  <input type="hidden" name="CSRF_token" id="CSRF_token" value="<?php echo $_SESSION['CSRF_token'] ?>" />
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
      <button type="submit" name="upload" class="pure-button pure-button-primary">Submit</button>
    </div>
  </fieldset>
</form>

<?php 

// include footer
include("footer.php"); 

?>
