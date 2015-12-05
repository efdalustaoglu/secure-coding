<?php

define('BANK_APP', TRUE);

require_once "../app/user.php";

startSession(true);

//CSRF
if (!isset($_POST['approve']) && !isset($_POST['reject'])) {
  clearCSRFToken();
  createCSRFToken('user');
}

// process form
if ((isset($_POST['approve']) || isset($_POST['reject'])) && isset($_SESSION['usertoken']) && $_POST['usertoken'] == $_SESSION['usertoken']) {
  $id = $_POST['userid'];
  $decision = (isset($_POST['approve'])) ? true : false;
  $approver = getAuthUser()->userid;
  unset($_SESSION['usertoken']);
  $approval = approveRegistration($id, $approver, $decision);

  if (!empty($approval->msg)) {
    $showMsg = $approval->msg;
  }
}

// get single user - Sanitize input 4.8.1
$id = (isset($_GET['id']) && getAuthUser()->usertype === 'E') ? (int) $_GET['id'] : getAuthUser()->userid;
//4.8.1
if (is_numeric($id)) {
  $user = getSingleUser($id);
}

// if this user is invalid, redirect to view users page
if (!$user) {
  header("Location: "."view_users.php");
  exit();
}

// include header
$pageTitle = "View User";
include("header.php");

?>

<?php if ($user): ?>
<h3>View User</h3>
<form class="pure-form pure-form-aligned" method="post" action="<?php $_SERVER['PHP_SELF']; ?>">
  <input type="hidden" name="usertoken" id="usertoken" value="<?php echo $_SESSION['usertoken'] ?>" />
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
      <label>Account number</label>
      <span><?php echo $user->ACCOUNT_NUMBER; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Balance</label>
      <span><?php echo number_format($user->BALANCE, 2, ".", ","); ?></span>
    </div>

    <div class="pure-control-group">
      <label>Approved By</label>
      <span><?php echo $user->APPROVED_BY; ?></span>
    </div>

    <div class="pure-control-group">
      <label>Approved On</label>
      <span><?php echo $user->DATE_APPROVED; ?></span>
    </div>

    <?php if ($user->DATE_APPROVED === null && getAuthUser()->usertype === 'E'): ?>
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
