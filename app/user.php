<?php

if(!defined('BANK_APP')) { die('Direct access not permitted'); }

require_once "db.php";

// set session variables
function saveSession($email, $usertype, $firstname, $lastname, $userid) {
  $_SESSION['userid'] = $userid;
  $_SESSION['firstname'] = $firstname;
  $_SESSION['lastname'] = $lastname;
  $_SESSION['email'] = $email;
  $_SESSION['usertype'] = $usertype;
}

// start session
function startSession() {
  session_start();
}

// check if user is authenticated
function isUserAuth() {
  return !empty($_SESSION['email']);
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

  if (count($login) !== 1) {
    $return->value = false;
    $return->msg = "Invalid login credentials";
    return $return;
  }

  // save user to session
  $firstname = $login['firstname'];
  $lastname = $login['lastname'];
  $userid = $login['userid'];
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
  $return  = returnValue();
  $approval = updateUserRegistration($id, $approver, $decision); //why is $decision not included in updateUserRegistration()?
  if (!approval) {
    $return->value = false;
    $return->msg = "Approval failed";
    return $return;
  }
  $return->value = true;
  $return->msg = "Approval successful";
  return $return;
}

// create 100 tans
function createTans($userAccount) {

	for ($i=0; $i < 100; $i++) { 
		
		$newTan = generateTan();
		insertTan($userAccount, $newTan);
	}
}

function generateTan(){
  $characters = '0123456789ABCDEFGHIJKLMNOPRSTUVWXYZ';
  $randomString = '';
  for ($i = 0; $i < 15; $i++)
    $randomString .= $characters[rand(0, strlen($characters) - 1)];

  return $randomString;
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


?>
