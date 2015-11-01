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
  }
}

// if the logged in user is not an employee
if (getAuthUser()->usertype === 'C') {
  $accountId = getAccountByUserId(getAuthUser()->userid)->ID;
  $transactions = getTransactionsByUserId($accountId);
} else {
  if (isset($_GET['id']) && $_GET['id'] > 0) {
    $accountId = getAccountByUserId($_GET['id'])->ID;
    $transactions = getTransactionsByUserId($accountId);
  } else {
    $transactions = getTransactions();
  }
}

$users = getUsers();

// include header
$pageTitle = "View Transactions";
include("header.php");
?>

<p>
  <a class="pure-button pure-button-primary" href="create_transaction.php">New Transaction</a>
  <a class="pure-button pure-button-primary" href="view_transactions.php?download=true">Download Transactions</a>

  <form class="pure-form" method="get" action="<?php $_SERVER['PHP_SELF']; ?>">
   <fieldset>
      <select name="id">
        <option value="0">All Users</option>
        <?php foreach($users as $user): ?>
        <option value="<?php echo $user->ID; ?>" <?php if (isset($_GET['id']) && $_GET['id'] === $user->ID) { echo "selected"; }?>>
          <?php echo $user->FIRST_NAME." ".$user->LAST_NAME; ?>
        </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="pure-button pure-button-primary">Select</button>
  </fieldset>
</form>
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
        <td><?php echo $transaction->SENDER_ACCOUNT_NUM; ?></td>
        <td><?php echo $transaction->RECIPIENT_ACCOUNT_NUM; ?></td>
        <td><?php echo number_format($transaction->AMOUNT); ?></td>
        <td><?php if ($transaction->STATUS === "A") echo "Approved"; else if ($transaction->STATUS === "D") echo "Declined"; else echo "Pending"; ?></td>
        <td><?php echo $transaction->TAN_NUMBER; //TAN NUMBER ? ?></td>
        <td><?php echo $transaction->APPROVED_BY_NAME; ?></td>
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

