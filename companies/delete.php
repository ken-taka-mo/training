<?php
require_once("../dbconnect.php");

if (empty($_POST['id'])) {
    header('Location: index.php');
    exit();
}

if (!preg_match('/^[0-9]*[1-9]+$/', $_POST['id'])) {
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

$statement = $db->prepare("UPDATE companies SET deleted=NOW(), modified=NOW() WHERE id=?");
$statement->bindParam(1, $id, PDO::PARAM_INT);
$statement->execute();
header('Location: index.php');
exit();
