<?php

if(!defined('BANK_APP')) { die('Direct access not permitted'); }

// standard return object for functions that need to 
// validate parameters and then return a value.
// The value property is whatever the function will
// ordinarily return, and should always be set.
// The msg property is optional, and contains error 
// (e.g. failed validation) or success messages where appropriate
function returnValue() {
  $return = array(
    "value" => null,
    "msg" => null
  );
  return (object) $return;
}

// functions on this page (db.php) should not use the returnValue() function
// because they should not do any validation, all parameters sent to functions
// here should have been pre-validated. For functions that return queries
// an array representation of the data should be returned. You could use
// mysqli_fetch_array, or some other method

// prefer mysqli over mysql. the i in mysqli stands for improved.

// global vars
$connection = null;

// opens a databse connection
function openDb() {
  $host = "";
  $username = "";
  $password = "";
  $database = "";
  // create connection
  $connection = new mysqli($host, $username, $password, $database);

  // check if connection was successful
  if ($connection->connect_error) {
    die("MYSQL connection failed: ". $connection->connect_error);
  }
}

// closes a database connection 
function closeDb() {
  $connection->close();
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