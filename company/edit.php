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
}

$statement = $db->prepare('SELECT id, name, manager_name, phone_number, postal_code, prefecture_code, address, mail_address, prefix FROM companies WHERE id=?');
$statement->bindParam(1, $id, PDO::PARAM_INT);
$statement->execute();
$details = $statement->fetch();
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
                <h1>会社編集</h1>
                <a href="index.php">戻る</a>
            </div>
            <form action="">
                <div class="form-items">
                    <div class="item">
                        <p>ID</p>
                        <p><?= h($details['id'])?></p>
                    </div>
                    <div class="item">
                        </div>
                        <label for="name_company" >会社名</label>
                        <input type="text" id="name_company" value=<?= h($details['name']) ?>>
                    <div class="item">
                        <label for="manager_name" class="item-titles">担当者名</label>
                        <input type="text" id="manager_name" name="manager_name" value=<?= h($details['manager_name']) ?>>
                    </div>
                    <div class="item">
                        <label for="phone_number" class="item-titles">電話番号</label>
                        <input type="text" id="phone_number" name="phone_number" value=<?= h($details['phone_number']) ?>>
                    </div>
                    <div class="item">
                        <label for="postal_code" class="item-titles">住所</label>
                        <div class="address-details">
                            <div class="address-detail">
                                <label for="postal_code">郵便番号</label>
                                <input type="text" id="postal_code" name="postal_code" value=<?= h($details['postal_code']) ?>>
                            </div>
                            <div class="address-detail">
                                <label for="prefecture_code">都道府県</label>
                                <select name="prefecture_code" id="prefecture_code">
                                    <?php for ($i = 1; $i <= 47; $i++) :?>
                                        <?php if ($details['prefecture_code'] == $i) :?>
                                            <option value=<?= $i ?> selected><?= $prefectures[$i] ?></option>
                                        <?php else :?>
                                            <option value=<?= $i ?>><?= $prefectures[$i] ?></option>
                                        <?php endif ?>
                                    <?php endfor ?>
                                </select>
                            </div>
                            <div class="address-detail">
                                <label for="address">市区町村</label>
                                <input type="text" id="address" name="address" value=<?= h($details['address']) ?>>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <label for="mail_address" class="item-titles">メールアドレス</label>
                        <input type="text" id="mail_address" name="mail_address" value=<?= h($details['mail_address']) ?>>
                    </div>
                    <div class="item">
                        <label for="prefix" class="item-titles">プレフィックス</label>
                        <p><?= h($details['prefix'])?></p>
                    </div>
                </div>
                <input type="submit" value="更新">
            </form>
        </div>
    </main>
</body>
</html>