<?php

require_once "db.php";

// set session variables
function saveSession($username) {

}

// start session
function startSession() {
  session_start();
}

// logs in user
function login($email, $password) {

}

// destroy user session
function logout () {

}

// creates a user: employee or client
function createUser($userType, $email, $password, $firstname, $lastname) {

}

// gets all users in the databse
function getUsers() {
  selectUsers();
}

// gets a single user from the db
function getSingUser($id) {
  selectUser($id);
}

// approves a user registration
function approveRegistration($id, $approver) {

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


?>