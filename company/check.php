<?php
require_once('../utils/functions.php');
require_once('../utils/prefectures.php');
require_once('../dbconnect.php');
session_start();

if (empty($_SESSION['register'])) {
    header('Location: index.php');
    exit();
} else {
    $register = $_SESSION['register'];
    $prefecture_code = intval($register['prefecture_code']);
}

if (!empty($_POST)) {
    $statement = $db->prepare('INSERT INTO companies SET
    name=?,
    manager_name=?,
    phone_number=?,
    postal_code=?,
    prefecture_code=?,
    address=?,
    mail_address=?,
    prefix=?,
    created=NOW(),
    modified=NOW()');
    $statement->bindParam(1, $register['name']);
    $statement->bindParam(2, $register['manager_name']);
    $statement->bindParam(3, $register['phone_number']);
    $statement->bindParam(4, $register['postal_code']);
    $statement->bindParam(5, $prefecture_code, PDO::PARAM_INT);
    $statement->bindParam(6, $register['address']);
    $statement->bindParam(7, $register['mail_address']);
    $statement->bindParam(8, $register['prefix']);
    $statement->execute();
    unset($_SESSION['register']);
    header('Location: index.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>入力内容確認</title>
</head>
<body>
    <main>
        <div class="container">
            <div class="heding">
                <h1>入力内容確認</h1>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="action" value="submit">
                <div class="form-items">
                    <p>会社名：<?= h($register['name']) ?></p>
                    <p>担当者名：<?= h($register['manager_name'])?></p>
                    <p>電話番号：<?= h($register['phone_number'])?></p>
                    <div>
                        <p>住所</p>
                        <div>
                            <p>郵便番号：<?= h($register['postal_code']) ?></p>
                            <p>都道府県：<?= $prefectures[h($register['prefecture_code'])]?></p>
                            <p>市区町村：<?= h($register['address'])?></p>
                        </div>
                    </div>
                    <p>メールアドレス：<?= h($register['mail_address'])?></p>
                    <p>プレフィックス：<?= h($register['prefix'])?></p>
                </div>
                <a href="./register.php?action=rewrite">&laquo;&nbsp;書き直す</a> || <input type="submit" value="作成">
            </form>
        </div>
    </main>
</body>
</html>