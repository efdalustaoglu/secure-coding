<?php

if(!defined('BANK_APP')) { die('Direct access not permitted'); }

require_once "db.php";
require_once "transaction.php";

//CSRF token
function createCSRFToken($action) {
    $data[$action . 'token'] = md5(uniqid(rand(), true));
    $_SESSION[$action . 'token'] = $data[$action . 'token'];
}

function clearCSRFToken() {
  if (isset($_SESSION['usertoken'])) {
    unset($_SESSION['usertoken']);
  }
  if (isset($_SESSION['transactiontoken'])) {
    unset($_SESSION['transactiontoken']);
  }
  if (isset($_SESSION['newtransactiontoken'])) {
    unset($_SESSION['newtransactiontoken']);
  }
}

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
    $secure = false;
    //$secure = true;
    $httponly = true;
    //ini_set('session.use_only_cookies',1);
    $path = APP_PATH;
    $domain = APP_DOMAIN;
    //$domain = $_SERVER['SERVER_ADDR'];
    session_set_cookie_params(0, $path, $domain, $secure, $httponly);
    session_start();
  }

  if ($privileged) {
    checkAccess();
  }
}

function checkAccess() {
  if (!isUserAuth()) {
    logout();
    //Ensure user does not receive sensitive content 4.4.3
    die("Unauthorized access");
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
  getDBCredentials('L');

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

// returns the password of the user
function rememberPassword($email){
  $return  = returnValue();
    
  // validate email format
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $return->value = false;
    $return->msg = "Invalid email format";
    return $return;
  }
  
  $return->value = true;
  $return->msg = "Your password successfully sent your e-mail address";
  return $return;
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

  //Whitelist name/surname fields
  if (preg_match('/[^A-Za-z\']/',$firstname)) {
    $return->value = false;
    $return->msg = "Invalid First Name";
    return $return;
  } 
  if (preg_match('/[^A-Za-z\']/',$lastname)) {
    $return->value = false;
    $return->msg = "Invalid Last Name";
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
  getDBCredentials('R');
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
  getDBCredentials(getAuthUser()->usertype);
  return selectUsers();
}

// gets a single user from the db
function getSingleUser($id) {
  getDBCredentials(getAuthUser()->usertype);
  return selectUser($id);
}


//Provisioning 4.4.3
function privilegedUserAction() {
  if (getAuthUser()->usertype != 'E') {
    die("Unauthorized access");
  }
}

// approves a user registration
function approveRegistration($id, $approver, $decision) {
  privilegedUserAction();
  $return = returnValue();
  getDBCredentials(getAuthUser()->usertype);

  //Ensure that users are approved only once 4.6.3
  $user = getSingleUser($id);
  if ($user->APPROVED_BY != NULL) {
    $return->value = false;
    $return->msg = "Invalid action";
    return $return;
  }

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
  if (!$tans->value) {
    $return->value = false;
    $return->msg = $tans->msg;
    return $return;
  }

  $return->value = true;
  $return->msg = "User approval successful";
  return $return;
}

// create user's tans
function createTans($id) {
  $return = returnValue();
  // get user's account number
  $accountId = getAccountByUserId($id)->ID;

  // generate 100 tans
  for ($i = 0; $i < 100; $i++) {
    $tanUnique = false; 

    while(!$tanUnique) {
      $tan = generateTan();

      // check if tan is unique
      if (checkTanUniqueness($tan)) {
        
        // save tan if it is unique
        if (insertTan($tan, $accountId)) {
          $tanUnique = true;
        } else {
          $return->value = false;
          $return->msg = "Error inserting tans to DB";
          return $return;
        }
      }
    }
  }
  
  // send email to user with tans
  if (!sendTanEmail($id, $accountId)) {
    $return->value = false;
    $return->msg = "Error sending tan email";
    return $return;
  }

  $return->value = true;
  $return->msg = "Tan creation process successful";
  return $return;
}

function generateTan() {
  $characters = '0123456789ABCDEFGHIJKLMNOPRSTUVWXYZ';
  $randomString = '';
  for ($i = 0; $i < 15; $i++) {
    $randomString .= $characters[rand(0, strlen($characters) - 1)];
  }
  return $randomString;
}


// check tan number for uniqueness
function checkTanUniqueness($tan) {
  return (selectTanByTan($tan) === null) ? true : false;
}

// get account data for a user
function getAccountByUserId($id) {
  return selectAccountByUserId($id);
}

// get account data for a specific account number
function getAccountByAccountNumber($number) {
  return selectAccountByNumber($number);
}

// generate a user's account number
function generateAccountNumber($id) {
  $account = selectAccountByUserId($id);
  if ($account) {
    return $account->ACCOUNT_NUMBER;
  }

  $accountNumber = $id + 1000000000;
  return insertAccount($id, $accountNumber);
}

function sendTanEmail($userId, $accountId) {
  $tans = selectTansByUserId($accountId);
  $user = selectUser($userId);
  $email = $user->EMAIL;
  $name = $user->FIRST_NAME . " " . $user->LAST_NAME;

  $subject = "Tan Numbers - ".$name;
  $body = "";

  for ($i = 0; $i < count($tans); $i++) {
    $body .= ($i + 1).". ".$tans[$i]->TAN_NUMBER."<br/>" ;
  }

  return sendEmailWithPDF($userId, $email, $name, $subject, $body);
}

function sendRegistrationEmail($userId) {
  $tans = selectTansByUserId($accountId);
  $user = selectUser($userId);
  $email = $user->EMAIL;
  $name = $user->FIRST_NAME . " " . $user->LAST_NAME;

  $subject = "Tan Numbers - ".$name;
  $body = "";

  for ($i = 0; $i < count($tans); $i++) {
    $body .= ($i + 1).". ".$tans[$i]->TAN_NUMBER."<br/>" ;
  }

  return sendEmail($email, $name, $subject, $body);
}

function sendEmail($email, $name, $subject, $body) {
  require_once('PHPMailer/class.phpmailer.php');
  $mail = new PHPMailer();
  $mail->CharSet = 'UTF-8';
  $mail->SetFrom('Admin@secoding.com', 'SecureCodingTeam6');
  $mail->SMTPAuth = true;
  $mail->Host = "smtp.gmail.com";
  $mail->SMTPSecure = "ssl";
  $mail->Username = "secoding6@gmail.com";
  $mail->Password = "efenikosmaltefdal"; 
  $mail->Port = "465";
  $mail->isSMTP();
  $mail->AddAddress($email, $name);
  $mail->Subject = $subject;
  $mail->MsgHTML($body);

  if (!$mail->send()) {
    return false;
  }

  return true;
}

function sendEmailWithPDF($userId, $email, $name, $subject, $body){
  require_once('PHPMailer/class.phpmailer.php');
  $doc = generatePDF($userId);
  $password = randomPassword();  
  $mail = new PHPMailer(); 
  $body="Requested Tan Numbers are attached to the e-mail..\n\n<br /><br />Password:$password";
  $mail->CharSet = 'UTF-8';
  $mail->SetFrom('Admin@secoding.com', 'SecureCodingTeam6');//Set the name as you like
  $mail->SMTPAuth = true;
  $mail->Host = "smtp.gmail.com"; // SMTP server
  $mail->SMTPSecure = "ssl";
  $mail->Username = "secoding6@gmail.com"; //account which you want to send mail from
  $mail->Password = "efenikosmaltefdal"; //this is account's password
  $mail->Port = "465";
  $mail->isSMTP();
  $user = getSingleUser($userId);
  $mail->AddAddress($email, $name);
  $mail->Subject = $subject;
  $mail->MsgHTML($body);

  $doc->SetProtection(array(), $password); 
  $doc = $doc->Output('', 'S');//Save the pdf file

  $mail->AddStringAttachment($doc, 'doc.pdf', 'base64', 'application/pdf');

  if (!$mail->send()) {
    return false;
  }

  return true;
}



/*
  Generates the PDF file that is given by user ID, and returns the created PDF document
*/
function generatePDF($userId){
  require('FPDF/fpdf_protection.php');
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

  return $doc2;
}

function randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 10; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function cleanInput($inputString){
  $inputString = htmlspecialchars($inputString, ENT_QUOTES);
  if (preg_match("/[^A-Za-z0-9]/", $inputString)){
    return false;
  }
  return true;
}

?>
 
