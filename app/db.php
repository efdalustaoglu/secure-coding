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

// opens a databse connection
function openDb() {
  $host = "localhost";
  $username = "root";
  $password = "";
  $database = "bank_db";

  // create connection
  $connection = mysqli_connect($host, $username, $password, $database);

  // check if connection was successful
  if (mysqli_connect_errno()) {
    die("MYSQL connection failed: ". mysqli_connect_error());
  }

  return $connection;
}

// closes a database connection 
function closeDb(&$connection) {
  mysqli_close($connection);
}

// execute query that returns a recordset
function executeQuery($sql, &$connection, $findFirst = false) {
  $result = mysqli_query($connection, $sql) or die(mysqli_error($connection));

  $resultSet = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $resultSet[] = (object) $row;
  }
  closeDb($connection);

  // return the first result only. useful when query for just 
  // a single record
  if ($findFirst) {
    $resultSet = (count($resultSet) > 0) ? $resultSet[0] : null; 
  } 

  return $resultSet;
}

// execute query that does not return a recordset
function executeNonQuery($sql, &$connection) {
  $result = mysqli_query($connection, $sql) or die(mysqli_error($connection));
  closeDb($connection);

  // as a non select query, it won't return a mysqli result set
  // it returns true or false depending on success or failure
  return $result;
}

function escape($value, &$connection) {
  return mysqli_real_escape_string($connection, $value);
}

// select all users
function selectUsers() {
  $connection = openDb();
  $sql = "SELECT * FROM users_view";
  return executeQuery($sql, $connection);
}

// select a user by ID
function selectUser($id) {
  $connection = openDb();
  $id = (int) $id;
  $sql = "SELECT * FROM users WHERE ID = $id";
  return executeQuery($sql, $connection, true);
}

// select a user by email and password
function selectByEmailAndPassword($email, $password) {
  $connection = openDb();
  $email = escape($email, $connection);
  $password = escape($password, $connection);

  $sql = "SELECT * FROM users WHERE EMAIL = '$email' AND PASSWORD = '$password' AND DATE_APPROVED IS NOT NULL";
  return executeQuery($sql, $connection, true);
}

// insert into user table
function insertUser($userType, $email, $password, $firstname, $lastname) {
  $connection = openDb();
  $date = date('Y-m-d');
  $userType = escape($userType, $connection);
  $email = escape($email, $connection);
  $password = escape($password, $connection);
  $firstname = escape($firstname, $connection);
  $lastname = escape($lastname, $connection);

  $sql = "INSERT INTO users (USER_TYPE, EMAIL, PASSWORD, FIRST_NAME, LAST_NAME, DATE_CREATED) ";
  $sql.= "VALUES ('$userType', '$email', '$password', '$firstname', '$lastname', '$date')";
  return executeNonQuery($sql, $connection);
}

// update user registration
function updateUserRegistration($id, $approver) {
  $connection = openDb();
  $id = (int) $id;
  $approver = (int) $approver;
  $date = date('Y-m-d');

  $sql = "UPDATE users SET APPROVED_BY = $approver, DATE_APPROVED = '$date' WHERE ID = $id";
  return executeQuery($sql, $connection);
}

// select all transactions
function selectTransactions() {
  $connection = openDb();
  $sql = "SELECT * FROM `transactions`";
  return executeQuery($sql, $connection);
}

// select single transactions
function selectTransaction($id) {
  $connection = openDb();
  $sql = "SELECT * FROM `transactions` WHERE ID = $id";
  return executeQuery($sql, $connection);
}

// insert into transactions table
function insertTransaction($sender, $recipient, $amount, $tan) {
  $connection = openDb();
  $date = date('d.m.Y H:i');

  if ($amount >= 10000) {
  
    $sql = "INSERT INTO transactions ( SENDER_ACCOUNT, RECIPIENT_ACCOUNT, AMOUNT, TAN_ID, DATE_CREATED, APPROVED_BY)";
    $sql.= "VALUES ('$sender', '$recipient', '$amount', '$tan', '$date', "0");";
    return executeNonQuery($sql, $connection);

  }else if ($amount < 10000 && $amount >= 0) {
  
    $sql = "INSERT INTO transactions ( SENDER_ACCOUNT, RECIPIENT_ACCOUNT, AMOUNT, TAN_ID, DATE_CREATED, APPROVED_BY, DATE_APPROVED)";
    $sql.= "VALUES ('$sender', '$recipient', '$amount', '$tan', '$date', "0", '$date');";
    return executeNonQuery($sql, $connection);

  }else{}
}

// update transaction approval
function updateTransactionApproval($id, $approver, $decison) {
  // $decision = A / D / P. Approved, Denied, Pending
  $connection = openDb();
  $date = date('d.m.Y H:i');
  $sql = "UPDATE transactions SET APPROVED_BY='$approver', DATE_APPROVED='$date', STATUS='$decision' WHERE id='$id' ";
  return executeQuery($sql, $connection);
}

// insert new tans
function insertTan($userAccount, $tan) {
  // default tan status is V - valid
  $connection = openDb();
  $date = date('d.m.Y H:i');

  $sql = "INSERT INTO tans ( TAN_NUMBER, CLIENT_ACCOUNT, DATE_CREATED)";
  $sql.= "VALUES ('$tan', '$userAccount', '$date');";
  return executeNonQuery($sql, $connection);
}

// update tan
function updateTanStatus($tanId, $status) {
  // possible values = U / V. Used, Valid.
  $connection = openDb();
  $sql = "UPDATE tans SET STATUS='$status' WHERE ID='$tanId' ";
  return executeQuery($sql, $connection);
}

// select tan
function getSingleTan($tan) {
  $connection = openDb();
  $sql = "SELECT * FROM `tans` WHERE ID = $id";
  return executeQuery($sql, $connection);
}

// select tans by giving userID
function getTansByUserId($id) {
  
  $connection = openDb();
  $sql = "SELECT * FROM `tans` WHERE CLIENT_ACCOUNT = $id";
  return executeQuery($sql, $connection);
}

?>