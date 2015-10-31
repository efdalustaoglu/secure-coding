<?php

if(!defined('BANK_APP')) { die('Direct access not permitted'); }

require_once "db.php";

// set session variables
function saveSession($email, $usertype, $firstname, $lastname, $userid) {
  startSession();
  $_SESSION['userid'] = $userid;
  $_SESSION['firstname'] = $firstname;
  $_SESSION['lastname'] = $lastname;
  $_SESSION['email'] = $email;
  $_SESSION['usertype'] = $usertype;
}

// start session
function startSession($privileged = false) {
  if (session_id() === '') {
    session_start();
  }

  if ($privileged) {
    checkAccess();
  }
}

function checkAccess() {
  if (!isUserAuth()) {
    logout();
  }
}

// check if user is authenticated
function isUserAuth() {
  return !empty($_SESSION['userid']);
}

// get session properties of authorized user
function getAuthUser() {
  $user = array(
    "email" => $_SESSION['email'],
    "usertype" => $_SESSION['usertype'],
    "firstname" => $_SESSION['firstname'],
    "lastname" => $_SESSION['lastname'],
    "userid" => $_SESSION['userid']
  );
  return (object) $user;
}

// logs in user
function login($email, $password) {
  $return  = returnValue();

  if (empty($email) || empty($password)) {
    $return->value = false;
    $return->msg = "You need to enter email and password";
    return $return;
  }
  
  $password = md5($password);
  $login = selectByEmailAndPassword($email, $password);

  if (!$login) {
    $return->value = false;
    $return->msg = "Invalid login credentials";
    return $return;
  }

  // save user to session
  $firstname = $login->FIRST_NAME;
  $lastname = $login->LAST_NAME;
  $userid = $login->ID;
  $usertype = $login->USER_TYPE;
  saveSession($email, $usertype, $firstname, $lastname, $userid);

  $return->value = true;
  $return->msg = "Login successful";
  return $return;
}

// destroy user session
function logout () {
  session_destroy();
  header("Location: "."login.php");
}

// creates a user: employee or client
function createUser($userType, $email, $password, $confirmPassword, $firstname, $lastname) {
  $return  = returnValue();

  // check for empty fields
  if (empty($firstname) || empty($lastname)) {
    $return->value = false;
    $return->msg = "Firstname or lastname is empty";
    return $return;
  }

  // validate email format
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $return->value = false;
    $return->msg = "Invalid email format";
    return $return;
  }

  // check if usertype is among valid values
  if ($userType !== "E" && $userType !== "C") {
    $return->value = false;
    $return->msg = "Invalid user type";
    return $return;
  }

  // check if passwords match
  if ($password !== $confirmPassword) {
    $return->value = false;
    $return->msg = "Passwords do not match";
    return $return;
  }

  $password = md5($password);
  $insert = insertUser($userType, $email, $password, $firstname, $lastname);

  // check if db operation failed
  if (!$insert) {
    $return->value = false;
    $return->msg = "DB insert operation failed";
    return $return;
  }

  $return->value = true;
  $return->msg = "Registration successful";
  return $return;
}

// gets all users in the databse
function getUsers() {
  return selectUsers();
}

// gets a single user from the db
function getSingleUser($id) {
  return selectUser($id);
}

// approves a user registration
function approveRegistration($id, $approver, $decision) {
  $return = returnValue();
  $update = updateUserRegistration($id, $approver, $decision);

  if (!$update) {
    $return->value = false;
    $return->msg = "DB update operation failed";
    return $return;
  }

  if (!$decision) {
    $return->value = true;
    $return->msg = "User registration denied successfully";
    return $return;
  }

  // create user's account number
  $accountNumber = generateAccountNumber($id);
  
  if (!$accountNumber) {
    $return->value = false;
    $return->msg = "Error updating user account number";
    return $return;
  }

  // send email to user with 100 tans
  $tans = createTans($id);
  if (!$tans) {
    $return->value = false;
    $return->msg = $tans->msg;
    return $return;
  }

  $return->value = true;
  $return->msg = "User approval successful";
  return $return;
}

// create 100 tans
function createTans($id) {
  $tansUnique = false;  

  while ($tansUnique === false) {
    // get tans from C program
    $tans = ""; 

    $temp = true;
    foreach($tans as $tan) {
      if (!checkTanUniqueness($tan)) {
        $temp = false;
        break;
      }
    }

    if ($temp) {
      $tansUnique = true;
    }
  }

  // get user's account number
  $accNumber = getAccountById($id);

  // insert tans into db
  $insert = 
  
  // send email to user with tans
}

// update tan status
function checkTanUniqueness($tan) {
  
}

// get account data for a user
function getAccountById($id) {

}

// get account data for a specific account number
function getAccountByAccountNumber($number) {

}

function sendEmail() {

}

function generateAccountNumber($id) {
  $accountNumber = $id + 1000000000;
  return insertAccount($id, $accountNumber);
}


?>