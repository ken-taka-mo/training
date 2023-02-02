<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');
require_once('../utils/prefectures.php');


if (empty($_GET['id'])) {
    header('Location: index.php');
    exit();
} elseif (!preg_match('/^[0-9]+$/', $_GET['id'])) {
    header('Location: index.php');
    exit();
} else {
    $id = $_GET['id'];
    $statement = $db->prepare('SELECT id, name, manager_name, phone_number, postal_code, prefecture_code, address, mail_address, prefix FROM companies WHERE id=?');
    $statement->bindParam(1, $id, PDO::PARAM_INT);
    $statement->execute();
    $details = $statement->fetch();
    if (empty($_POST)) {
        $name = $details['name'];
        $manager_name = $details['manager_name'];
        $phone_number = $details['phone_number'];
        $postal_code = $details['postal_code'];
        $prefecture_code = $details['prefecture_code'];
        $address = $details['address'];
        $mail_address = $details['mail_address'];
    }
}



if (!empty($_POST)) {
    $name = $_POST['name'];
    $manager_name = $_POST['manager_name'];
    $phone_number = $_POST['phone_number'];
    $postal_code = $_POST['postal_code'];
    $prefecture_code = $_POST['prefecture_code'];
    $address = $_POST['address'];
    $mail_address = $_POST['mail_address'];

    if ($name == '' || preg_match('/^[\s\n\t]+$/', $name)) {
        $error['name'] = '会社名を入力してください';
    } elseif (mb_strlen($name) > 64) {
        $error['name'] = '会社名は64文字以内で入力してください';
    }

    if ($manager_name == '' || preg_match('/^[\s\n\t]+$/', $manager_name)) {
        $error['manager_name'] = '担当者名を入力してください';
    } elseif (mb_strlen($manager_name) > 32) {
        $error['manager_name'] = '担当者名は32文字以内で入力してください';
    }

    if ($phone_number == '' || preg_match('/^[\s\n\t]+$/', $phone_number)) {
        $error['phone_number'] = '電話番号を入力してください';
    } elseif (mb_strlen($phone_number) > 11 || !preg_match('/^\d+$/', $phone_number)) {
        $error['phone_number'] = '電話番号はハイフンなしの11桁以下の半角整数で入力してください';
    }

    if ($postal_code == '' || preg_match('/^[\s\n\t]+$/', $postal_code)) {
        $error['postal_code'] = '郵便番号を入力してください';
    } elseif (mb_strlen($postal_code) != 7 || !preg_match('/^\d+$/', $postal_code)) {
        $error['postal_code'] = '郵便番号はハイフンなしの7桁の半角整数で入力してください';
    }

    if ($prefecture_code == '' || preg_match('/^[\s\n\t]+$/', $prefecture_code)) {
        $error['prefecture_code'] = 'もう一度都道府県を選択してください';
    } elseif (mb_strlen($prefecture_code) < 1 && mb_strlen($prefecture_code > 47)) {
        $error['prefecture_code'] = 'もう一度都道府県を選択してください';
    }

    if ($address == '' || preg_match('/^[\s\n\t]+$/', $address)) {
        $error['address'] = '市区町村を入力してください';
    } elseif (mb_strlen($address) > 100) {
        $error['address'] = '市区町村は100字以内で入力してください';
    }

    if ($mail_address == '' || preg_match('/^[\s\n\t]+$/', $mail_address)) {
        $error['mail_address'] = 'メールアドレスを入力してください';
    } elseif (mb_strlen($mail_address) > 100) {
        $error['mail_address'] = 'メールアドレスは100字以内で入力して下さい';
    } elseif (!preg_match('/^[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*@[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*$/', $mail_address)) {
        $error['mail_address'] = '正しいメールアドレスを入力してください';
    }

    if (!isset($error)) {
        $statement = $db->prepare('UPDATE companies SET
        name=?,
        manager_name=?,
        phone_number=?,
        postal_code=?,
        prefecture_code=?,
        address=?,
        mail_address=?,
        modified=NOW() WHERE id=?');
        $statement->bindParam(1, $name);
        $statement->bindParam(2, $manager_name);
        $statement->bindParam(3, $phone_number);
        $statement->bindParam(4, $postal_code);
        $statement->bindParam(5, $prefecture_code, PDO::PARAM_INT);
        $statement->bindParam(6, $address);
        $statement->bindParam(7, $mail_address);
        $statement->bindParam(8, $id, PDO::PARAM_INT);
        $statement->execute();
        header('Location: index.php');
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
                <a href="index.php">戻る</a>
            </div>
            <form action="" method="POST" class="edit-form">
                <div class="form-items">
                    <div class="item">
                        <a class="item-titles">ID</a>
                        <p><?= h($details['id'])?></p>
                    </div>
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
                        <a class="item-titles">プレフィックス</a>
                        <p><?= h($details['prefix'])?></p>
                    </div>
                </div>
                <input type="submit" value="更新">
            </form>
        </div>
    </main>
</body>
</html>