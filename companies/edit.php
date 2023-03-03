<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');
require_once('../utils/prefectures.php');

// クエリパラメータからidを受け取る
if (!is_exact_id($_GET['id'])) {
    header('Location: index.php');
    exit();
}
$id = $_GET['id'];
$post = $_POST;
if (empty($post)) {
    // idの会社データを取得（会社名、担当者名、電話番号、郵便番号、都道府県コード、住所、メールアドレス、プレフィックス）
    $detailStmt = $db->prepare('SELECT name, manager_name, phone_number, postal_code, prefecture_code, address, mail_address, prefix FROM companies WHERE id=:id');
    $detailStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $detailStmt->execute();
    // 受け取ったidの会社データが存在するかチェック
    $details = $detailStmt->fetch(PDO::FETCH_ASSOC);
    if (!$details) {
        header('Location: index.php');
        exit();
    }

    // 会社データをフォームの初期値に設定する
    $name = $details['name'];
    $managerName = $details['manager_name'];
    $phoneNumber = $details['phone_number'];
    $postalCode = $details['postal_code'];
    $prefectureCode = $details['prefecture_code'];
    $address = $details['address'];
    $mailAddress = $details['mail_address'];
} else {
    // 入力された値の全角スペース、全角数字を半角に変換
    $items = convert_half_width($post);

    // 入力された値をフォームの初期値に設定する
    $name = $items['name'];
    $managerName = $items['manager_name'];
    $phoneNumber = $items['phone_number'];
    $postalCode = $items['postal_code'];
    $prefectureCode = $items['prefecture_code'];
    $address = $items['address'];
    $mailAddress = $items['mail_address'];

    // 会社テーブルのバリデーションチェック
    $error = check_company($items);
    // バリデーションで問題がなければidの会社データをupdate
    if (empty($error)) {
        $updateStmt = $db->prepare('UPDATE companies SET
        name=:name,
        manager_name=:manager_name,
        phone_number=:phone_number,
        postal_code=:postal_code,
        prefecture_code=:prefecture_code,
        address=:address,
        mail_address=:mail_address,
        modified=NOW() WHERE id=:id');
        $updateStmt->bindParam(':name', $name);
        $updateStmt->bindParam(':manager_name', $managerName);
        $updateStmt->bindParam(':phone_number', $phoneNumber);
        $updateStmt->bindParam(':postal_code', $postalCode);
        $updateStmt->bindParam(':prefecture_code', $prefectureCode, PDO::PARAM_INT);
        $updateStmt->bindParam(':address', $address);
        $updateStmt->bindParam(':mail_address', $mailAddress);
        $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
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
                        <h3 class="item-title">ID</h3>
                        <div class="form-wrapper"><p><?= h($id)?></p></div>
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
                                <input type="text" name="postal_code" class="short-input" maxlength="7" id="postal_code" value=<?= h($postalCode) ?>>
                                <span>(ハイフンなし)</span>
                            </div>
                            <div class="address-item">
                                <h4>都道府県</h4>
                                <select name="prefecture_code" id="prefecture_code">
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
    <script type="text/javascript">
        getAddress();
    </script>
</body>
</html>