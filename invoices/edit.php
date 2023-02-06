<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');

if (!isset($_GET['no']) || !preg_match('/^[a-zA-Z0-9]{1,8}?(-i-)[0-9]{8}$/', $_GET['no'])) {
    header('Location: ../companies');
    exit();
}

$no = $_GET['no'];

$statement = $db->prepare('SELECT id, title, company_id, total, payment_deadline, date_of_issue, no, quotation_no, status FROM invoices WHERE no=?');
$statement->execute(array($no));
$invoiceData = $statement->fetch();
$companyDataStatement = $db->prepare('SELECT name, prefix FROM companies WHERE id = ?');
$companyDataStatement->execute(array($invoiceData['company_id']));
$companyData = $companyDataStatement->fetch();

$title = $invoiceData['title'];
$total = $invoiceData['total'];
$payment_deadline = $invoiceData['payment_deadline'];
$date_of_issue = $invoiceData['date_of_issue'];
$status = $invoiceData['status'];
$id = $invoiceData['id'];

if (!empty($_POST)) {
    $title = $_POST['title'];
    $total = $_POST['total'];
    $payment_deadline = $_POST['payment_deadline'];
    $date_of_issue = $_POST['date_of_issue'];
    $status = $_POST['status'];

    if (preg_match('/^[\s\n\t]*$/', $_POST['title'])) {
        $error['title'] = '請求名を入力してください';
    } elseif (mb_strlen($_POST['title']) > 64) {
        $error['title'] = '請求名は64以下で入力してください';
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
    if (preg_match('/^[\s\n\t]*$/', $_POST['status'])) {
        $error['status'] = '状態を入力してください';
    } elseif (!preg_match('/^[129]$/', $_POST['status'])) {
        $error['status'] = '状態をもう一度選択してください';
    }

    if (empty($error)) {
        $updateStatement = $db->prepare('UPDATE invoices SET
        title=?, total=?, payment_deadline=?, date_of_issue=?, status=?, modified=NOW()
        WHERE id=?');
        $updateStatement->bindParam(1, $title);
        $updateStatement->bindParam(2, $total, PDO::PARAM_INT);
        $updateStatement->bindParam(3, $payment_deadline);
        $updateStatement->bindParam(4, $date_of_issue);
        $updateStatement->bindParam(5, $status, PDO::PARAM_INT);
        $updateStatement->bindParam(6, $id, PDO::PARAM_INT);
        $updateStatement->execute();
        header("Location: index.php?id={$invoiceData['company_id']}");
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
    <title>見積編集</title>
</head>
<body>
    <main>
        <div class="container">
            <div class="heding">
                <h1>見積編集</h1>
                <a href="index.php?id=<?= h($invoiceData['company_id'])?>">戻る</a>
            </div>
            <form action="edit.php?no=<?= h($no)?>" method="POST">
                <table>
                    <tr>
                        <th>請求番号</th>
                        <td><?= $invoiceData['no']?></td>
                    </tr>
                    <tr>
                        <th>請求名</th>
                        <td><input type="text" name="title" value=<?= h($title) ?>></td>
                    </tr>
                    <?php if (isset($error['title'])) :?>
                        <p><?= $error['title'] ?></p>
                    <?php endif?>
                    <tr>
                        <th>会社名</th>
                        <td><?= h($companyData['name'])?></td>
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
                        <td><?= $companyData['prefix'] . '-q-' . $invoiceData['quotation_no']?></td>
                    </tr>
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