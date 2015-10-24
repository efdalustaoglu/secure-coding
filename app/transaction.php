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
  class Returnable {
    public $value;
    public $msg;
  }
  $res = new Returnable();
  if (!preg_match('/[^A-Za-z0-9]/', $tan)) {
    $res->value = 1;
    // string contains only english letters & digits
  } else {
    $res->value = 0;
    $res->msg = "Invalid TAN";
    return $res;
  }
  $action = insertTransaction($sender,$recipient,$amount,$tan);
  if (!$action) {
    $res->value = 0;
    $res->msg = "Database error";
  }
  else {
    $res->value = 1;
  }
  return $res;
}

// approve / deny a transaction
function approveTransaction($id, $approver, $decison) {

}

// parse transaction file
function parseTransactionFile($path, $sender) {

}

?>
