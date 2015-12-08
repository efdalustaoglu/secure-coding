<?php

if(!defined('BANK_APP')) { die('Direct access not permitted'); }

require_once "config.sample.php";

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
  $host = DB_HOST;
  $username = DB_USER;
  $password = DB_PASSWORD;
  $database = DB_NAME;

  // create connection
  $connection = mysqli_connect($host, $username, $password, $database);

  // check if connection was successful
  if (mysqli_connect_errno()) {
    die("MYSQL connection failed");
  }

  return $connection;
}

// closes a database connection 
function closeDb(&$connection) {
  mysqli_close($connection);
}


function bind_array($stmt,&$row) {
  $md = $stmt->result_metadata();
  $params = array();
  while($field = $md->fetch_field()) {
    $params[] = &$row[$field->name];
  }
  call_user_func_array(array($stmt, 'bind_result'),$params);
}

// execute query that does not return a recordset
function executeNonQuery(&$stmt, &$connection) {

  //Using prepared statements and parameterized queries:
  $result = $stmt->execute();
  $stmt->close();

  closeDb($connection);
  // as a non select query, it won't return a mysqli result set
  // it returns true or false depending on success or failure
  return $result;
}


function executeQueryPrepared(&$stmt, &$connection, $findFirst = false) {
  $resultSet = array();
  $row = array();
  if($stmt->execute()) {
    bind_array($stmt,$row);

    while ($stmt->fetch()) {
      $res = array();
      foreach($row as $key => $value) {
        $res[$key] = $value;
      }
      $resultSet[] = (object) $res;
    }

    $stmt->close();
    closeDb($connection);
    // return the first result only. useful when query for just 
    // a single record
    if ($findFirst) {
      $resultSet = (count($resultSet) > 0) ? $resultSet[0] : null; 
    } 
  }
  return $resultSet;
}

