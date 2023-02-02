<?php
require_once("../dbconnect.php");

if (isset($_GET['id'])) {
    if (preg_match('/^[0-9]+$/', $_GET['id']) && $_GET['id'] != 0) {
        $id = $_GET['id'];
        $statement = $db->prepare("DELETE FROM companies WHERE id=?");
        $statement->bindParam(1, $id, PDO::PARAM_INT);
        $statement->execute();
        header('Location: index.php');
        exit();
    } else {
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
