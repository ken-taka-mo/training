<?php
require_once('../utils/prefectures.php');
// バリデーション

// id 自動割り振り
// 会社名 64文字
// 担当者名　32文字
// 電話番号　半角整数、ハイフンなし　11字
// 住所 郵便番号　半角整数、ハイフンなし 7字
    //  都道府県　半角整数、ハイフンなし　1～47までの整数
    //  市町村_地名_番地_建物名 100字
// メールアドレス　100字 アドレス条件あり
// プレフィックス　半角英数字のみ　16字
// 作成日時　自動
// 更新日時　自動

$name = '';
$manager_name = '';
$phone_number = '';
$postal_code = '';
$prefecture = '';
$address = '';
$mail_address = '';
$prefix = '';

function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES);
}

if (!empty($_POST)) {
    $name = $_POST['name'];
    $manager_name = $_POST['manager_name'];
    $phone_number = $_POST['phone_number'];
    $postal_code = $_POST['postal_code'];
    $prefecture_code = intval($_POST['prefecture_code']);
    $address = $_POST['address'];
    $mail_address = $_POST['mail_address'];
    $prefix = $_POST['prefix'];

    if ($name == '') {
        $error['name'] = '会社名を入力してください';
    } elseif (mb_strlen($name) > 64) {
        $error['name'] = '会社名は64文字以内で入力してください';
    }

    if ($manager_name == '') {
        $error['manager_name'] = '担当者名を入力してください';
    } elseif (mb_strlen($manager_name) > 32) {
        $error['manager_name'] = '担当者名は32文字以内で入力してください';
    }

    if ($phone_number == '') {
        $error['phone_number'] = '電話番号を入力してください';
    } elseif (mb_strlen($phone_number) > 11 || !preg_match('/^\d+$/', $phone_number)) {
        $error['phone_number'] = '電話番号はハイフンなしの11桁以下の半角整数で入力してください';
    }

    if ($postal_code == '') {
        $error['postal_code'] = '郵便番号を入力してください';
    } elseif (mb_strlen($postal_code) != 7 || !preg_match('/^\d+$/', $postal_code)) {
        $error['postal_code'] = '郵便番号はハイフンなしの7桁の半角整数で入力してください';
    }

    if ($prefecture_code == '') {
        $error['prefecture_code'] = 'もう一度都道府県を選択してください';
    } elseif (mb_strlen($prefecture_code) < 1 && mb_strlen($prefecture_code > 47)) {
        $error['prefecture_code'] = 'もう一度都道府県を選択してください';
    } elseif (!is_numeric($prefecture_code)) {
        $error['prefecture_code'] = 'もう一度都道府県を選択してください';
    }

    if ($address == '') {
        $error['address'] = '市区町村を入力してください';
    } elseif (mb_strlen($address) > 100) {
        $error['address'] = '市区町村は100字以内で入力してください';
    }

    if ($mail_address == '') {
        $error['mail_address'] = 'メールアドレスを入力してください';
    } elseif (mb_strlen($mail_address) > 100) {
        $error['mail_address'] = 'メールアドレスは100字以内で入力して下さい';
    } elseif (!preg_match('/^[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*@[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*$/', $mail_address)) {
        $error['mail_address'] = '正しいメールアドレスを入力してください';
    }

    if ($prefix == '') {
        $error['prefix'] = 'プレフィックスを入力してください';
    } elseif (mb_strlen($prefix) > 8 || !preg_match('/^[a-zA-Z0-9]+$/', $prefix)) {
        $error['prefix'] = 'プレフィックスは8字以内の半角英数字で入力してください';
    }

    if (empty($error)) {
        header('Location: check.php');
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
    <title>会社登録</title>
</head>
<body>
    <main>
        <div class="container">
            <div class="heding">
                <h1></h1>
                <a href="../index.php">戻る</a>
            </div>
            <form action="" method="POST">
                <!-- 会社番号(ID)は自動割り振り -->
                <!-- 会社名、担当者名、電話番号、住所（郵便番号・都道府県・市町村＿地名＿番地＿建物名）、メールアドレス、プレフィックスを入力 -->
                <div class="form-items">
                    <label for="name">会社名</label>
                    <input type="text" id="name" name="name" value=<?= h($name) ?>>
                    <?php if (isset($error['name'])) :?>
                        <p class="error"><?= $error['name'] ?></p>
                    <?php endif ?>
                    <label for="manager_name">担当者名</label>
                    <input type="text" id="manager_name" name="manager_name" value=<?= h($manager_name) ?>>
                    <?php if (isset($error['manager_name'])) :?>
                        <p class="error"><?= $error['manager_name'] ?></p>
                    <?php endif ?>
                    <label for="phone_number">電話番号</label>
                    <input type="text" id="phone_number" name="phone_number" value=<?= h($phone_number) ?>>
                    <?php if (isset($error['phone_number'])) :?>
                        <p class="error"><?= $error['phone_number'] ?></p>
                    <?php endif ?>
                    <div>
                        <p>住所</p>
                        <div>
                            <label for="postal_code">郵便番号</label>
                            <input type="text" id="postal_code" name="postal_code" value=<?= h($postal_code) ?>>
                            <?php if (isset($error['postal_code'])) :?>
                                <p class="error"><?= $error['postal_code'] ?></p>
                            <?php endif ?>
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
                            <?php if (isset($error['prefecture'])) :?>
                                <p class="error"><?= $error['prefecture'] ?></p>
                            <?php endif ?>
                            <label for="address">市区町村</label>
                            <input type="text" id="address" name="address" value=<?= h($address) ?>>
                            <?php if (isset($error['address'])) :?>
                                <p class="error"><?= $error['address'] ?></p>
                            <?php endif ?>
                        </div>
                    </div>
                    <label for="mail_address">メールアドレス</label>
                    <input type="text" id="mail_address" name="mail_address" value=<?= h($mail_address) ?>>
                    <?php if (isset($error['mail_address'])) :?>
                        <p class="error"><?= $error['mail_address'] ?></p>
                    <?php endif ?>
                    <label for="prefix">プレフィックス</label>
                    <input type="text" id="prefix" name="prefix" value=<?= h($prefix) ?>>
                    <?php if (isset($error['prefix'])) :?>
                        <p class="error"><?= $error['prefix'] ?></p>
                    <?php endif ?>
                </div>
                <input type="submit" value="作成"> <!-- postでバリデーション -->
            </form>
        </div>
    </main>
</body>
</html>