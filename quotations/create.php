<?php
require_once('../dbconnect.php');
    // id
    // 会社id　companiesTABLEのid
    // 見積番号 プレフィックス-q-8桁の数字
    // 見積名
    // 金額
    // 見積書有効期限
    // 納期
    // 状態
    // 作成日時
    // 更新日時
if (empty($_GET['id'])) {
    header('Location: ../companies/index.php');
    exit();
}

if (!preg_match('/^[0-9]+$/', $_GET['id']) || preg_match('/^[0]*$/', $_GET['id'])) {
    header('Location: ../companies/index.php');
    exit();
}
$hasData = $db->prepare('SELECT COUNT(*) AS cnt FROM companies WHERE id=?');
$hasData->execute(array($_GET['id']));
$count = $hasData->fetch();
if ($count['cnt']) {
    $id = $_GET['id'];
} else {
    header('Location: ../companies/index.php');
    exit();
}

$statement = $db->prepare('SELECT name, manager_name, prefix FROM companies WHERE id=?');
$statement->execute(array($id));
$companyData = $statement->fetch();
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
                <h1>見積作成</h1>
                <a href="#">戻る</a>
            </div>
            <form action="">
                <div class="form-items">
                    <!-- 見積名 -->
                    <!-- 会社名 表示のみ　companiesTABLEの会社名-->
                    <!-- 金額 -->
                    <!-- 見積書有効期限 -->
                    <!-- 納期 -->
                    <!-- 状態 -->
                </div>
                <input type="submit" value="作成">
            </form>
        </div>
    </main>
</body>
</html>