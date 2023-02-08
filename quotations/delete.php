<?php
require_once('../dbconnect.php');
if (!isset($_POST['no']) || !preg_match('/^[a-zA-Z0-9]{1,8}?(-q-)[0-9]{8}$/', $_POST['no'])) {
    header('Location: ../companies');
    exit();
}

$no = $_POST['no'];
$statement = $db->prepare('SELECT id, company_id FROM quotations WHERE no=?');
$statement->execute(array($no));
$idArray = $statement->fetch();

if (!$idArray) {
    header('Location: index.php');
} else {
    $deleteStatement = $db->prepare('UPDATE quotations set deleted=NOW(), modified=NOW() WHERE id=?');
    $deleteStatement->bindParam(1, $idArray['id'], PDO::PARAM_INT);
    $deleteStatement->execute();
    if (!empty($_POST['status'])) {
        header("Location: search.php?id={$idArray['company_id']}&status={$_POST['status']}");
        exit();
    }
    header("Location: index.php?id={$idArray['company_id']}");
    exit();
}
