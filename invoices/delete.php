<?php
require_once('../dbconnect.php');
if (!isset($_POST['no']) || !preg_match('/^[a-zA-Z0-9]{1,8}?(-i-)[0-9]{8}$/', $_POST['no'])) {
    header('Location: ../companies');
    exit();
}
$no = $_POST['no'];
$statement = $db->prepare('SELECT id, company_id FROM invoices WHERE no=?');
$statement->execute(array($no));
$idArray = $statement->fetch();

if (!$idArray) {
    header('Location: index.php');
} else {
    $deleteStatement = $db->prepare('UPDATE invoices set deleted=NOW(), modified=NOW() WHERE id=?');
    $deleteStatement->bindParam(1, $idArray['id'], PDO::PARAM_INT);
    $deleteStatement->execute();
    if (preg_match('/^[0-9]+$/', $_POST['min']) && preg_match('/^[0-9]+$/', $_POST['max'])) {
        header("Location: search.php?id={$idArray['company_id']}&min={$_POST['min']}&max={$_POST['max']}");
        exit();
    }
    header("Location: index.php?id={$idArray['company_id']}");
    exit();
}
