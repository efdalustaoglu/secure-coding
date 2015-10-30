<?php

define('BANK_APP', TRUE);

require_once "../app/user.php";

startSession(true);

// get single user
$id = (isset($_GET['id']) && getAuthUser()->usertype === 'E') ? $_GET['id'] : getAuthUser()->userid;
$user = getSingleUser($id);

// process form
if (isset($_POST['approve']) || isset($_POST['reject'])) {
  $id = $_POST['userid'];
  $decision = (isset($_POST['approve'])) ? true : false;
  $approver = getAuthUser()->userid;
  $approval = approveRegistration($id, $approver, $decision);

  if (!empty($approval->msg)) {
    $showMsg = $approval->msg;
  }
}

// include header
$pageTitle = "View User";
include("header.php");

?>

<?php if ($user): ?>
<h3>View User</h3>
<form class="pure-form pure-form-aligned" method="post" action="<?php $_SERVER['PHP_SELF']; ?>">
  <fieldset>
    <div class="pure-control-group">
      <label>Created On</label>
      <span><?php echo $user->DATE_CREATED; ?></span>
    </div>

    <div class="pure-control-group">
      <label>First name</label>
      <span><?php echo $user->FIRST_NAME; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Last name</label>
      <span><?php echo $user->LAST_NAME; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Email</label>
      <span><?php echo $user->EMAIL; ?></span>
    </div>

    <div class="pure-control-group">
      <label>User type</label>
      <span><?php echo $user->USER_TYPE === 'C' ? "Client" : "Employee"; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Approved By</label>
      <span><?php echo $user->APPROVED_BY; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Approved On</label>
      <span><?php echo $user->DATE_APPROVED; ?></span>
    </div>

    <?php if ($user->DATE_APPROVED === null): ?>
    <div class="pure-controls">
      <input type="hidden" name="userid" value="<?php echo $id; ?>" />
      <button type="submit" name="approve" class="pure-button button-success">Approve</button>
      <button type="submit" name="reject" class="pure-button button-error">Reject</button>
    </div>
    <?php endif; ?>
  </fieldset>
</form>

<?php 
endif;

// include footer
include("footer.php"); 

?>