<?php

define('BANK_APP', TRUE);

require_once "../app/user.php";

startSession();

$users = getUsers();

// include header
$pageTitle = "View Users";
include("header.php");

?>

<h3>View Users</h3>
<table class="pure-table">
  <thead>
    <tr>
      <th>#</th>
      <th>User Name</th>
      <th>Email</th>
      <th>Type</th>
      <th>Account No.</th>
      <th>Approved By</th>
      <th>Approved On</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>1</td>
      <td>John Doe</td>
      <td>efe@gmail.com</td>
      <td>Client</td>
      <td>19000489083</td>
      <td>Betty White</td>
      <td>23.09.2014</td>
      <td><a href="">Open</a></td>
    </tr>
  </tbody>
</table>

<?php 

// include footer
include("footer.php"); 

?>