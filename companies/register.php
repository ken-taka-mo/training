<?php
require_once('../utils/prefectures.php');
require_once('../utils/functions.php');
require_once('../utils/columns.php');
session_start();

// 各入力データを初期化（フォームの初期値)
$name = '';
$managerName = '';
$phoneNumber = '';
$postalCode = '';
$prefectureCode = '';
$address = '';
$mailAddress = '';
$prefix = '';

$post = $_POST;
if (!empty($post)) {
    // 入力された値の全角スペース、全角数字を半角に変換
    $items = convert_half_width($post);
    $error = check_company($items);
    // バリデーションチェックで問題がなかった場合セッションに値を代入し確認ページへ遷移
    if (empty($error)) {
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
                                <input type="text" name="postal_code" id="postal_code" class="short-input" maxlength="7" value=<?= h($postalCode) ?>>
                                <span>(半角数字のみ・ハイフンなし)</span>
                            </div>
                            <div class="address-item">
                                <h4>都道府県</h4>
                                <select name="prefecture_code" id="prefecture_code">
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
                                <input type="text" name="address" id="address" value=<?= h($address) ?>>
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
                        <div class="form-wrapper"><input type="text" name="prefix" class="short-input" maxlength="8" value=<?= h($prefix) ?>><span class="prefix-span">(半角英数字8文字以下)</soan></div>
                    </div>
                    <?php if (isset($error['prefix'])) :?>
                        <p class="error"><?= $error['prefix'] ?></p>
                    <?php endif ?>
                </div>
                <input class="btn btn-form" type="submit" value="新規登録">
            </form>
        </div>
    </main>
    <script type="text/javascript" src="../js/functions/getAddress.js"></script>
</body>
</html>