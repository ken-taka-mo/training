<?php
require_once("../dbconnect.php");

if (empty($_GET['id']) || !preg_match('/^\d*[1-9]+$/', $_GET['id'])) {
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

$updateStmt = $db->prepare("UPDATE companies SET deleted=NOW(), modified=NOW() WHERE id=?");
$updateStmt->bindParam(1, $id, PDO::PARAM_INT);
$updateStmt->execute();
header('Location: index.php');
exit();
