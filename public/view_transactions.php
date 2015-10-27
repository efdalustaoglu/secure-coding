<?php

define('BANK_APP', TRUE);

require_once "../app/user.php";
require_once "../app/transaction.php";

startSession(true);

$transaction = getTransactions();

// include header
$pageTitle = "View Transactions";
include("header.php");

?>

<h3>View Transactions</h3>
<p>
  <a class="pure-button pure-button-primary" href="create_transaction.php">New Transaction</a>
</p>
<table class="pure-table">
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
    <tr>
      <td>1</td>
      <td>Honda</td>
      <td>Accord</td>
      <td>2009</td>
      <td>Accord</td>
      <td>Accord</td>
      <td>Accord</td>
      <td>Accord</td>
      <td>Accord</td>
      <td><a href="">Open</a></td>
    </tr>
  </tbody>
</table>

<?php 

// include footer
include("footer.php"); 

?>