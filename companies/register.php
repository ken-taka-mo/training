<?php
require_once('../utils/prefectures.php');
require_once('../utils/functions.php');
session_start();


if (!empty($_POST)) {
    if (preg_match('/^[\s]*$/', mb_convert_kana($_POST['name'], "s"))) {
        $error['name'] = '会社名を入力してください';
    } elseif (mb_strlen($_POST['name']) > 64) {
        $error['name'] = '会社名は64文字以内で入力してください';
    }

    if (preg_match('/^[\s]*$/', mb_convert_kana($_POST['manager_name'], "s"))) {
        $error['manager_name'] = '担当者名を入力してください';
    } elseif (mb_strlen($_POST['manager_name']) > 32) {
        $error['manager_name'] = '担当者名は32文字以内で入力してください';
    }

    if (preg_match('/^[\s]*$/', mb_convert_kana($_POST['phone_number'], "s"))) {
        $error['phone_number'] = '電話番号を入力してください';
    } elseif (mb_strlen($_POST['phone_number']) > 11 || !preg_match('/^\d+$/', $_POST['phone_number'])) {
        $error['phone_number'] = '電話番号はハイフンなしの11桁以下の半角整数で入力してください';
    }

    if (preg_match('/^[\s]*$/', mb_convert_kana($_POST['postal_code'], "s"))) {
        $error['postal_code'] = '郵便番号を入力してください';
    } elseif (!preg_match('/^\d{7}$/', $_POST['postal_code'])) {
        $error['postal_code'] = '郵便番号はハイフンなしの7桁の半角整数で入力してください';
    }

    if (preg_match('/^[\s]*$/', mb_convert_kana($_POST['prefecture_code'], "s"))) {
        $error['prefecture_code'] = '都道府県を選択してください';
    } elseif (mb_strlen($_POST['prefecture_code']) < 1 && mb_strlen($_POST['prefecture_code'] > 47)) {
        $error['prefecture_code'] = 'もう一度都道府県を選択してください';
    }

    if (preg_match('/^[\s]*$/', mb_convert_kana($_POST['address'], "s"))) {
        $error['address'] = '市区町村を入力してください';
    } elseif (mb_strlen($_POST['address']) > 100) {
        $error['address'] = '市区町村は100字以内で入力してください';
    }

    if (preg_match('/^[\s]*$/', mb_convert_kana($_POST['mail_address'], "s"))) {
        $error['mail_address'] = 'メールアドレスを入力してください';
    } elseif (mb_strlen($_POST['mail_address']) > 100) {
        $error['mail_address'] = 'メールアドレスは100字以内で入力して下さい';
    } elseif (!preg_match('/^[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*@[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*$/', $_POST['mail_address'])) {
        $error['mail_address'] = '正しいメールアドレスを入力してください';
    }

    if (preg_match('/^[\s]*$/', mb_convert_kana($_POST['prefix'], "s"))) {
        $error['prefix'] = 'プレフィックスを入力してください';
    } elseif (!preg_match('/^[a-zA-Z0-9]{1,8}$/', $_POST['prefix'])) {
        $error['prefix'] = 'プレフィックスは8字以内の半角英数字で入力してください';
    }

    if (!isset($error)) {
        $_POST['name'] = preg_replace("/(^\s+)|(\s+$)/u", "", $_POST['name']);
        $_POST['name'] = mb_convert_kana($_POST['name'], "n");
        $_POST['manager_name'] = preg_replace("/(^\s+)|(\s+$)/u", "", $_POST['manager_name']);
        $_POST['manager_name'] = mb_convert_kana($_POST['manager_name'], "n");
        $_POST['address'] = preg_replace("/(^\s+)|(\s+$)/u", "", $_POST['address']);
        $_POST['address'] = mb_convert_kana($_POST['address'], "n");
        $_SESSION['register'] = $_POST;
        header('Location: check.php');
        exit();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'rewrite') {
    $_POST = $_SESSION['register'];
}


$name = '';
$managerName = '';
$phoneNumber = '';
$postalCode = '';
$prefectureCode = '';
$address = '';
$mailAddress = '';
$prefix = '';

if (!empty($_POST)) {
    $name = $_POST['name'];
    $managerName = $_POST['manager_name'];
    $phoneNumber = $_POST['phone_number'];
    $postalCode = $_POST['postal_code'];
    $prefectureCode = intval($_POST['prefecture_code']);
    $address = $_POST['address'];
    $mailAddress = $_POST['mail_address'];
    $prefix = $_POST['prefix'];
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会社登録</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="create-page">
        <div class="container">
            <div class="heading">
                <h1>会社登録</h1>
                <a href="index.php" class="btn-back">戻る</a>
            </div>
            <form action="register.php" method="POST">
                <div class="form-items">
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
                        <h3 class="item-title">電話番号<span>(半角)</span></h3>
                        <div class="form-wrapper"><input type="text" name="phone_number" class="tel-input" maxlength="11" value=<?= h($phoneNumber) ?>></div>
                    </div>
                    <?php if (isset($error['phone_number'])) :?>
                        <p class="error"><?= $error['phone_number'] ?></p>
                    <?php endif ?>
                    <div class="item address-items">
                        <h3 class="item-title">住所</h3>
                        <div class="address-item-wrapper">
                            <div class="address-item">
                                <h4>郵便番号<span>(半角)</span></h4>
                                <input type="text" name="postal_code" class="short-input" maxlength="7" value=<?= h($postalCode) ?>>
                            </div>
                            <div class="address-item">
                                <h4>都道府県</h4>
                                <select name="prefecture_code">
                                    <option value="">選択してください</option>
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
                    <?php if (isset($error['prefecture_code'])) :?>
                        <p class="error"><?= $error['prefecture_code'] ?></p>
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
                    <div class="item">
                        <h3 class="item-title">プレフィックス</h3>
                        <div class="form-wrapper"><input type="text" name="prefix" class="short-input" maxlength="8" value=<?= h($prefix) ?>><span class="prefix-span">(半角8桁以下)</soan></div>
                    </div>
                    <?php if (isset($error['prefix'])) :?>
                        <p class="error"><?= $error['prefix'] ?></p>
                    <?php endif ?>
                </div>
                <input class="btn btn-form" type="submit" value="新規登録">
            </form>
        </div>
    </main>
</body>
</html>