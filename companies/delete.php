<?php
require_once("../dbconnect.php");

if (empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

if (!preg_match('/^[0-9]+$/', $_GET['id']) || preg_match('/^[0]*$/', $_GET['id'])) {
    header('Location: index.php');
    exit();
}

$hasData = $db->prepare('SELECT COUNT(*) AS cnt FROM companies WHERE id=?');
$hasData->execute(array($_GET['id']));
$count = $hasData->fetch();
if ($count['cnt']) {
    $id = $_GET['id'];
} else {
    header('Location: index.php');
    exit();
}

$statement = $db->prepare("DELETE FROM companies WHERE id=?");
$statement->bindParam(1, $id, PDO::PARAM_INT);
$statement->execute();
header('Location: index.php');
exit();
