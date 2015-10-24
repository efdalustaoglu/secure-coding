<?php

require_once "db.php";

// set session variables
function saveSession($username, $usertype) {
  $_SESSION['username'] = $username;
  $_SESSION['usertype'] = $usertype;
}

// start session
function startSession() {
  session_start();
}

// check if user is authenticated
function isUserAuth() {
  return !empty($_SESSION['username']);
}

// get session properties of authorized user
function getAuthUser() {
  $props = array(
    "username" => $_SESSION['username'],
    "usertype" => $_SESSION['usertype']
  );
  return (object) $props;
}

// logs in user
function login($email, $password) {

}

// destroy user session
function logout () {
  session_destroy();
  header("Location: "."login.php");
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

}

// update tan status
function checkTanUniqueness($tan) {
  
}


?>