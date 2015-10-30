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
  selectUser($id);
}

// approves a user registration
function approveRegistration($id, $approver, $decision) {
  $return  = returnValue();
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


/*
  Generates the PDF file that is given by user ID, and returns the created PDF document
*/
function generatePDF($userId){

  require('FPDF/fpdf.php');

  $pdf = new FPDF();//create the instance
  $pdf->AddPage();
  $pdf->SetFont('Helvetica','B',18);//set the font style

  $pdf->Cell(75);//start 7.5 cm from right
  $pdf->Cell(0,10,"Tan Numbers");//name the title
  $pdf->SetFont('Helvetica','',15);

  $pdf->Ln(15);//linebreak

  $tans = getTansByUserId($userId);

  $i = 0;
  foreach ($tans as $tan) {
    
    $pdf->SetFont('Helvetica','B',15);
    $pdf->Cell(15, 10, ($i+1) . " - )");
    $pdf->SetFont('Helvetica','',15);
    $pdf->Cell(0,10," $tan->TAN_NUMBER");
    $pdf->Ln(10);
    $i++;
  }

  //$pdf->Output();//print the pdf file to the screen
  
  $doc = $pdf->Output('', 'S');//Save the pdf file 
  return $doc;
}

function sendEmail($userId){

  require_once('PHPMailer/class.phpmailer.php');
  $doc = generatePDF($userId);

  $mail             = new PHPMailer(); 
  $body="
        Requested Tan Numbers are attached to the e-mail..
  ";
  $mail->CharSet = 'UTF-8';

  $mail->SetFrom('Admin@secoding.com', 'SecureCodingTeam6');//Set the name as you like
  $mail->SMTPAuth = true;
  $mail->Host = "smtp.gmail.com"; // SMTP server
  $mail->SMTPSecure = "ssl";
  $mail->Username = "secoding6@gmail.com"; //account which you want to send mail from
  $mail->Password = "efenikosmaltefdal"; //this is account's password
  $mail->Port = "465";
  $mail->isSMTP();  // telling the class to use SMTP

  $user = getSingleUser($userId);

  $mail->AddAddress("$user->EMAIL", "$user->FIRST_NAME $user->LAST_NAME");
  $mail->Subject    = "SecureCodingTeam6";
  $mail->MsgHTML($body);
  $mail->AddStringAttachment($doc, 'doc.pdf', 'base64', 'application/pdf');
}

function sendEmail2($userId){

  require_once('PHPMailer/class.phpmailer.php');
  $doc = generatePDF($userId);

  $mail             = new PHPMailer(); 
  $body = "
      Tan Numbers

  ";
  $tans = getTansByUserId($userId);

    $i = 0;
    foreach ($tans as $tan) {
    
      $body .= ($i+1)." - ) $tan->TAN_NUMBER";
    $body .= "<br />";
    $i++;
    }

  $mail->CharSet = 'UTF-8';

  $mail->SetFrom('Admin@secoding.com', 'SecureCodingTeam6');//Set the name as you like
  $mail->SMTPAuth = true;
  $mail->Host = "smtp.gmail.com"; // SMTP server
  $mail->SMTPSecure = "ssl";
  $mail->Username = "secoding6@gmail.com"; //account which you want to send mail from
  $mail->Password = "efenikosmaltefdal"; //this is account's password
  $mail->Port = "465";
  $mail->isSMTP();  // telling the class to use SMTP

  $user = getSingleUser($userId);

  $mail->AddAddress("$user->EMAIL", "$user->FIRST_NAME $user->LAST_NAME");
  $mail->Subject    = "SecureCodingTeam6";
  $mail->MsgHTML($body);
}


?>