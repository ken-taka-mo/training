<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');
require_once('../utils/prefectures.php');

if (empty($_GET['id'])) {
    header('Location: index.php');
    exit();
} elseif (!preg_match('/^[0-9]*[1-9]+$/', $_GET['id'])) {
    header('Location: index.php');
    exit();
}

$countStmt = $db->prepare('SELECT COUNT(*) AS cnt FROM companies WHERE id=? AND deleted is NULL');
$countStmt->execute(array($_GET['id']));
$count = $countStmt->fetch();
if ($count['cnt']) {
    $id = $_GET['id'];
} else {
    header('Location: index.php');
    exit();
}

$detailStmt = $db->prepare('SELECT id, name, manager_name, phone_number, postal_code, prefecture_code, address, mail_address, prefix FROM companies WHERE id=?');
$detailStmt->bindParam(1, $id, PDO::PARAM_INT);
$detailStmt->execute();
$details = $detailStmt->fetch();

if (empty($_POST)) {
    $name = $details['name'];
    $managerName = $details['manager_name'];
    $phoneNumber = $details['phone_number'];
    $postalCode = $details['postal_code'];
    $prefectureCode = $details['prefecture_code'];
    $address = $details['address'];
    $mailAddress = $details['mail_address'];
} else {
    $name = $_POST['name'];
    $managerName = $_POST['manager_name'];
    $phoneNumber = $_POST['phone_number'];
    $postalCode = $_POST['postal_code'];
    $prefectureCode = $_POST['prefecture_code'];
    $address = $_POST['address'];
    $mailAddress = $_POST['mail_address'];

    if (preg_match('/^[\s\n\t]*$/', $name)) {
        $error['name'] = '会社名を入力してください';
    } elseif (mb_strlen($name) > 64) {
        $error['name'] = '会社名は64文字以内で入力してください';
    }

    if (preg_match('/^[\s\n\t]*$/', $managerName)) {
        $error['manager_name'] = '担当者名を入力してください';
    } elseif (mb_strlen($managerName) > 32) {
        $error['manager_name'] = '担当者名は32文字以内で入力してください';
    }

    if (preg_match('/^[\s\n\t]*$/', $phoneNumber)) {
        $error['phone_number'] = '電話番号を入力してください';
    } elseif (mb_strlen($phoneNumber) > 11 || !preg_match('/^\d+$/', $phoneNumber)) {
        $error['phone_number'] = '電話番号はハイフンなしの11桁以下の半角整数で入力してください';
    }

    if (preg_match('/^[\s\n\t]*$/', $postalCode)) {
        $error['postal_code'] = '郵便番号を入力してください';
    } elseif (mb_strlen($postalCode) != 7 || !preg_match('/^\d+$/', $postalCode)) {
        $error['postal_code'] = '郵便番号はハイフンなしの7桁の半角整数で入力してください';
    }

    if (preg_match('/^[\s\n\t]*$/', $prefectureCode)) {
        $error['prefecture_code'] = 'もう一度都道府県を選択してください';
    } elseif (mb_strlen($prefectureCode) < 1 && mb_strlen($prefectureCode > 47)) {
        $error['prefecture_code'] = 'もう一度都道府県を選択してください';
    }

    if (preg_match('/^[\s\n\t]*$/', $address)) {
        $error['address'] = '市区町村を入力してください';
    } elseif (mb_strlen($address) > 100) {
        $error['address'] = '市区町村は100字以内で入力してください';
    }

    if (preg_match('/^[\s\n\t]*$/', $mailAddress)) {
        $error['mail_address'] = 'メールアドレスを入力してください';
    } elseif (mb_strlen($mailAddress) > 100) {
        $error['mail_address'] = 'メールアドレスは100字以内で入力して下さい';
    } elseif (!preg_match('/^[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*@[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*$/', $mailAddress)) {
        $error['mail_address'] = '正しいメールアドレスを入力してください';
    }

    if (!isset($error)) {
        $updateStmt = $db->prepare('UPDATE companies SET
        name=?,
        manager_name=?,
        phone_number=?,
        postal_code=?,
        prefecture_code=?,
        address=?,
        mail_address=?,
        modified=NOW() WHERE id=?');
        $updateStmt->bindParam(1, $name);
        $updateStmt->bindParam(2, $managerName);
        $updateStmt->bindParam(3, $phoneNumber);
        $updateStmt->bindParam(4, $postalCode);
        $updateStmt->bindParam(5, $prefectureCode, PDO::PARAM_INT);
        $updateStmt->bindParam(6, $address);
        $updateStmt->bindParam(7, $mailAddress);
        $updateStmt->bindParam(8, $id, PDO::PARAM_INT);
        $updateStmt->execute();
        header('Location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会社編集</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="edit-page">
        <div class="container">
            <div class="heading">
                <h1>会社編集</h1>
                <a href="index.php" class="btn-back">戻る</a>
            </div>
            <form action="" method="POST" class="edit-form">
                <div class="form-items">
                    <div class="item">
                        <h1 class="item-title">ID</h1>
                        <div class="form-wrapper"><p><?= h($details['id'])?></p></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">会社名</h3>
                        <div class="form-wrapper"><input type="text" name="name" value=<?= h($name) ?>></div>
                    </div>
                    <?php if (isset($error['name'])) :?>
                        <p class="error"><?= $error['name'] ?></p>
                    <?php endif ?>
                    <div class="item">
                        <h3 class="item-title">担当者</h3>
                        <div class="form-wrapper"><input type="text" name="manager_name" value=<?= h($managerName) ?>></div>
                    </div>
                    <?php if (isset($error['manager_name'])) :?>
                        <p class="error"><?= $error['manager_name'] ?></p>
                    <?php endif ?>
                    <div class="item">
                        <h3 class="item-title">電話番号</h3>
                        <div class="form-wrapper"><input type="text" name="phone_number" value=<?= h($phoneNumber) ?>></div>
                    </div>
                    <?php if (isset($error['phone_number'])) :?>
                        <p class="error"><?= $error['phone_number'] ?></p>
                    <?php endif ?>
                    <div class="item address-items">
                        <h3 class="item-title">住所</h3>
                        <div class="address-item-wrapper">
                            <div class="address-item">
                                <h4>郵便番号</h4>
                                <input type="text" name="postal_code" value=<?= h($postalCode) ?>>
                            </div>
                            <div class="address-item">
                                <h4>都道府県</h4>
                                <select name="prefecture_code">
                                    <?php for ($i = 1; $i <= 47; $i++) :?>
                                        <?php if ($prefectureCode == $i) :?>
                                            <option value=<?= $i ?> selected><?= PREFECTURES[$i] ?></option>
                                        <?php else :?>
                                            <option value=<?= $i ?>><?= PREFECTURES[$i] ?></option>
                                        <?php endif ?>
                                    <?php endfor ?>
                                </select>
                            </div>
                            <div class="address-item">
                                <h4>市区町村</h4>
                                <input type="text" name="address" value=<?= h($address) ?>>
                            </div>
                        </div>
                    </div>
                    <?php if (isset($error['postal_code'])) :?>
                        <p class="error"><?= $error['postal_code'] ?></p>
                    <?php endif ?>
                    <?php if (isset($error['prefecture'])) :?>
                        <p class="error"><?= $error['prefecture'] ?></p>
                    <?php endif ?>
                    <?php if (isset($error['address'])) :?>
                        <p class="error"><?= $error['address'] ?></p>
                    <?php endif ?>
                    <div class="item">
                        <h3 class="item-title">メールアドレス</h3>
                        <div class="form-wrapper"><input type="text" name="mail_address" value=<?= h($mailAddress) ?>></div>
                    </div>
                    <?php if (isset($error['mail_address'])) :?>
                        <p class="error"><?= $error['mail_address'] ?></p>
                    <?php endif ?>
                </div>
                <input class="btn btn-form" type="submit" value="更新">
            </form>
        </div>
    </main>
</body>
</html>