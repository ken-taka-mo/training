<?php
require_once("../dbconnect.php");

$companyId = $_POST['id'];
if (empty($companyId) || !preg_match('/^[1-9]+[0]*$/', $companyId)) {
    header('Location: index.php');
    exit();
}

$countStmt = $db->prepare('SELECT COUNT(*) AS cnt FROM companies WHERE id=:id');
$countStmt->execute([':id' => $companyId]);
$count = $countStmt->fetch();
if ($count['cnt'] < 1) {
    header('Location: index.php');
    exit();
}

$delQuotationStmt = $db->prepare("UPDATE quotations SET deleted=NOW(), modified=NOW() WHERE company_id=:company_id");
$delQuotationStmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
$delQuotationStmt->execute();
$delInvoiceStmt = $db->prepare("UPDATE invoices SET deleted=NOW(), modified=NOW() WHERE company_id=:company_id");
$delInvoiceStmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
$delInvoiceStmt->execute();
$updateStmt = $db->prepare("UPDATE companies SET deleted=NOW(), modified=NOW() WHERE id=:id");
$updateStmt->bindParam(':id', $companyId, PDO::PARAM_INT);
$updateStmt->execute();
header('Location: index.php');
exit();
