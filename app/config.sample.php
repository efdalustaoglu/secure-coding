<?php 

if(!defined('BANK_APP')) { die('Direct access not permitted'); }
define("APP_PATH", "/secure-coding");
define("APP_DOMAIN", "");

// define constants
define("DB_HOST", "localhost");
define("DB_NAME", "bank_db");
function getDBCredentials($usertype) {
  switch ($usertype) {
    case 'L':
      if(!defined('DB_USER')) {  define("DB_USER", "login"); }
      if(!defined('DB_PASSWORD')) {  define("DB_PASSWORD", "login"); }
      break;
    case 'R':
      if(!defined('DB_USER')) { define("DB_USER", "register"); }
      if(!defined('DB_PASSWORD')) { define("DB_PASSWORD", "register"); }
      break;
    case 'C':
      if(!defined('DB_USER')) { define("DB_USER", "client"); }
      if(!defined('DB_PASSWORD')) { define("DB_PASSWORD", "client"); }
      break;
    case 'E':
      if(!defined('DB_USER')) { define("DB_USER", "employee"); }
      if(!defined('DB_PASSWORD')) { define("DB_PASSWORD", "employee"); }
      break;
    default:
  } 
}

?>