// execute query that returns a recordset
function executeQuery($sql, &$connection, $findFirst = false) {
  $result = mysqli_query($connection, $sql) or die("connection error");
  $resultSet = array();
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

function escape($value, &$connection) {
  $value = mysqli_real_escape_string($connection, $value);
  return $value;
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


  //Using prepared statements and parameterized queries:
  $sql = "SELECT * FROM users_view WHERE ID = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("i",$id);

  return executeQueryPrepared($stmt, $connection, true);
}

// select a user by email and password
function selectByEmailAndPassword($email, $password) {
  $connection = openDb();
  $email = escape($email, $connection);
  $password = escape($password, $connection);


  //Using prepared statements and parameterized queries:
  $sql = "SELECT * FROM users WHERE EMAIL = ? AND PASSWORD = ? AND DATE_APPROVED IS NOT NULL";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("ss",$email,$password);

  return executeQueryPrepared($stmt, $connection, true);
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

  //Using prepared statements and parameterized queries:
  $sql = "INSERT INTO users (USER_TYPE, EMAIL, PASSWORD, FIRST_NAME, LAST_NAME, DATE_CREATED) ";
  $sql.= "VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("ssssss",$userType,$email,$password,$firstname,$lastname,$date);

  return executeNonQuery($stmt, $connection);
}

// update user registration
function updateUserRegistration($id, $approver, $decision) {
  $connection = openDb();
  $id = (int) $id;
  $approver = (int) $approver;
  $date = date('Y-m-d');

  if ($decision === false) {
    return deleteUserRegistration($id);
  }


  //Using prepared statements and parameterized queries:
  $sql = "UPDATE users SET APPROVED_BY = ?, DATE_APPROVED = ? WHERE ID = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("ssi",$approver,$date,$id);

  return executeNonQuery($stmt, $connection);
}

// delete a rejected user registration
function deleteUserRegistration($id) {
  $connection = openDb();
  $id = (int) $id;

  //Using prepared statements and parameterized queries:
  $sql = "DELETE FROM users WHERE ID = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("i",$id);

  return executeNonQuery($stmt, $connection);
}

// select all transactions
function selectTransactions() {
  $connection = openDb();
  $sql = "SELECT * FROM transaction_view";
  return executeQuery($sql, $connection);
}

// select a user's transactions
function selectTransactionsByAccountId($id) {
  $connection = openDb();
  $id = (int) $id;


  //Using prepared statements and parameterized queries:
  $sql = "SELECT * FROM transaction_view WHERE SENDER_ACCOUNT = ? OR RECIPIENT_ACCOUNT = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("si",$id,$id);

  return executeQueryPrepared($stmt, $connection);
}

// select single transactions
function selectTransaction($id) {
  $connection = openDb();
  $id = (int) $id;

  //Using prepared statements and parameterized queries:
  $sql = "SELECT * FROM transaction_view WHERE ID = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("i",$id);

  return executeQueryPrepared($stmt, $connection, true);
}

// insert into transactions table
function insertTransaction($sender, $recipient, $amount, $description, $tan) {
  $connection = openDb();
  $date = date('Y-m-d');

  if ($amount > 10000) {

    //Using prepared statements and parameterized queries:
    $sql = "INSERT INTO transactions (SENDER_ACCOUNT, RECIPIENT_ACCOUNT, AMOUNT, DESCRIPTION, STATUS, TAN_ID, DATE_CREATED) ";
    $sql.= "VALUES (?, ?, ?, ?, 'P', ?, ?)";
    $stmt = $connection->stmt_init();
    if(!$stmt->prepare($sql)) {
      return false;
    }
    $stmt->bind_param("iidsss",$sender,$recipient,$amount,$description,$tan,$date);
  } else {

    //Using prepared statements and parameterized queries:
    $sql = "INSERT INTO transactions (SENDER_ACCOUNT, RECIPIENT_ACCOUNT, AMOUNT, DESCRIPTION, STATUS, TAN_ID, DATE_CREATED, APPROVED_BY, DATE_APPROVED) ";
    $sql.= "VALUES (?, ?, ?, ?, 'A', ?, ?, 6, ?)";
    $stmt = $connection->stmt_init();
    if(!$stmt->prepare($sql)) {
      return false;
    }
    $stmt->bind_param("iidssss",$sender,$recipient,$amount,$description,$tan,$date,$date);
  }

  return executeNonQuery($stmt, $connection);
}

//Update account balance of the sender/recipient during a transaction
function updateBalance($sender, $recipient, $amount) {
  $senderBalance = selectAccountById($sender)->BALANCE;
  $recipientBalance = selectAccountById($recipient)->BALANCE;

  $newSenderBalance = $senderBalance - $amount;
  $newRecipientbalance = $recipientBalance + $amount;

  $connection = openDb();

  //Using prepared statements and parameterized queries:
  $sql = "UPDATE accounts SET BALANCE = ? WHERE ID = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("di",$newSenderBalance,$sender);

  if (!executeNonQuery($stmt, $connection)) {
    return false;
  }

  $connection = openDb();

  //Using prepared statements and parameterized queries:
  $sql = "UPDATE accounts SET BALANCE = ? WHERE ID = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("ss",$newRecipientbalance,$recipient);

  return executeNonQuery($stmt, $connection);
}

// update transaction approval
function updateTransactionApproval($id, $approver, $decision) {
  // $decision = A / D / P. Approved, Denied, Pending
  $connection = openDb();
  $date = date('Y-m-d');

  //Using prepared statements and parameterized queries:
  $sql = "UPDATE transactions SET APPROVED_BY = ?, DATE_APPROVED = ?, STATUS = ? WHERE id = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("ssss",$approver,$date,$decision,$id);

  return executeNonQuery($stmt, $connection);
}

// insert new tans
function insertTan($tan, $client) {
  // default tan status is V - valid
  $connection = openDb();
  $date = date('Y-m-d');

  //Using prepared statements and parameterized queries:
  $sql = "INSERT INTO tans(TAN_NUMBER, CLIENT_ACCOUNT, DATE_CREATED, STATUS) ";
  $sql.= "VALUES (?, ?, ?, 'V')";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("sss",$tan,$client,$date);

  return executeNonQuery($stmt, $connection);
}

// update tan
function updateTanStatus($tanId) {
  // possible values = U / V. Used, Valid.
  $connection = openDb();

  //Using prepared statements and parameterized queries:
  $sql = "UPDATE tans SET STATUS = 'U' WHERE ID = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("s",$tanId);

  return executeNonQuery($stmt, $connection);
}

// select tan by tan
function selectTanByTan($tan) {
  $connection = openDb();

  //Using prepared statements and parameterized queries:
  $sql = "SELECT * FROM tans WHERE TAN_NUMBER = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("s",$tan);

  return executeQueryPrepared($stmt, $connection, true);
}

// select tan by tan ID <-------- NOT USED ?
//function selectSingleTan($tan) {
//  $connection = openDb();
//
//  ////Using prepared statements and parameterized queries:
//  //$sql = "SELECT * FROM tans ID = ?";
//  //$stmt = $connection->stmt_init();
//  //if(!$stmt->prepare($sql)) {
//  //  return false;
//  //}
//  //$stmt->bind_param("s",$id);
//
//  //return executeQueryPrepared($stmt, $connection, true);
//
//
//
//  $sql = "SELECT * FROM tans WHERE ID = $id";
//  echo $sql;
//  return executeQuery($sql, $connection, true);
//}

// select tans by user ID
function selectTansByUserId($id) {
  $connection = openDb();

  //Using prepared statements and parameterized queries:
  $sql = "SELECT * FROM tans WHERE CLIENT_ACCOUNT = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("i",$id);

  return executeQueryPrepared($stmt, $connection);
}

// insert user account
function insertAccount($userid, $accountNumber) {
  $connection = openDb();
  $date = date('Y-m-d');

  //Using prepared statements and parameterized queries:
  $sql = "INSERT INTO accounts(USER, ACCOUNT_NUMBER, DATE_CREATED) ";
  $sql.= "VALUES (?, ?, ?)";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("sss",$userid,$accountNumber,$date);

  return executeNonQuery($stmt, $connection);
}

// select account account by account number
function selectAccountByNumber($accountNumber) {
  $connection = openDb();

  //Using prepared statements and parameterized queries:
  $sql = "SELECT * FROM accounts WHERE ACCOUNT_NUMBER = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("s",$accountNumber);

  return executeQueryPrepared($stmt, $connection, true);
}

// select account account by user id
function selectAccountByUserId($id) {
  $connection = openDb();
  $id = escape($id,$connection);
  $id = (int) $id;

  //Using prepared statements and parameterized queries:
  $sql = "SELECT * FROM accounts WHERE USER = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("i",$id);

  return executeQueryPrepared($stmt, $connection, true);
}

// select account account by user id
function selectAccountById($id) {
  $connection = openDb();

  //Using prepared statements:
  $id = (int) $id;
  $sql = "SELECT * FROM accounts WHERE ID = ?";
  $stmt = $connection->stmt_init();
  if(!$stmt->prepare($sql)) {
    return false;
  }
  $stmt->bind_param("i",$id);

  return executeQueryPrepared($stmt, $connection, true);
}

?>

