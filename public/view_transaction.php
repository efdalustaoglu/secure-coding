<?php
define('BANK_APP', TRUE);
require_once "../app/user.php";
require_once "../app/transaction.php";

startSession(true);
//SQL: Get credentials for user group
get_db_credentials(getAuthUser()->usertype);

// process form
if (isset($_POST['approve']) || isset($_POST['deny'])) {
  $id = $_POST['transactionid'];
  $decision = (isset($_POST['approve'])) ? "A" : "D";
  $approver = getAuthUser()->userid;
  $approval = approveTransaction($id, $approver, $decision); 
  
  if (!empty($approval->msg)) {
    $showMsg = $approval->msg;
  }
}

// get single transaction
if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $transaction = getSingleTransaction($id);
}

// include header
$pageTitle = "View Transaction";
include("header.php");
?>

<?php if (isset($transaction) && $transaction): ?>

<?php //Ensure user is authorized to see transaction 4.4.3
  $account = getAccountByUserId(getAuthUser()->userid)->ID;
  if (getAuthUser()->usertype != 'E' && $transaction->SENDER_ACCOUNT != $account && $transaction->RECIPIENT_ACCOUNT != $account) {
    die("Unauthorized access");
  } ?>

<h3>View Transaction</h3>
<form class="pure-form pure-form-aligned" method="post" action="<?php $_SERVER['PHP_SELF']; ?>">
  <fieldset>
    <div class="pure-control-group">
      <label>Created On</label>
      <span><?php echo $transaction->DATE_CREATED; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Sender</label>
      <span><?php echo $transaction->SENDER_ACCOUNT_NUM; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Recipient</label>
      <span><?php echo $transaction->RECIPIENT_ACCOUNT_NUM; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Amount</label>
      <span><?php echo number_format($transaction->AMOUNT, 2, ".", ","); ?></span>
    </div>

    <div class="pure-control-group">
      <label>Status</label>
      <span><?php if ($transaction->STATUS == "P") echo "Pending"; else if ($transaction->STATUS == "A") echo "Accepted"; else echo "Denied"; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Tan</label>
      <span><?php echo $transaction->TAN_NUMBER; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Approved By</label>
      <span><?php echo $transaction->APPROVED_BY_NAME; ?></span>
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
