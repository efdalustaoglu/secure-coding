<?php

if(!defined('BANK_APP')) { die('Direct access not permitted'); }

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
  $res = returnValue();
  if (!is_int($sender) && $sender < 1) {
    $res->value = false;
    $res->msg = "Invalid sender";
    return $res;
  }
  if (!is_int($recipient) && $recipient < 1) {
    $res->value = false;
    $res->msg = "Invalid recipient";
    return $res;
  }
  if ($recipient == $sender) {
    $res->value = false;
    $res->msg = "Invalid transaction";
    return $res;
  }
  if (!is_int($amount) && $amount < 1) {
    $res->value = false;
    $res->msg = "Invalid amount";
    return $res;
  }
  if (preg_match('/[^A-Za-z0-9]/', $tan)) {
    $res->value = false;
    $res->msg = "Invalid TAN";
    return $res;
  }
  $action = insertTransaction($sender,$recipient,$amount,$tan);
  if (!$action) {
    $res->value = false;
    $res->msg = "Database error";
    return $res;
  }
  $res->value = true;
  $res->msg = "Transaction successful";
  return $res;
}

// approve / deny a transaction
function approveTransaction($id, $approver, $decison) {
  $return  = returnValue();
  return $return;
}

// parse transaction file
function parseTransactionFile($path, $sender) {

}

?>
