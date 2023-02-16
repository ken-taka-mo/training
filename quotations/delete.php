<?php
require_once('../dbconnect.php');

$no = $_POST['no'];
$status = $_POST['status'];

if (!isset($no) || !preg_match('/^[a-zA-Z0-9]{1,8}?(-q-)[0-9]{8}$/', $no)) {
    header('Location: ../companies/index.php');
    exit();
}


$idStmt = $db->prepare('SELECT id, company_id FROM quotations WHERE no=:no');
$idStmt->execute([':no' => $no]);
$idArray = $idStmt->fetch();

if (!$idArray) {
    header('Location: index.php');
} else {
    $deleteStatement = $db->prepare('UPDATE quotations set deleted=NOW(), modified=NOW() WHERE id=:id');
    $deleteStatement->bindParam(':id', $idArray['id'], PDO::PARAM_INT);
    $deleteStatement->execute();
    if (!empty($status)) {
        header("Location: search.php?id={$idArray['company_id']}&status={$status}");
        exit();
    }
    header("Location: index.php?id={$idArray['company_id']}");
    exit();
}
