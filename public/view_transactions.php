<?php
define('BANK_APP', TRUE);
require_once "../app/user.php";
require_once "../app/transaction.php";
startSession(true);
$transactions = getTransactions();
// include header
$pageTitle = "View Transactions";
include("header.php");
?>

<h3>View Transactions</h3>
<p>
  <a class="pure-button pure-button-primary" href="create_transaction.php">New Transaction</a>
</p>
<table class="pure-table pure-table-bordered">
  <thead>
    <tr>
      <th>#</th>
      <th>Created On</th>
      <th>Sender</th>
      <th>Recipient</th>
      <th>Amount</th>
      <th>Status</th>
      <th>Tan</th>
      <th>Approved By</th>
      <th>Approved On</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach($transactions as $transaction): ?>
    <tr>
      <td><?php echo $transaction->ID; ?></td>
      <td><?php echo $transaction->DATE_CREATED; ?></td>
      <td><?php echo $transaction->SENDER_ACCOUNT; ?></td>
      <td><?php echo $transaction->RECIPIENT_ACCOUNT; ?></td>
      <td><?php echo $transaction->AMOUNT; ?></td>
      <td><?php if ($transaction->STATUS === "A") echo "Approved"; else if ($transaction->STATUS === "D") echo "Declined"; else echo "Pending"; ?></td>
      <td><?php echo $transaction->TAN_ID; //TAN NUMBER ? ?></td>
      <td><?php echo $transaction->APPROVED_BY; ?></td>
      <td><?php echo $transaction->DATE_APPROVED; ?></td>
      <td><a href="view_transaction.php?id=<?php echo $transaction->ID;?>">Open</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php 
// include footer
include("footer.php"); 
?>

