<?php
require_once('../databese/dbconnect.php');
// パラメータのバリデーション(no)
if (empty($_POST['no']) || !preg_match('/^[a-zA-Z0-9]{1,8}?(-i-)\d{8}$/', $_POST['no'])) {
    header('Location: ../companies/index.php');
    exit();
}
$no = $_POST['no'];
// $noを持つ請求データのidと会社idを取得
$idStmt = $db->prepare('SELECT id, company_id FROM invoices WHERE no=:no AND deleted is NULL');
$idStmt->execute(['no' => $no]);
$idArray = $idStmt->fetch(PDO::FETCH_ASSOC);
// 取得できなければ会社一覧ページに遷移
if (!$idArray) {
    header('Location: ../companies/index.php');
    exit();
}
// 削除クエリ
$deleteStmt = $db->prepare('UPDATE invoices set deleted=NOW(), modified=NOW() WHERE id=:id');
$deleteStmt->bindParam(':id', $idArray['id'], PDO::PARAM_INT);
$deleteStmt->execute();
if (preg_match('/^[0-9]+$/', $_POST['min']) && preg_match('/^[0-9]+$/', $_POST['max'])) {
    header("Location: search.php?id={$idArray['company_id']}&min={$_POST['min']}&max={$_POST['max']}");
    exit();
}
header("Location: index.php?id={$idArray['company_id']}");
exit();
