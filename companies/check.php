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
    $prefectureCode = intval($register['prefecture_code']);
}

if (!empty($_POST)) {
    $insertStmt = $db->prepare('INSERT INTO companies SET
    name=:name,
    manager_name=:manager_name,
    phone_number=:phone_number,
    postal_code=:postal_code,
    prefecture_code=:prefecture_code,
    address=:address,
    mail_address=:mail_address,
    prefix=:prefix,
    created=NOW(),
    modified=NOW()');
    $insertStmt->bindParam(':name', $register['name']);
    $insertStmt->bindParam(':manager_name', $register['manager_name']);
    $insertStmt->bindParam(':phone_number', $register['phone_number']);
    $insertStmt->bindParam(':postal_code', $register['postal_code']);
    $insertStmt->bindParam(':prefecture_code', $prefectureCode, PDO::PARAM_INT);
    $insertStmt->bindParam(':address', $register['address']);
    $insertStmt->bindParam(':mail_address', $register['mail_address']);
    $insertStmt->bindParam(':prefix', $register['prefix']);
    $insertStmt->execute();
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
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="create-page">
        <div class="container">
            <div class="heading">
                <h1>入力内容確認</h1>
                <a href="index.php" class="btn-back">戻る</a>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="action" value="submit">
                <div class="form-items">
                    <div class="item">
                        <h3 class="item-title">会社名</h3>
                        <div class="form-wrapper"><?= h($register['name']) ?></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">担当者</h3>
                        <div class="form-wrapper"><?= h($register['manager_name'])?></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">電話番号</h3>
                        <div class="form-wrapper"><?= h($register['phone_number'])?></div>
                    </div>
                    <div class="item address-items">
                        <h3 class="item-title">住所</h3>
                        <div class="address-item-wrapper">
                            <div class="address-item">
                                <h4>郵便番号</h4>
                                <?= h(substr_replace($register['postal_code'], '-', 3, 0)) ?>
                            </div>
                            <div class="address-item">
                                <h4>都道府県</h4>
                                <?= PREFECTURES[h($register['prefecture_code'])]?>
                            </div>
                            <div class="address-item">
                                <h4>市区町村</h4>
                                <?= h($register['address'])?>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">メールアドレス</h3>
                        <div class="form-wrapper"><?= h($register['mail_address'])?></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">プレフィックス</h3>
                        <div class="form-wrapper"><?= h($register['prefix'])?></div>
                    </div>
                </div>
                <a href="./register.php?action=rewrite">&laquo;&nbsp;書き直す</a> || <input class="btn btn-form" type="submit" value="登録">
            </form>
        </div>
    </main>
</body>
</html>