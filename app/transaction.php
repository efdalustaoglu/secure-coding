<?php

require_once "db.php";

// gets all transactions
function getTransactions() {
  $transactions = selectTransactions();
  if ($transactions->num_rows > 0) {
    // output data of each row
    echo "
      <table>   
        <tr>
          <td>Transaction ID</td>
          <td>Sender Account</td>
          <td>Recipient Account</td>
          <td>Amount</td>
          <td>Status</td>
          <td>TAN ID</td>
          <td>Approved by</td>
          <td>Date Approved</td>
          <td>Date Created</td>
        </tr>"
    while($row = $transactions->fetch_assoc()) {
      echo "
        <tr>
          <td>" . $row["ID"] . "</td>
          <td>" . $row["SENDER_ACCOUNT"] . "</td>
          <td>" . $row["RECIPIENT_ACCOUNT"]. "</td>
          <td>" . $row["AMOUNT"]. "</td>
          <td>" . $row["STATUS"]. "</td>
          <td>" . $row["TAN_ID"]. "</td>
          <td>" . $row["APPROVED_BY"]. "</td>
          <td>" . $row["DATE_APPROVED"]. "</td>
          <td>" . $row["DATE_CREATED"]. "</td>
        </tr>
      "; 
    }
    echo "</table>";
  } else {
    echo "";
  }
}

// gets a single transaction
function getSingleTransaction($id) {

}

// creates a transaction
function createTransaction($sender, $recipient, $amount, $tan) {

}

// approve / deny a transaction
function approveTransaction($id, $approver, $decison) {

}

// parse transaction file
function parseTransactionFile($path, $sender) {

}

?>
