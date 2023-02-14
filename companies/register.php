<?php
require_once('../utils/prefectures.php');
require_once('../utils/functions.php');
require_once('../utils/columns.php');
session_start();

// 各入力データを初期化（フォームの初期値））
$name = '';
$managerName = '';
$phoneNumber = '';
$postalCode = '';
$prefectureCode = '';
$address = '';
$mailAddress = '';
$prefix = '';

$items = [];
if (!empty($_POST)) {
    // 入力された値の全角スペース、全角数字を半角に変換
    foreach ($_POST as $key => $value) {
        $items[$key] = preg_replace("/(^\s+)|(\s+$)/u", "", $value);
        $items[$key] = mb_convert_kana($items[$key], "n");
    }

    if (mb_strlen($items['name']) > 64) {
        $error['name'] = '会社名は64文字以内で入力してください';
    }
    if (mb_strlen($items['manager_name']) > 32) {
        $error['manager_name'] = '担当者名は32文字以内で入力してください';
    }

    if (!preg_match('/^\d{1,11}$/', $items['phone_number'])) {
        $error['phone_number'] = '電話番号は11桁以下の整数で入力してください';
    }

    if (!preg_match('/^\d{7}$/', $items['postal_code'])) {
        $error['postal_code'] = '郵便番号は7桁の整数で入力してください';
    }

    if ($items['prefecture_code'] < 1 && $items['prefecture_code'] > 47) {
        $error['prefecture_code'] = 'もう一度都道府県を選択してください';
    }

    if (mb_strlen($items['address']) > 100) {
        $error['address'] = '市区町村は100字以内で入力してください';
    }

    if (mb_strlen($items['mail_address']) > 100) {
        $error['mail_address'] = 'メールアドレスは100字以内で入力して下さい';
    } elseif (!preg_match('/^[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*@[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*$/', $items['mail_address'])) {
        $error['mail_address'] = '正しいメールアドレスを入力してください';
    }

    if (!preg_match('/^[a-zA-Z0-9]{1,8}$/', $items['prefix'])) {
        $error['prefix'] = 'プレフィックスは8字以内の英数字で入力してください';
    }
    // スペース以外の値が入っているか＝入力されているか
    foreach ($items as $key => $value) {
        if (preg_match('/^[\s]*$/', $value)) {
            $error[$key] = COLUMNS[$key] . 'を入力してください';
        }
    }
    // バリデーションチェックで問題がなかった場合セッションに値を代入し確認ページへ遷移
    if (!isset($error)) {
        $_SESSION['register'] = $items;
        header('Location: check.php');
        exit();
    }
}

// 確認ページからの書き戻しによる遷移かどうかの確認
// trueだった場合セッションの値をitemsに代入
if (isset($_GET['action']) && $_GET['action'] == 'rewrite') {
    $items = $_SESSION['register'];
}

// inputに表示する値の代入
if (!empty($items)) {
    $name = $items['name'];
    $managerName = $items['manager_name'];
    $phoneNumber = $items['phone_number'];
    $postalCode = $items['postal_code'];
    $prefectureCode = intval($items['prefecture_code']);
    $address = $items['address'];
    $mailAddress = $items['mail_address'];
    $prefix = $items['prefix'];
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
                        <h3 class="item-title">電話番号</h3>
                        <div class="form-wrapper"><input type="text" name="phone_number" class="tel-input" maxlength="11" value=<?= h($phoneNumber) ?>></div>
                    </div>
                    <?php if (isset($error['phone_number'])) :?>
                        <p class="error"><?= $error['phone_number'] ?></p>
                    <?php endif ?>
                    <div class="item address-items">
                        <h3 class="item-title">住所</h3>
                        <div class="address-item-wrapper">
                            <div class="address-item">
                                <h4>郵便番号</h4>
                                <input type="text" name="postal_code" class="short-input" maxlength="7" value=<?= h($postalCode) ?>>
                                <span>(ハイフンなし)</span>
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