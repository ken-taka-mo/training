<?php
require_once("../dbconnect.php");

if (empty($_POST['id']) || !preg_match('/^[1-9]+[0]*$/', $_POST['id'])) {
    header('Location: index.php');
    exit();
}

$countStmt = $db->prepare('SELECT COUNT(*) AS cnt FROM companies WHERE id=?');
$countStmt->execute(array($_POST['id']));
$count = $countStmt->fetch();
if ($count['cnt']) {
    $id = $_POST['id'];
} else {
    header('Location: index.php');
    exit();
}

$updateStmt = $db->prepare("UPDATE companies SET deleted=NOW(), modified=NOW() WHERE id=?");
$updateStmt->bindParam(1, $id, PDO::PARAM_INT);
$updateStmt->execute();
$delQuotationStmt = $db->prepare("UPDATE quotations SET deleted=NOW(), modified=NOW() WHERE company_id=?");
$delQuotationStmt->bindParam(1, $id, PDO::PARAM_INT);
$delQuotationStmt->execute();
$delInvoiceStmt = $db->prepare("UPDATE invoices SET deleted=NOW(), modified=NOW() WHERE company_id=?");
$delInvoiceStmt->bindParam(1, $id, PDO::PARAM_INT);
$delInvoiceStmt->execute();
header('Location: index.php');
exit();
