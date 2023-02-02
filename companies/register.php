<?php
require_once('../utils/prefectures.php');
require_once('../utils/functions.php');
session_start();

if (!empty($_POST)) {
    if ($_POST['name'] == '' || preg_match('/^[\s\n\t]+$/', $_POST['name'])) {
        $error['name'] = '会社名を入力してください';
    } elseif (mb_strlen($_POST['name']) > 64) {
        $error['name'] = '会社名は64文字以内で入力してください';
    }

    if ($_POST['manager_name'] == '' || preg_match('/^[\s\n\t]+$/', $_POST['manager_name'])) {
        $error['manager_name'] = '担当者名を入力してください';
    } elseif (mb_strlen($_POST['manager_name']) > 32) {
        $error['manager_name'] = '担当者名は32文字以内で入力してください';
    }

    if ($_POST['phone_number'] == '' || preg_match('/^[\s\n\t]+$/', $_POST['phone_number'])) {
        $error['phone_number'] = '電話番号を入力してください';
    } elseif (mb_strlen($_POST['phone_number']) > 11 || !preg_match('/^\d+$/', $_POST['phone_number'])) {
        $error['phone_number'] = '電話番号はハイフンなしの11桁以下の半角整数で入力してください';
    }

    if ($_POST['postal_code'] == '' || preg_match('/^[\s\n\t]+$/', $_POST['postal_code'])) {
        $error['postal_code'] = '郵便番号を入力してください';
    } elseif (mb_strlen($_POST['postal_code']) != 7 || !preg_match('/^\d+$/', $_POST['postal_code'])) {
        $error['postal_code'] = '郵便番号はハイフンなしの7桁の半角整数で入力してください';
    }

    if ($_POST['prefecture_code'] == '' || preg_match('/^[\s\n\t]+$/', $_POST['prefecture_code'])) {
        $error['prefecture_code'] = 'もう一度都道府県を選択してください';
    } elseif (mb_strlen($_POST['prefecture_code']) < 1 && mb_strlen($_POST['prefecture_code'] > 47)) {
        $error['prefecture_code'] = 'もう一度都道府県を選択してください';
    }

    if ($_POST['address'] == '' || preg_match('/^[\s\n\t]+$/', $_POST['address'])) {
        $error['address'] = '市区町村を入力してください';
    } elseif (mb_strlen($_POST['address']) > 100) {
        $error['address'] = '市区町村は100字以内で入力してください';
    }

    if ($_POST['mail_address'] == '' || preg_match('/^[\s\n\t]+$/', $_POST['mail_address'])) {
        $error['mail_address'] = 'メールアドレスを入力してください';
    } elseif (mb_strlen($_POST['mail_address']) > 100) {
        $error['mail_address'] = 'メールアドレスは100字以内で入力して下さい';
    } elseif (!preg_match('/^[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*@[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*$/', $_POST['mail_address'])) {
        $error['mail_address'] = '正しいメールアドレスを入力してください';
    }

    if ($_POST['prefix'] == '' || preg_match('/^[\s\n\t]+$/', $_POST['prefix'])) {
        $error['prefix'] = 'プレフィックスを入力してください';
    } elseif (mb_strlen($_POST['prefix']) > 8 || !preg_match('/^[a-zA-Z0-9]+$/', $_POST['prefix'])) {
        $error['prefix'] = 'プレフィックスは8字以内の半角英数字で入力してください';
    }

    if (!isset($error)) {
        $_SESSION['register'] = $_POST;
        header('Location: check.php');
        exit();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'rewrite') {
    $_POST = $_SESSION['register'];
}


$name = '';
$manager_name = '';
$phone_number = '';
$postal_code = '';
$prefecture_code = '';
$address = '';
$mail_address = '';
$prefix = '';

if (!empty($_POST)) {
    $name = $_POST['name'];
    $manager_name = $_POST['manager_name'];
    $phone_number = $_POST['phone_number'];
    $postal_code = $_POST['postal_code'];
    $prefecture_code = intval($_POST['prefecture_code']);
    $address = $_POST['address'];
    $mail_address = $_POST['mail_address'];
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
                <a href="index.php">戻る</a>
            </div>
            <form action="register.php" method="POST" class="create-form">
                <div class="form-items">
                    <div class="item">
                        <label for="name" class="item-titles">会社名</label>
                        <input type="text" id="name" name="name" value=<?= h($name) ?>>
                    </div>
                    <?php if (isset($error['name'])) :?>
                        <p class="error"><?= $error['name'] ?></p>
                    <?php endif ?>
                    <div class="item">
                        <label for="manager_name" class="item-titles">担当者名</label>
                        <input type="text" id="manager_name" name="manager_name" value=<?= h($manager_name) ?>>
                    </div>
                    <?php if (isset($error['manager_name'])) :?>
                        <p class="error"><?= $error['manager_name'] ?></p>
                    <?php endif ?>
                    <div class="item">
                        <label for="phone_number" class="item-titles">電話番号</label>
                        <input type="text" id="phone_number" name="phone_number" value=<?= h($phone_number) ?>>
                    </div>
                    <?php if (isset($error['phone_number'])) :?>
                        <p class="error"><?= $error['phone_number'] ?></p>
                    <?php endif ?>
                    <div class="item">
                        <label for="postal_code" class="item-titles">住所</label>
                        <div class="address-details">
                            <div class="address-detail">
                                <label for="postal_code">郵便番号</label>
                                <input type="text" id="postal_code" name="postal_code" value=<?= h($postal_code) ?>>
                            </div>
                            <?php if (isset($error['postal_code'])) :?>
                                <p class="error"><?= $error['postal_code'] ?></p>
                            <?php endif ?>
                            <div class="address-detail">
                                <label for="prefecture_code">都道府県</label>
                                <select name="prefecture_code" id="prefecture_code">
                                    <?php for ($i = 1; $i <= 47; $i++) :?>
                                        <?php if ($prefecture_code == $i) :?>
                                            <option value=<?= $i ?> selected><?= $prefectures[$i] ?></option>
                                        <?php else :?>
                                            <option value=<?= $i ?>><?= $prefectures[$i] ?></option>
                                        <?php endif ?>
                                    <?php endfor ?>
                                </select>
                            </div>
                            <?php if (isset($error['prefecture'])) :?>
                                <p class="error"><?= $error['prefecture'] ?></p>
                            <?php endif ?>
                            <div class="address-detail">
                                <label for="address">市区町村</label>
                                <input type="text" id="address" name="address" value=<?= h($address) ?>>
                            </div>
                            <?php if (isset($error['address'])) :?>
                                <p class="error"><?= $error['address'] ?></p>
                            <?php endif ?>
                        </div>
                    </div>
                    <div class="item">
                        <label for="mail_address" class="item-titles">メールアドレス</label>
                        <input type="text" id="mail_address" name="mail_address" value=<?= h($mail_address) ?>>
                    </div>
                    <?php if (isset($error['mail_address'])) :?>
                        <p class="error"><?= $error['mail_address'] ?></p>
                    <?php endif ?>
                    <div class="item">
                        <label for="prefix" class="item-titles">プレフィックス</label>
                        <input type="text" id="prefix" name="prefix" value=<?= h($prefix) ?>>
                    </div>
                    <?php if (isset($error['prefix'])) :?>
                        <p class="error"><?= $error['prefix'] ?></p>
                    <?php endif ?>
                </div>
                <input type="submit" value="作成">
            </form>
        </div>
    </main>
</body>
</html>