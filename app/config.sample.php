<?php 

if(!defined('BANK_APP')) { die('Direct access not permitted'); }
define("APP_PATH", "/nikos/secure-coding");
define("APP_DOMAIN", "localhost");

// define constants
define("DB_HOST", "localhost");
define("DB_NAME", "bank_db");
function get_db_credentials($usertype) {
  switch ($usertype) {
    case 'L':
      define("DB_USER", "login");
      define("DB_PASSWORD", "login");
      break;
    case 'R':
      define("DB_USER", "register");
      define("DB_PASSWORD", "register");
      break;
    case 'C':
      define("DB_USER", "client");
      define("DB_PASSWORD", "client");
      break;
    case 'E':
      define("DB_USER", "employee");
      define("DB_PASSWORD", "employee");
      break;
    default:
  } 
}

?>
