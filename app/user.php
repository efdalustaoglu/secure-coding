<?php

if(!defined('BANK_APP')) { die('Direct access not permitted'); }

require_once "db.php";
require_once "transaction.php";

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
function startSession() {
  if (session_id() === '') {
    session_start();
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
  
  #$password = md5($password);
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
  $res = getAccountByUId($id);
  return ($res);
}

// get account data for a specific account number
function getAccountByAccountNumber($number) {
  return getAccountByAccNumber($number);
}

function generatePDF($userId){
  require('../FPDF/fpdf.php');
  $pdf = new FPDF();//create the instance
  $pdf->AddPage();
  $pdf->SetFont('Helvetica','B',18);//set the font style

  
  $transactions = getTransactions(true);
  $account = getAccountByUId($userId);
  $pdf->SetFont('Helvetica','B',11);
  $pdf->Cell(79, 10, "#  Created On  Sender  Recipient  Amount  Status  Tan  Approved By  Approved On");
  $pdf->Ln(10);
  foreach($transactions as $transaction) {
    if($account->ACCOUNT_NUMBER == $transaction->SENDER_ACCOUNT) {
      if ($transaction->STATUS === "A") $status = "Approved"; 
      else if ($transaction->STATUS === "D") $status = "Declined"; 
      else $status = "Pending";
      $pdf->SetFont('Helvetica','B',11);
      $pdf->Cell(90, 10, "$transaction->ID $transaction->DATE_CREATED $transaction->SENDER_ACCOUNT $transaction->RECIPIENT_ACCOUNT $transaction->AMOUNT $status $transaction->TAN_ID $transaction->APPROVED_BY $transaction->DATE_APPROVED");
      $pdf->SetFont('Helvetica','',15);
      //$pdf->Cell(0,10," $tan->TAN_NUMBER");
      $pdf->Ln(10);

        
        
    }
  }
  //$pdf->Output();//print the pdf file to the screen
  
  $doc = $pdf->Output('transactions.pdf', 'D');//Save the pdf file 
  return $doc;
}


?>
