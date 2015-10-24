<?php

define('BANK_APP', TRUE);

require_once "../app/user.php";
require_once "../app/transaction.php";

startSession();

// process form
if (isset($_POST['approve'])) {
  $id = $_POST['transactionid'];
  $decision = true;
  $approver = getAuthUser()->userid;

  $approval = approveTransaction($id, $approver, $decison);

  if (!empty($approval->msg)) {
    $showMsg = $approval->msg;
  }
}

// include header
$pageTitle = "View Transaction";
include("header.php");

?>

<h3>View Transaction</h3>
<form class="pure-form pure-form-aligned" method="post" action="<?php $_SERVER['PHP_SELF']; ?>">
  <fieldset>
    <div class="pure-control-group">
      <label>Created On</label>
      <span>Hey</span>
    </div>

    <div class="pure-control-group">
      <label>Sender</label>
      <span>Hey</span>
    </div>

    <div class="pure-control-group">
      <label>Recipient</label>
      <span>Hey</span>
    </div>

    <div class="pure-control-group">
      <label>Amount</label>
      <span>Hey</span>
    </div>

    <div class="pure-control-group">
      <label>Status</label>
      <span>Hey</span>
    </div>

    <div class="pure-control-group">
      <label>Tan</label>
      <span>Hey</span>
    </div>

    <div class="pure-control-group">
      <label>Approved By</label>
      <span>Hey</span>
    </div>

    <div class="pure-control-group">
      <label>Approved On</label>
      <span>Hey</span>
    </div>

    <div class="pure-controls">
      <input type="hidden" name="transactionid" value="" />
      <button type="submit" name="approve" class="pure-button button-success">Approve</button>
      <button type="submit" name="deny" class="pure-button button-error">Deny</button>
    </div>
  </fieldset>
</form>

<?php 

// include footer
include("footer.php"); 

?>