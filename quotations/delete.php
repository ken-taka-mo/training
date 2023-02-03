<?php
require_once('../dbconnect.php');
if (!isset($_GET['no']) || !preg_match('/^[a-zA-Z0-9]{1,8}?(-q-)[0-9]{8}$/', $_GET['no'])) {
    header('Location: ../companies');
    exit();
}

$no = $_GET['no'];
$statement = $db->prepare('SELECT id, company_id FROM quotations WHERE no=?');
$statement->execute(array($no));
$idArray = $statement->fetch();

if (!$idArray) {
    header('Location: index.php');
} else {
    $deleteStatement = $db->prepare('UPDATE quotations set deleted=NOW() WHERE id=?');
    $deleteStatement->bindParam(1, $idArray['id'], PDO::PARAM_INT);
    $deleteStatement->execute();
    header("Location: index.php?id={$idArray['company_id']}");
    exit;
}
