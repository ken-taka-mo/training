<?php
require_once('../utils/functions.php');
require_once('../utils/prefectures.php');
session_start();

if (empty($_SESSION['register'])) {
    header('Location: index.php');
    exit();
} else {
    $register = $_SESSION['register'];
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <main>
        <div class="container">
            <div class="heding">
                <h1>入力内容確認</h1>
            </div>
            <form action="" method="POST">
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
                <a href="../index.php">戻る</a> || <input type="submit" value="作成">
            </form>
        </div>
    </main>
</body>
</html>