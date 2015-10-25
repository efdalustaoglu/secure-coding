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
  if (!is_int((int)$sender) or $sender < 1) {
    $res->value = false;
    $res->msg = "Bad Request: Sender account must be a positive integer.";
    return $res;
  }
  if (!is_int((int)$recipient) or $recipient < 1) {
    $res->value = false;
    $res->msg = "Bad Request: Recipient account must be a positive integer.";
    return $res;
  }
  if ($recipient == $sender) {
    $res->value = false;
    $res->msg = "Bad Request: Recipient account must be different from sender.";
    return $res;
  }
  if (!is_int((int)$amount) or $amount < 1) {
    $res->value = false;
    $res->msg = "Bad Request: Amount must be a positive integer.";
    return $res;
  }
  if (empty($tan) or preg_match('/[^A-Za-z0-9]/', $tan)) {
    $res->value = false;
    $res->msg = "Bad Request: Malformed TAN";
    return $res;
  }
  $sender_account = selectAccount($sender);
  if (!$sender_account->num_rows) {
    $res->value = false;
    $res->msg = "Not Found: Sender account";
    return $res;
  } else if (($sender_account->fetch_row())[3] < $amount) {
      $res->value = false;
      $res->msg = "Bad Request: Amount to be transferred greater than balance";
      return $res;
  }
  $rec_account = selectAccount($recipient);
  if (!$rec_account->num_rows) {
    $res->value = false;
    $res->msg = "Not Found: Recipient account";
    return $res;
  }
  $tan_request = getSingleTan($tan);
  if (!$tan_request->num_rows) {
    $res->value = false;
    $res->msg = "Invalid TAN";
    return $res;
  } else {
    $tan_record = $tan_request->fetch_row();
    if ($tan_record[2] != $sender || strcmp($tan_record[3],"V")) {
      $res->value = false;
      $res->msg = "Invalid TAN";
      return $res;
    }
  }
  $action = insertTransaction($sender,$recipient,$amount,$tan_record[0]);
  if (!$action) {
    $res->value = false;
    $res->msg = "Transaction failed";
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
