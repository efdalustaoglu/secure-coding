<?php

require_once "../app/user.php";

// start session if page the header is included in is privilieged
if (isset($privileged) && $privileged) {
  startSession();
}

?>

<!doctype html>
<html>
<head>
  <title><?php echo $pageTitle; ?></title>
  <link rel="stylesheet" href="assets/css/pure-min.css">
  
  <!-- css overwrite -->
  <style type="text/css">
  body {
    padding: 15px;
  }

  .container {
    width: 1000px;
    margin: 0 auto;
  }

  .pull-right {
    float: right;
  }

  .divider {
    border-top: 1px solid #eee;
    margin: 15px 0;
  }

  .show-msg {
    padding: 10px;
    background-color: #afeeee;
    color: #000;
  }

  .button-success,
  .button-error {
    color: white;
    border-radius: 4px;
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
  }

  .button-success {
    background: rgb(28, 184, 65);
  }

  .button-error {
    background: rgb(202, 60, 60);
  }
  </style>
</head>

<body>
<div class="container">
  <!-- header -->
  <div class="pure-g">
    <div class="pure-u-1-1">
      <div class="pure-menu pure-menu-horizontal">
        <a href="#!" class="pure-menu-heading pure-menu-link"><b>BANK-APP</b></a>
        <?php if (isUserAuth()): ?>
        <ul class="pure-menu-list">
          <li class="pure-menu-item">
            <a href="<?php echo 'view_transactions.php'; ?>" class="pure-menu-link">Transaction</a>
          </li>
          <li class="pure-menu-item">
            <a href="<?php echo 'view_users.php'; ?>" class="pure-menu-link">User</a>
          </li>
        </ul>
        <ul class="pure-menu-list pull-right">
          <li class="pure-menu-item">
            <a href="#!" class="pure-menu-link">
              <b><?php echo getAuthUser()->email; ?></b>
            </a>
          </li>
          <li class="pure-menu-item">
            <a href="<?php echo 'logout.php'; ?>" class="pure-menu-link">Logout</a>
          </li>
        </ul>
      <?php endif; ?>
      </div>
      <div class="divider"></div>

<?php

// show error/success message, if any
if (isset($showMsg) && !empty($showMsg)) {
  echo "<div class='show-msg'>" . $showMsg . "</div>";
}

?>