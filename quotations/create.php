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
    $companyId = $_GET['id'];
} else {
    header('Location: ../companies/index.php');
    exit();
}

$statement = $db->prepare('SELECT name, manager_name, prefix FROM companies WHERE id=?');
$statement->execute(array($companyId));
$companyData = $statement->fetch();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>見積作成</title>
</head>
<body>
    <main>
        <div class="container">
            <div class="heding">
                <h1>見積作成</h1>
                <a href="index.php?id=<?= $companyId?>">戻る</a>
            </div>
            <form action="">
                <input type="hidden" name="id" value=<?= $companyId?>>
                <input type="hidden" name="prefix" value=<?= $companyData['prefix']?>>
                <table class="form-items">
                    <tr>
                        <th>見積名</th>
                        <td><input type="text" name="title"></td>
                    </tr>
                    <tr>
                        <th>会社名</th>
                        <td><?= $companyData['name']?></td>
                    </tr>
                    <tr>
                        <th>金額</th>
                        <td><input type="text" name="total"></td>
                    </tr>
                    <tr>
                        <th>見積有効期限</th>
                        <td><input type="date" name="validity_period"></td>
                    </tr>
                    <tr>
                        <th>納期</th>
                        <td><input type="date" name="due_date"></td>
                    </tr>
                    <tr>
                        <th>状態</th>
                        <td>
                            <select name="status" id="">
                                <option value="1">下書き</option>
                                <option value="2">発行済み</option>
                                <option value="9">破棄</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <input type="submit" value="見積作成">
            </form>
        </div>
    </main>
</body>
</html>