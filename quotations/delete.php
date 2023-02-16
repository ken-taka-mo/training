<?php
require_once('../dbconnect.php');

$no = $_POST['no'];
$status = $_POST['status'];
// 見積番号のバリデーション
if (!isset($no) || !preg_match('/^[a-zA-Z0-9]{1,8}?(-q-)[0-9]{8}$/', $no)) {
    header('Location: ../companies/index.php');
    exit();
}

// 見積番号と会社idの取得
$idStmt = $db->prepare('SELECT id, company_id FROM quotations WHERE no=:no');
$idStmt->execute([':no' => $no]);
$idArray = $idStmt->fetch(PDO::FETCH_ASSOC);

// データがなかった場合会社一覧ページに遷移
if (!$idArray) {
    header('Location: ../companies/index.php');
    exit();
}
// 削除クエリ
$deleteStatement = $db->prepare('UPDATE quotations set deleted=NOW(), modified=NOW() WHERE id=:id');
$deleteStatement->bindParam(':id', $idArray['id'], PDO::PARAM_INT);
$deleteStatement->execute();
// 検索ページから削除した場合もとの検索ページに遷移する
if (isset($status)) {
    header("Location: search.php?id={$idArray['company_id']}&status={$status}");
    exit();
}
header("Location: index.php?id={$idArray['company_id']}");
exit();
