<?php

define('BANK_APP', TRUE);

if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

require_once "../app/user.php";

startSession();
logout();

?>