<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');
session_start();
if (empty($_GET['id'])) {
    header('Location: ../companies/index.php');
    exit();
}

if (!preg_match('/^[0-9]+$/', $_GET['id']) || preg_match('/^[0]*$/', $_GET['id'])) {
    header('Location: ../companies/index.php');
    exit();
}
$hasData = $db->prepare('SELECT COUNT(*) AS cnt FROM companies WHERE id=? AND deleted is NULL');
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
        $error['title'] = '請求名を入力してください';
    } elseif (mb_strlen($_POST['title']) > 64) {
        $error['title'] = '請求名は64字以下で入力してください';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['total'])) {
        $error['total'] = '金額を入力してください';
    } elseif (!preg_match('/^[1-9]+[0-9]*/', $_POST['total']) || strlen($_POST['total']) > 10) {
        $error['total'] = '金額は10桁以下の半角数字のみで入力してください';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['payment_deadline'])) {
        $error['payment_deadline'] = '支払い期限を入力してください';
    } elseif ($_POST['payment_deadline'] <= date("Y-m-d")) {
        $error['payment_deadline'] = '本日以降の日付を入力してください';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['date_of_issue'])) {
        $error['date_of_issue'] = '請求日を入力してください';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['quotation_no'])) {
        $error['quotation_no'] = '見積番号を入力してください';
    } elseif (mb_strlen($_POST['quotation_no']) > 8 || !preg_match('/^[0]*[1-9]+$/', $_POST['quotation_no'])) {
        $error['quotation_no'] = '見積番号は8桁の半角数字で入力して下さい';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['status'])) {
        $error['status'] = '状態を入力してください';
    } elseif (!preg_match('/^[129]$/', $_POST['status'])) {
        $error['status'] = '状態をもう一度選択してください';
    }

    if (!isset($error)) {
        $_SESSION['new_invoice'] = $_POST;
        header('Location: check.php');
        exit();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'rewrite') {
    $_POST = $_SESSION['new_invoice'];
}

$title = '';
$total = '';
$payment_deadline = '';
$date_of_issue = '';
$quotation_no = '';
$status = 1;

if (!empty($_POST)) {
    $title = $_POST['title'];
    $total = $_POST['total'];
    $payment_deadline = $_POST['payment_deadline'];
    $date_of_issue = $_POST['date_of_issue'];
    $quotation_no = $_POST['quotation_no'];
    $status = $_POST['status'];
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>請求作成</title>
</head>
<body>
    <main>
        <div class="container">
            <div class="heding">
                <h1>請求作成</h1>
                <a href="index.php?id=<?= $companyId?>">戻る</a>
            </div>
            <form action="create.php?id=<?= $companyId?>" method="POST">
                <input type="hidden" name="company_id" value=<?= $companyId?>>
                <input type="hidden" name="prefix" value=<?= $companyData['prefix']?>>
                <input type="hidden" name="name" value=<?= $companyData['name']?>>
                <table class="form-items">
                    <tr>
                        <th>請求名</th>
                        <td><input type="text" name="title" value=<?= h($title) ?>></td>
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
                        <td><input type="text" name="total" value=<?= h($total) ?>></td>
                    </tr>
                    <?php if (isset($error['total'])) :?>
                        <p><?= $error['total'] ?></p>
                    <?php endif?>
                    <tr>
                        <th>支払い期限</th>
                        <td><input type="date" name="payment_deadline" value=<?= h($payment_deadline) ?>></td>
                    </tr>
                    <?php if (isset($error['payment_deadline'])) :?>
                        <p><?= $error['payment_deadline'] ?></p>
                    <?php endif?>
                    <tr>
                        <th>請求日</th>
                        <td><input type="date" name="date_of_issue" value=<?= h($date_of_issue) ?>></td>
                    </tr>
                    <?php if (isset($error['date_of_issue'])) :?>
                        <p><?= $error['date_of_issue'] ?></p>
                    <?php endif?>
                    <tr>
                        <th>見積番号</th>
                        <td><?=$companyData['prefix'] . '-q-'?><input type="text" name="quotation_no" value=<?= h($quotation_no) ?>></td>
                    </tr>
                    <?php if (isset($error['quotation_no'])) :?>
                        <p><?= $error['quotation_no'] ?></p>
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
                    <?php if (isset($error['status'])) :?>
                        <p><?= $error['status'] ?></p>
                    <?php endif?>
                </table>
                <input type="submit" value="確認">
            </form>
        </div>
    </main>
</body>
</html>