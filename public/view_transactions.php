<?php
define('BANK_APP', TRUE);
require_once "../app/user.php";
require_once "../app/transaction.php";
startSession(true);
if (isset($_GET['download'])) {
  $download = $_GET['download'];
  if (download == true) {
    $pdf = generatePDF(getAuthUser()->userid);
    return $pdf;

    $file_name = $_SERVER['PATH_INFO'];
$file = '/path/to/pdf/files' . $file_name;
if (file_exists($file)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="' . basename($file_name) . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
} else {
    header('HTTP/1.1 404 Not Found');
}
  }
}
$transactions = getTransactions(true);
// include header
$pageTitle = "View Transactions";
include("header.php");
?>

<p>
  <a class="pure-button pure-button-primary" href="create_transaction.php">New Transaction</a>
</p>
<?php $account = getAccountByUId(getAuthUser()->userid); ?>
<h3>My Transactions</h3>
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
    <?php if($account->ACCOUNT_NUMBER == $transaction->SENDER_ACCOUNT): ?>
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
    <?php endif; ?>
  <?php endforeach; ?>
  </tbody>
</table>

<p>
  <a class="pure-button pure-button-primary" href="view_transactions.php?download=true">Download Transactions</a>
</p>


<?php if(getAuthUser()->usertype == "E"): ?> 
  <h3>View All Transactions</h3>
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
<?php endif ?>

<?php 
// include footer
include("footer.php"); 
?>

