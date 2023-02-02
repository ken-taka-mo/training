<?php
require_once('../dbconnect.php');
session_start();
    // id
    // 会社id　companiesTABLEのid
    // 見積番号 プレフィックス-q-8桁の数字
    // 見積名
    // 金額
    // 見積書有効期限
    // 納期
    // 状態
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

if (!empty($_POST)) {
    if (preg_match('/^[\s\n\t]*$/', $_POST['title'])) {
        $error['title'] = '見積名を入力してください';
    } elseif (mb_strlen($_POST['title']) > 64) {
        $error['title'] = '見積名は64以下で入力してください';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['total'])) {
        $error['total'] = '金額を入力してください';
    } elseif (!preg_match('/^[1-9]+[0-9]*/', $_POST['total']) || strlen($_POST['total']) > 10) {
        $error['total'] = '金額は10桁以下の半角数字のみで入力してください';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['validity_period'])) {
        $error['validity_period'] = '見積有効期限を入力してください';
    } elseif (mb_strlen($_POST['validity_period']) > 32) {
        $error['validity_period'] = '見積有効期限を入力しなおしてください';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['due_date'])) {
        $error['due_date'] = '納期を入力してください';
    } elseif ($_POST['due_date'] <= date("Y-m-d")) {
        $error['due_date'] = '本日以降の日付を入力してください';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['status'])) {
        $error['status'] = '状態を入力してください';
    } elseif (!preg_match('/^[129]$/', $_POST['status'])) {
        $error['status'] = '状態をもう一度選択してください';
    }

    if (!isset($error)) {
        $_SESSION['new_quotation'] = $_POST;
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
    <title>見積作成</title>
</head>
<body>
    <main>
        <div class="container">
            <div class="heding">
                <h1>見積作成</h1>
                <a href="index.php?id=<?= $companyId?>">戻る</a>
            </div>
            <form action="create.php?id=<?= $companyId?>" method="POST">
                <input type="hidden" name="company_id" value=<?= $companyId?>>
                <input type="hidden" name="prefix" value=<?= $companyData['prefix']?>>
                <input type="hidden" name="name" value=<?= $companyData['name']?>>
                <table class="form-items">
                    <tr>
                        <th>見積名</th>
                        <td><input type="text" name="title"></td>
                    </tr>
                    <?php if (isset($error['title'])) :?>
                        <p><?= $error['title'] ?></p>
                    <?php endif?>
                    <tr>
                        <th>会社名</th>
                        <td><?= $companyData['name']?></td>
                    </tr>
                    <tr>
                        <th>金額</th>
                        <td><input type="text" name="total"></td>
                    </tr>
                    <?php if (isset($error['total'])) :?>
                        <p><?= $error['total'] ?></p>
                    <?php endif?>
                    <tr>
                        <th>見積有効期限</th>
                        <td><input type="date" name="validity_period"></td>
                    </tr>
                    <?php if (isset($error['validity_period'])) :?>
                        <p><?= $error['validity_period'] ?></p>
                    <?php endif?>
                    <tr>
                        <th>納期</th>
                        <td><input type="date" name="due_date"></td>
                    </tr>
                    <?php if (isset($error['due_date'])) :?>
                        <p><?= $error['due_date'] ?></p>
                    <?php endif?>
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
                    <?php if (isset($error['status'])) :?>
                        <p><?= $error['status'] ?></p>
                    <?php endif?>
                </table>
                <input type="submit" value="見積作成">
            </form>
        </div>
    </main>
</body>
</html>