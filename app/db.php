<?php

$con ;
$db_name="bank_db";

function initConn(){

  $db_host="localhost";
  $db_username="";
  $db_pass="";

  $GLOBALS["con"] = mysql_connect($db_host,$db_username,$db_pass);//connects to the DB in here
}

// opens a databse connection
function openDb() {
    
  initConn();

  if (!$GLOBALS["con"]){
      die('Could not connect: ' . mysql_error());
  }

  $db_select = mysql_select_db($GLOBALS["db_name"], $GLOBALS["con"]);

  mysql_query("SET NAMES 'utf8'");
  mysql_query("SET character_set_connection = 'utf8");
  mysql_query("SET character_set_client = 'utf8'");
  mysql_query("SET character_set_results = 'utf8'");

  if(!$db_select){ 
    die("Error when selecting the DB:".mysql_error());
  }
}

// closes a database connection 
function closeDb() {

  mysql_close($GLOBALS["con"]);
}

// select all users
function selectUsers() {

  openDb();

  $sql = "SELECT * FROM `users`";

  $check = mysql_query($sql);

  if (!$check){   
    die("Invalid query: " . mysql_error());
  }  
  closeDb();

  return $check;
}

// select a user by ID
function selectUser($id) {

  openDb();

  $sql = "SELECT * FROM `users` WHERE ID = ".$id."";

  $check = mysql_query($sql);

  if (!$check){   
    die("Invalid query: " . mysql_error());
  } 
  closeDb();

  return $check;
}

// select a user by email and password
function selectByEmailAndPassword($email, $password) {

  openDb();

  $sql = "SELECT * FROM `users` WHERE EMAIL = '".$email."' AND PASSWORD = '".$password."'";

  $check = mysql_query($sql);

  if (!$check){   
    die("Invalid query: " . mysql_error());
  }
  closeDb();

  return $check;  
}

// insert into user table
function insertUser($userType, $email, $password, $firstname, $lastname) {

  openDb();
  $date = date('d.m.Y H:i');

  $sql="INSERT INTO users ( USER_TYPE, EMAIL, PASSWORD, FIRST_NAME, LAST_NAME, DATE_CREATED)
        VALUES('".$userType."', '".$email."', '".$password."', '".$firstname."', '".$lastname."', '".$date."');";

  $check = mysql_query($sql);

  if (!$check){   
    die("Invalid query: " . mysql_error());
  }
  closeDb();
}

// update user registration
function updateUserRegistration($id, $approver) {

  openDb();
  $date = date('d.m.Y H:i');

  $sql = "UPDATE users SET APPROVED_BY='".$approver."', DATE_APPROVED='".$date."' WHERE id='".$id."' ";
  
  $check = mysql_query($sql);

  if (!$check){   
    die("Invalid query: " . mysql_error());
  }  
  closeDb();
}

// select all transactions
function selectTransactions() {
  
  openDb();
  $sql = "SELECT * FROM `transactions`";

  $check = mysql_query($sql);

  if (!$check){   
    die("Invalid query: " . mysql_error());
  }  
  closeDb();

  return $check;  
}

// select single transactions
function selectTransaction($id) {

  openDb();
  $sql = "SELECT * FROM `transactions` WHERE ID = ".$id."";

  $check = mysql_query($sql);

  if (!$check){   
    die("Invalid query: " . mysql_error());
  }  
  closeDb();

  return $check;
}

// insert into transactions table
function insertTransaction($sender, $recipient, $amount, $tan) {
  
  openDb();
  $date = date('d.m.Y H:i');

  if ($amount >= 10000) {
  
    $sql="INSERT INTO transactions ( SENDER_ACCOUNT, RECIPIENT_ACCOUNT, AMOUNT, TAN_ID, DATE_CREATED, APPROVED_BY)
          VALUES('".$sender."', '".$recipient."', '".$amount."', '".$tan."', '".$date."', "0");";

    $check = mysql_query($sql);

    if (!$check){   
      die("Invalid query: " . mysql_error());
    }

  }else if ($amount < 10000 && $amount >= 0) {
  
    $sql="INSERT INTO transactions ( SENDER_ACCOUNT, RECIPIENT_ACCOUNT, AMOUNT, TAN_ID, DATE_CREATED, APPROVED_BY, DATE_APPROVED)
          VALUES('".$sender."', '".$recipient."', '".$amount."', '".$tan."', '".$date."', "0", '".$date."');";

    $check = mysql_query($sql);

    if (!$check){   
      die("Invalid query: " . mysql_error());
    }

  }else{
    echo "Invalid amount";
  }
  closeDb();
}

// update transaction approval
function updateTransactionApproval($id, $approver, $decison) {
  // $decision = A / D / P. Approved, Denied, Pending
  openDb();
  $date = date('d.m.Y H:i');

  $sql = "UPDATE transactions SET APPROVED_BY='".$approver."', DATE_APPROVED='".$date."', STATUS='".$decision."' WHERE id='".$id."' ";

  $check = mysql_query($sql);

  if (!$check){   
    die("Invalid query: " . mysql_error());
  }
  closeDb();
}

// insert new tans
function insertTan($userAccount, $tan) {
  // default tan status is V - valid
  openDb();
  $date = date('d.m.Y H:i');

  $sql="INSERT INTO tans ( TAN_NUMBER, CLIENT_ACCOUNT, DATE_CREATED)
        VALUES('".$tan."', '".$userAccount."', '".$date."');";

  $check = mysql_query($sql);

  if (!$check){   
    die("Invalid query: " . mysql_error());
  }
  closeDb();
}

// update tan
function updateTanStatus($tanId, $status) {
  // possible values = U / V. Used, Valid.
  openDb();

  $sql = "UPDATE tans SET STATUS='".$status."' WHERE ID='".$tanId."' ";

  $check = mysql_query($sql);

  if (!$check){   
    die("Invalid query: " . mysql_error());
  }
  closeDb();
}

// select tan
function getSingleTan($tan) {

  openDb();
  $sql = "SELECT * FROM `tans` WHERE ID = ".$id."";

  $check = mysql_query($sql);

  if (!$check){   
    die("Invalid query: " . mysql_error());
  }  
  closeDb();

  return $check;
}

?>