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
  $return = returnValue();
  if (!filter_var($sender, FILTER_VALIDATE_INT) or $sender < 1) {
    $return->value = false;
    $return->msg = "Bad Request: Sender account must be a positive integer.";
    return $return;
  }
  if (!filter_var($recipient, FILTER_VALIDATE_INT) or $recipient < 1) {
    $return->value = false;
    $return->msg = "Bad Request: Recipient account must be a positive integer.";
    return $return;
  }
  if ($recipient == $sender) {
    $return->value = false;
    $return->msg = "Bad Request: Recipient account must be different from sender.";
    return $return;
  }
  if (is_string($amount) && !ctype_digit($amount) || !filter_var($amount, FILTER_VALIDATE_INT)) {
    $return->value = false;
    $return->msg = "Bad Request: Amount must be an integer.";
    return $return;
  } else if ($amount < 1) {
    $return->value = false;
    $return->msg = "Bad Request: Amount must be positive.";
    return $return;
  }
  if (empty($tan) or preg_match('/[^A-Za-z0-9]/', $tan)) {
    $return->value = false;
    $return->msg = "Bad Request: Malformed TAN";
    return $return;
  }
  $sender_account = selectAccount($sender); //I think a selectAccount() is needed (?)
  if (!$sender_account) {
    $return->value = false;
    $return->msg = "Not Found: Sender account";
    return $return;
  } else if ($sender_account->BALANCE < $amount) {
      $return->value = false;
      $return->msg = "Bad Request: Amount to be transferred greater than balance";
      return $return;
  }
  $rec_account = selectAccount($recipient);
  if ($rec_account) {
    $return->value = false;
    $return->msg = "Not Found: Recipient account";
    return $return;
  }
  $tan_record = getSingleTan($tan);
  if (!$tan_record) {
    $return->value = false;
    $return->msg = "Invalid TAN";
    return $return;
  } else {
    if ($tan_record->CLIENT_ACCOUNT != $sender || strcmp($tan_record[4],"V")) {
      $return->value = false;
      $return->msg = "Invalid TAN";
      return $return;
    }
  }
  $action = insertTransaction($sender,$recipient,$amount,$tan_record[0]);
  if (!$action) {
    $return->value = false;
    $return->msg = "Transaction failed";
    return $return;
  }
  $return->value = true;
  $return->msg = "Transaction successful";
  return $return;
}

// approve / deny a transaction
function approveTransaction($id, $approver, $decison) {
  $return  = returnValue();
  if (!filter_var($id, FILTER_VALIDATE_INT) or $id < 1) {
    $return->value = false;
    $return->msg = "Invalid transaction id.";
    return $return;
  }
  $user_record = selectUSer($id);
  if (!$user_record || $user_record->USER_TYPE != "E") {
    $return->value = false;
    $return->msg = "Invalid approver";
    return $return;
  }
  $approve = updateTransactionApproval($id, $approver, $decison);
  if (!$approve) {
    $return->value = false;
    $return->msg = "Transaction update failed";
    return $return;
  }
  $return->value = true;
  $return->msg = "Transaction update successful";
  return $return;
}

// parse transaction file
function parseTransactionFile($path, $sender) {

}

?>

