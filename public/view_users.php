<?php

define('BANK_APP', TRUE);

require_once "../app/user.php";

startSession(true);

//Provisioning 4.4.3
privilegedUserAction();

$users = getUsers();

// include header
$pageTitle = "View Users";
include("header.php");

?>

<h3>View Users</h3>
<table class="pure-table pure-table-bordered">
  <thead>
    <tr>
      <th>#</th>
      <th>User Name</th>
      <th>Email</th>
      <th>Type</th>
      <th>Account No.</th>
      <th>Approved By</th>
      <th>Approved On</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach($users as $user): ?>
    <tr>
      <td><?php echo $user->ID; ?></td>
      <td><?php echo $user->FIRST_NAME.' '.$user->LAST_NAME; ?></td>
      <td><?php echo $user->EMAIL; ?></td>
      <td><?php echo $user->USER_TYPE === "C" ? "Client" : "Employee"; ?></td>
      <td><?php echo $user->ACCOUNT_NUMBER; ?></td>
      <td><?php echo $user->APPROVED_BY; ?></td>
      <td><?php echo $user->DATE_APPROVED; ?></td>
      <td><a href="view_user.php?id=<?php echo $user->ID;?>">Open</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php 

// include footer
include("footer.php"); 

?>
