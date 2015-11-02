<?php

if(!defined('BANK_APP')) { die('Direct access not permitted'); }

require_once "db.php";

// gets all transactions
function getTransactions() {
  return selectTransactions();
}

// gets filtered transactions
function getTransactionsByAccountId($id) {
  return selectTransactionsByAccountId($id);
}

// gets a single transaction
function getSingleTransaction($id) {
  return selectTransaction($id);
}


// creates a transaction
function createTransaction($sender, $recipient, $amount, $tan) {
  $return = returnValue();
  
  if ($recipient == $sender) {
    $return->value = false;
    $return->msg = "Recipient account must be different from sender.";
    return $return;
  }

  if (!is_numeric($amount)) {
    $return->value = false;
    $return->msg = "Amount must be a number";
    return $return;
  }

  $recipientAccount = selectAccountByNumber($recipient);
  if (!$recipientAccount) {
    $return->value = false;
    $return->msg = "Recipient account not found";
    return $return;
  }
  
  $senderAccount = selectAccountByNumber($sender);
  if ($senderAccount->BALANCE < $amount) {
    $return->value = false;
    $return->msg = "Insufficient funds";
    return $return;
  }

  $tanEntry = selectTanByTan($tan);
  if (!$tanEntry) {
    $return->value = false;
    $return->msg = "Invalid TAN";
    return $return;
  }

  if ($tanEntry->CLIENT_ACCOUNT !== $senderAccount->ID || $tanEntry->STATUS !== "V") {
    $return->value = false;
    $return->msg = "Invalid TAN";
    return $return;
  }
  
  $invalidateTan = updateTanStatus($tanEntry->ID);
  if (!$invalidateTan) {
    $return->value = false;
    $return->msg = "Tan update failed";
    return $return;
  }

  $insert = insertTransaction($senderAccount->ID, $recipientAccount->ID, $amount, $tanEntry->ID);
  if (!$insert) {
    $return->value = false;
    $return->msg = "Transaction failed";
    return $return;
  }

  if ($amount <= 10000) {
    $balance = updateBalance($senderAccount->ID, $recipientAccount->ID, $amount);

    if (!$balance) {
      $return->value = false;
      $return->msg = "Error updating balance";
      return $return;
    }
  }

  $return->value = true;
  $return->msg = "Transaction successful";
  return $return;
}

// approve / deny a transaction
function approveTransaction($id, $approver, $decision) {
  $return  = returnValue();

  $transaction = selectTransaction($id);
  if (!$transaction) {
    $return->value = false;
    $return->msg = "Invalid transaction id";
    return $return;
  }

  $user = selectUser($approver);
  if (!$user || $user->USER_TYPE !== "E") {
    $return->value = false;
    $return->msg = "Invalid approver";
    return $return;
  }

  $senderAccount = selectAccountById($transaction->SENDER_ACCOUNT);
  if ($senderAccount->BALANCE < $transaction->AMOUNT) {
    $return->value = false;
    $return->msg = "Insufficient funds";
    return $return;
  }

  $approve = updateTransactionApproval($id, $approver, $decision);
  if (!$approve) {
    $return->value = false;
    $return->msg = "Transaction update failed";
    return $return;
  }

  if ($decision === 'D') {
    $return->value = true;
    $return->msg = "Transaction successfully denied";
    return $return;
  }

  $balance = updateBalance($transaction->SENDER_ACCOUNT, $transaction->RECIPIENT_ACCOUNT, $transaction->AMOUNT);
  if (!$balance) {
    $return->value = false;
    $return->msg = "Error updating balance";
    return $return;
  }
  
  $return->value = true;
  $return->msg = "Transaction successfully approved";
  return $return;
}

// upload transaction file
function uploadTransactionFile() {
  $return = returnValue();
  $filename = basename($_FILES["file"]["name"]);
  $target_dir = "uploads/";
  $target_file = $target_dir . $filename;

  if (!move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
    $return->value = false;
    $return->msg = "Upload failed";
    return $return;
  }

  $return->value = $filename;
  $return->msg = "Upload successful";
  return $return;
}

// generate PDF file
function generatePDF($accountId){
  $transactions = selectTransactionsByAccountId($accountId);  
  $userId = selectAccountById($accountId)->USER;
  $user = selectUser($userId);

  require('FPDF/fpdf.php');
  $pdf = new FPDF();

  // Column headings
  $header = array("Created On", "Sender", "Recipient", "Amount", "Status", "TAN", "Approved By", "Approved On");

  // Column widths
  $w = array(23, 26, 26, 30, 20, 40, 42, 23);

  $pdf->AddPage("L");
  $pdf->SetFont('Arial','B', 12);
  $pdf->Cell(0, 10, "Transaction Summary: ".$user->FIRST_NAME." ".$user->LAST_NAME);
  $pdf->Ln();
  $pdf->SetFont('Arial','', 10);

  for ($i=0; $i < count($header); $i++) {
    $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
  }
  $pdf->Ln();

  // Data
  foreach($transactions as $row) {
    $status = "Pending";
    if ($row->STATUS === "A") {
      $status = "Approved"; 
    } else if ($row->STATUS === "D") {
      $status = "Declined"; 
    }

    $pdf->Cell($w[0], 6, $row->DATE_CREATED, 'LR');
    $pdf->Cell($w[1], 6, $row->SENDER_ACCOUNT_NUM, 'LR');
    $pdf->Cell($w[2], 6, $row->RECIPIENT_ACCOUNT_NUM, 'LR');
    $pdf->Cell($w[3], 6, number_format($row->AMOUNT), 'LR', 0, 'R');
    $pdf->Cell($w[4], 6, $status, 'LR');
    $pdf->Cell($w[5], 6, $row->TAN_NUMBER, 'LR');
    $pdf->Cell($w[6], 6, $row->APPROVED_BY_NAME, 'LR');
    $pdf->Cell($w[7], 6, $row->DATE_APPROVED, 'LR');
    $pdf->Ln();
  }

  // Closing line
  $pdf->Cell(array_sum($w), 0, '', 'T');
  
  $doc = $pdf->Output('transactions.pdf', 'D');//Save the pdf file 
  return $doc;
}

?>

