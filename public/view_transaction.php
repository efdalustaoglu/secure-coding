<?php
define('BANK_APP', TRUE);
require_once "../app/user.php";
require_once "../app/transaction.php";
startSession(true);
// get single transaction
if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $transaction = getSingleTransaction($id,true);
}
// process form
if (isset($_POST['approve']) || isset($_POST['deny'])) {
  $id = $_POST['transactionid'];
  $decision = (isset($_POST['approve'])) ? true : false; //NOT "A" (Accepted) /"D" (Declined) ?
  $approver = getAuthUser()->userid;
  $approval = approveTransaction($id, $approver, $decison, $transaction); //I need sender recipient ids to actually transfer the money, so I included the $transaction
  if (!empty($approval->msg)) {
    $showMsg = $approval->msg;
  }
  //On my browser, after a transaction update, the displayed data weren't refreshed, so I included the following:
  $transaction = getSingleTransaction($id,true);
}
// include header
$pageTitle = "View Transaction";
include("header.php");
?>

<?php if (isset($transaction) && $transaction): ?>
<h3>View Transaction</h3>
<form class="pure-form pure-form-aligned" method="post" action="<?php $_SERVER['PHP_SELF']; ?>">
  <fieldset>
    <div class="pure-control-group">
      <label>Created On</label>
      <span><?php echo $transaction->DATE_CREATED; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Sender</label>
      <span><?php echo $transaction->SENDER_ACCOUNT; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Recipient</label>
      <span><?php echo $transaction->RECIPIENT_ACCOUNT; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Amount</label>
      <span><?php echo $transaction->AMOUNT; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Status</label>
      <span><?php if ($transaction->STATUS == "P") echo "Pending"; else if ($transaction->STATUS == "A") echo "Accepted"; else echo "Denied"; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Tan</label>
      <span><?php echo $transaction->TAN_ID; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Approved By</label>
      <span><?php echo $transaction->APPROVED_BY; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Approved On</label>
      <span><?php echo $transaction->DATE_APPROVED; ?></span>
    </div>

    <div class="pure-controls">
      <input type="hidden" name="transactionid" value="<?php echo $transaction->ID?>" />
      <?php if ($transaction->STATUS == "P" and getAuthUser()->usertype == "E") : ?>
      <button type="submit" name="approve" class="pure-button button-success">Approve</button>
      <button type="submit" name="deny" class="pure-button button-error">Deny</button>
      <?php endif; ?>
    </div>
  </fieldset>
</form>

<?php 
endif;
// include footer
include("footer.php"); 
?>
