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

// parse transaction file
function parseTransactionFile($path, $sender) {

}

function generateTransactionPdf() {

}

?>