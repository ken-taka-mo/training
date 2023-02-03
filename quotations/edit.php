<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');

if (!isset($_GET['no']) || !preg_match('/^[a-zA-Z0-9]{1,8}?(-q-)[0-9]{8}$/', $_GET['no'])) {
    header('Location: ../companies');
    exit();
}

$no = $_GET['no'];

$statement = $db->prepare('SELECT title, company_id, total, validity_period, due_date, status FROM quotations WHERE no=?');
$statement->execute(array($no));
$quotationData = $statement->fetch();
$companyNameStatement = $db->prepare('SELECT name FROM companies WHERE id = ?');
$companyNameStatement->execute(array($quotationData['company_id']));
$companyName = $companyNameStatement->fetch();

$title = $quotationData['title'];
$total = $quotationData['total'];
$validity_period = $quotationData['validity_period'];
$due_date = $quotationData['due_date'];
$status = $quotationData['status'];

if (!empty($_POST)) {
    $title = $_POST['title'];
    $total = $_POST['total'];
    $validity_period = $_POST['validity_period'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

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
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>見積編集</title>
</head>
<body>
    <main>
        <div class="container">
            <div class="heding">
                <h1>見積編集</h1>
                <a href="index.php?id=<?= $quotationData['company_id']?>">戻る</a>
            </div>
            <form action="edit.php?no=<?= $no?>" method="POST">
                <table>
                    <tr>
                        <th>見積名</th>
                        <td><input type="text" name="title" value=<?= h($title) ?>></td>
                    </tr>
                    <?php if (isset($error['title'])) :?>
                        <p><?= $error['title'] ?></p>
                    <?php endif?>
                    <tr>
                        <th>会社名</th>
                        <td><?= $companyName['name']?></td>
                    </tr>
                    <tr>
                        <th>金額</th>
                        <td><input type="text" name="total" value=<?= h($total) ?>></td>
                    </tr>
                    <?php if (isset($error['total'])) :?>
                        <p><?= $error['total'] ?></p>
                    <?php endif?>
                    <tr>
                        <th>見積有効期限</th>
                        <td><input type="date" name="validity_period" value=<?= h($validity_period) ?>></td>
                    </tr>
                    <?php if (isset($error['validity_period'])) :?>
                        <p><?= $error['validity_period'] ?></p>
                    <?php endif?>
                    <tr>
                        <th>納期</th>
                        <td><input type="date" name="due_date" value=<?= h($due_date) ?>></td>
                    </tr>
                    <?php if (isset($error['due_date'])) :?>
                        <p><?= $error['due_date'] ?></p>
                    <?php endif?>
                    <tr>
                        <th>状態</th>
                        <td>
                            <select name="status" id="">
                                <?php if ($status == 1) :?>
                                    <option value="1" selected>下書き</option>
                                    <option value="2">発行済み</option>
                                    <option value="9">破棄</option>
                                <?php elseif ($status == 2) :?>
                                    <option value="1">下書き</option>
                                    <option value="2" selected>発行済み</option>
                                    <option value="9">破棄</option>
                                <?php else :?>
                                    <option value="1">下書き</option>
                                    <option value="2">発行済み</option>
                                    <option value="9" selected>破棄</option>
                                <?php endif ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <input type="submit" value="更新">
            </form>
        </div>
    </main>
</body>
</html>