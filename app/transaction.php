<?php

if(!defined('BANK_APP')) { die('Direct access not permitted'); }

require_once "db.php";

// gets all transactions
function getTransactions($display = false) {
  $transactions = selectTransactions();
  if ($display) {
    foreach($transactions as $transaction) {
      $returnSet[] = (object) array(
        "ID" => $transaction->ID,
        "SENDER_ACCOUNT" => getAccountByAccId($transaction->SENDER_ACCOUNT)->ACCOUNT_NUMBER,
        "RECIPIENT_ACCOUNT" => getAccountByAccId($transaction->RECIPIENT_ACCOUNT)->ACCOUNT_NUMBER,
        "AMOUNT" => $transaction->AMOUNT,
        "STATUS" => $transaction->STATUS,
        "TAN_ID" => getSingleTanById($transaction->TAN_ID)->TAN_NUMBER,
        "APPROVED_BY" => $transaction->APPROVED_BY != 0 ? selectUser($transaction->APPROVED_BY)->LAST_NAME ." ". selectUser($transaction->APPROVED_BY)->FIRST_NAME : $transaction->APPROVED_BY,
        "DATE_APPROVED" => $transaction->DATE_APPROVED,
        "DATE_CREATED" => $transaction->DATE_CREATED
      );
    }
    return $returnSet;
  }
  return $transactions;
}

// gets a single transaction
function getSingleTransaction($id, $display = false) {
  $transaction = selectTransaction($id);
  if ($display) {
    $return = array(
      "ID" => $transaction->ID,
      "SENDER_ACCOUNT" => getAccountByAccId($transaction->SENDER_ACCOUNT)->ACCOUNT_NUMBER,
      "RECIPIENT_ACCOUNT" => getAccountByAccId($transaction->RECIPIENT_ACCOUNT)->ACCOUNT_NUMBER,
      "AMOUNT" => $transaction->AMOUNT,
      "STATUS" => $transaction->STATUS,
      "TAN_ID" => getSingleTanById($transaction->TAN_ID)->TAN_NUMBER,
      "APPROVED_BY" => $transaction->APPROVED_BY != 0 ? selectUser($transaction->APPROVED_BY)->LAST_NAME ." ". selectUser($transaction->APPROVED_BY)->FIRST_NAME : $transaction->APPROVED_BY,
      "DATE_APPROVED" => $transaction->DATE_APPROVED,
      "DATE_CREATED" => $transaction->DATE_CREATED
    );
    return (object) $return;
  }
  return $transaction;
}

function transferAmount($sender,$recipient,$amount) {
  //TODO: be persistent about sender_account
  $sender_account = getAccountByAccId($sender); //I think a getAccountByAccId() is needed (?)
  $new_balance = ($sender_account->BALANCE - $amount); 
  $remove = updateBalance($sender_account->ACCOUNT_NUMBER, $new_balance);

  //2. method: continue attempt to transfer money
  while (!$remove_amount) {
    $remove_amount = updateBalance($sender_account->ACCOUNT_NUMBER, $new_balance);
  }
  $recipient_account = getAccountByAccId($recipient); //I think a getAccountByAccId() is needed (?)
  $new_balance = ($recipient_account->BALANCE + $amount); 
  $add_amount = updateBalance($recipient_account->ACCOUNT_NUMBER, $new_balance);
  while (!$add_amount) {
    $add_amount = updateBalance($recipient_account->ACCOUNT_NUMBER, $new_balance);
  }
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
  $sender_account = getAccountByAccNumber($sender); //I think a getAccountByAccNumber() is needed (?)
  if (!$sender_account) {
    $return->value = false;
    $return->msg = "Not Found: Sender account";
    return $return;
  } else if ($sender_account->BALANCE < $amount) {
      $return->value = false;
      $return->msg = "Bad Request: Amount to be transferred greater than balance";
      return $return;
  }
  $rec_account = getAccountByAccNumber($recipient);
  if (!$rec_account) {
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
    if ($tan_record->CLIENT_ACCOUNT != $sender_account->ID || strcmp($tan_record->STATUS,"V")) {
      $return->value = false;
      $return->msg = "Invalid TAN";
      return $return;
    }
  }
  $invalidate_tan = updateTanStatus($tan_record->ID,"I");
  if (!$invalidate_tan) {
    $return->value = false;
    $return->msg = "Transaction failed";
    return $return;
  }
  $action = insertTransaction($sender_account->ID,$rec_account->ID,$amount,$tan_record->ID);
  if (!$action) {
    $return->value = false;
    $return->msg = "Transaction failed";
    return $return;
  }
  if ($amount < 10000) {
    transferAmount($sender_account->ID,$rec_account->ID,$amount);
  }
  $return->value = true;
  $return->msg = "Transaction successful";
  return $return;
}

// approve / deny a transaction
function approveTransaction($id, $approver, $decision, $transaction) { //reasons for $transaction explained on view_transaction.php
  $return  = returnValue();
  $transaction = getSingleTransaction($transaction->ID);
  if (!filter_var($id, FILTER_VALIDATE_INT) or $id < 1) {
    $return->value = false;
    $return->msg = "Invalid transaction id.";
    return $return;
  }
  $user_record = selectUser($approver);
  if (!$user_record || $user_record->USER_TYPE != "E") {
    $return->value = false;
    $return->msg = "Invalid approver";
    return $return;
  }
  $new_status = ($decision == true) ? "D" : "A";
  $approve = updateTransactionApproval($id, $approver, $new_status);
  if (!$approve) {
    $return->value = false;
    $return->msg = "Transaction update failed";
    return $return;
  }

  $amount = $transaction->AMOUNT;
  $sender = $transaction->SENDER_ACCOUNT;
  $recipient = $transaction->RECIPIENT_ACCOUNT;
  transferAmount($sender,$recipient,$amount);
  
  $return->value = true;
  $return->msg = "Transaction update successful";
  return $return;
}

// upload transaction file
function uploadTransactionFile() {
  $return = returnValue();
  $filename = basename($_FILES["file"]["name"]);
  $target_dir = "uploads/";
  $target_file = $target_dir . $filename;

  if (!move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
    $return->value = false;
    $return->msg = "Upload failed";
    return $return;
  }

  $return->value = $filename;
  $return->msg = "Upload successful";
  return $return;
}

// parse transaction file
function parseTransactionFile($path, $sender) {

}

?>

