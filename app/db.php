<?php

// opens a databse connection
function openDb() {

}

// closes a database connection 
function closeDb() {

}

// select all users
function selectUsers() {
  openDb();

  closeDb();
}

// select a user by ID
function selectUser($id) {
  openDb();

  closeDb();
}

// select a user by email and password
function selectByEmailAndPassword($email, $password) {
  openDb();

  closeDb();
}

// insert into user table
function insertUser($userType, $email, $password, $firstname, $lastname) {
  openDb();
  $date = date('d.m.Y H:i');
  closeDb();
}

// update user registration
function updateUserRegistration($id, $approver) {
  openDb();

  closeDb();
}

// select all transactions
function selectTransactions() {
  openDb();

  closeDb();
}

// select single transactions
function selectTransaction($id) {
  openDb();

  closeDb();
}

// insert into transactions table
function insertTransaction($sender, $recipient, $amount, $tan) {
  openDb();

  closeDb();
}

// update transaction approval
function updateTransactionApproval($id, $approver, $decison) {
  // $decision = A / D / P. Approved, Denied, Pending
  openDb();

  closeDb();
}


// insert new tans
function insertTan($userAccount, $tan) {
  // default tan status is V - valid
  openDb();
  

  closeDb();
}

// update tan
function updateTanStatus($tanId, $status) {
  // possible values = U / V. Used, Valid.
  openDb();

  closeDb();
}

// select tan
function getSingleTan($tan) {
  openDb();

  closeDb();
}

?>