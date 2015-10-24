<?php

require_once "db.php";

// gets all transactions
function getTransactions() {
  $transactions = selectTransactions();
  return $transactions;
}

// gets a single transaction
function getSingleTransaction($id) {
  $transaction = selectTransaction();
  return $transaction;

}

// creates a transaction
function createTransaction($sender, $recipient, $amount, $tan) {
  //TODO: check if parameters are valid
  $res = insertTransaction($sender,$recipient,$amount,$tan);
  if (!$res)
    return 0;
  else
    return 1;
}

// approve / deny a transaction
function approveTransaction($id, $approver, $decison) {

}

// parse transaction file
function parseTransactionFile($path, $sender) {

}

?>
