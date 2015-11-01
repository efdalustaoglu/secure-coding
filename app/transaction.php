<?php

if(!defined('BANK_APP')) { die('Direct access not permitted'); }

require_once "db.php";

// gets all transactions
function getTransactions() {
  return selectTransactions();
}

// gets filtered transactions
function getTransactionsByUserId($id) {
  return selectTransactionsByUserId($id);
}

// gets a single transaction
function getSingleTransaction($id) {
  return selectTransaction($id);
}


// creates a transaction
function createTransaction($sender, $recipient, $amount, $tan) {
  $return = returnValue();
  
  if ($recipient == $sender) {
    $return->value = false;
    $return->msg = "Recipient account must be different from sender.";
    return $return;
  }

  if (!is_numeric($amount)) {
    $return->value = false;
    $return->msg = "Amount must be a number";
    return $return;
  }

  $recipientAccount = selectAccountByNumber($recipient);
  if (!$recipientAccount) {
    $return->value = false;
    $return->msg = "Recipient account not found";
    return $return;
  }
  
  $senderAccount = selectAccountByNumber($sender);
  if ($senderAccount->BALANCE < $amount) {
    $return->value = false;
    $return->msg = "Insufficient funds";
    return $return;
  }

  $tanEntry = selectTanByTan($tan);
  if (!$tanEntry) {
    $return->value = false;
    $return->msg = "Invalid TAN";
    return $return;
  }

  if ($tanEntry->CLIENT_ACCOUNT !== $senderAccount->ID || $tanEntry->STATUS !== "V") {
    $return->value = false;
    $return->msg = "Invalid TAN";
    return $return;
  }
  
  $invalidateTan = updateTanStatus($tanEntry->ID);
  if (!$invalidateTan) {
    $return->value = false;
    $return->msg = "Tan update failed";
    return $return;
  }

  $insert = insertTransaction($senderAccount->ID, $recipientAccount->ID, $amount, $tanEntry->ID);
  if (!$insert) {
    $return->value = false;
    $return->msg = "Transaction failed";
    return $return;
  }

  if ($amount <= 10000) {
    $balance = updateBalance($senderAccount->ID, $recipientAccount->ID, $amount);

    if (!$balance) {
      $return->value = false;
      $return->msg = "Error updating balance";
      return $return;
    }
  }

  $return->value = true;
  $return->msg = "Transaction successful";
  return $return;
}

// approve / deny a transaction
function approveTransaction($id, $approver, $decision) {
  $return  = returnValue();

  $transaction = selectTransaction($id);
  if (!$transaction) {
    $return->value = false;
    $return->msg = "Invalid transaction id";
    return $return;
  }

  $user = selectUser($approver);
  if (!$user || $user->USER_TYPE !== "E") {
    $return->value = false;
    $return->msg = "Invalid approver";
    return $return;
  }

  $approve = updateTransactionApproval($id, $approver, $decision);
  if (!$approve) {
    $return->value = false;
    $return->msg = "Transaction update failed";
    return $return;
  }

  if ($decision === 'D') {
    $return->value = true;
    $return->msg = "Transaction successfully denied";
    return $return;
  }

  $balance = updateBalance($transaction->SENDER_ACCOUNT, $transaction->RECIPIENT_ACCOUNT, $transaction->AMOUNT);
  if (!$balance) {
    $return->value = false;
    $return->msg = "Error updating balance";
    return $return;
  }
  
  $return->value = true;
  $return->msg = "Transaction successfully approved";
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

?>

