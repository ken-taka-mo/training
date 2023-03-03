<?php
require_once('../databese/dbconnect.php');
require_once('../utils/functions.php');
// postで会社idを受け取る
if (!is_exact_id($_POST['id'])) {
    header('Location: index.php');
    exit();
}
$companyId = $_POST['id'];

// 受け取ったidが存在するかチェック
$companyCountStmt = $db->prepare('SELECT COUNT(*) AS cnt FROM companies WHERE id=:id AND deleted is NULL');
$companyCountStmt->execute([':id' => $companyId]);
$companyCount = $companyCountStmt->fetch(PDO::FETCH_ASSOC);

if (!$companyCount['cnt']) {
    header('Location: index.php');
    exit();
}

// 受け取ったidを会社idとして持つ請求テーブル、見積テーブルの子データも論理削除
$delQuotationStmt = $db->prepare("UPDATE quotations SET deleted=NOW(), modified=NOW() WHERE company_id=:company_id");
$delQuotationStmt->execute([':company_id' => $companyId]);
$delInvoiceStmt = $db->prepare("UPDATE invoices SET deleted=NOW(), modified=NOW() WHERE company_id=:company_id");
$delInvoiceStmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
$delInvoiceStmt->execute();
$updateStmt = $db->prepare("UPDATE companies SET deleted=NOW(), modified=NOW() WHERE id=:id");
$updateStmt->bindParam(':id', $companyId, PDO::PARAM_INT);
$updateStmt->execute();
header('Location: index.php');
exit();
