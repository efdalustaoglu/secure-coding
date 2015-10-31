<?php

if(!defined('BANK_APP')) { die('Direct access not permitted'); }

require_once "db.php";

// gets all transactions
function getTransactions() {

}

// gets a single transaction
function getSingleTransaction($id) {

}

// creates a transaction
function createTransaction($sender, $recipient, $amount, $tan) {
  $return  = returnValue();
  return $return;
}

// approve / deny a transaction
function approveTransaction($id, $approver, $decison) {
  $return  = returnValue();
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

function generateTransactionPdf() {

}

?>