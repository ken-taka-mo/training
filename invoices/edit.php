<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');

if (!isset($_GET['no']) || !preg_match('/^[a-zA-Z0-9]{1,8}?(-i-)[0-9]{8}$/', $_GET['no'])) {
    header('Location: ../companies');
    exit();
}

$no = $_GET['no'];

$invoiceDataStmt = $db->prepare('SELECT id, title, company_id, total, payment_deadline, date_of_issue, no, quotation_no, status FROM invoices WHERE no=?');
$invoiceDataStmt->execute(array($no));
$invoiceData = $invoiceDataStmt->fetch();

$companyDataStmt = $db->prepare('SELECT name, prefix FROM companies WHERE id = ?');
$companyDataStmt->execute(array($invoiceData['company_id']));
$companyData = $companyDataStmt->fetch();

$title = $invoiceData['title'];
$total = $invoiceData['total'];
$paymentDeadline = $invoiceData['payment_deadline'];
$dateOfIssue = $invoiceData['date_of_issue'];
$status = $invoiceData['status'];
$id = $invoiceData['id'];

if (!empty($_POST)) {
    $title = $_POST['title'];
    $total = $_POST['total'];
    $paymentDeadline = $_POST['payment_deadline'];
    $dateOfIssue = $_POST['date_of_issue'];
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
        $updateStmt = $db->prepare('UPDATE invoices SET
        title=?, total=?, payment_deadline=?, date_of_issue=?, status=?, modified=NOW()
        WHERE id=?');
        $updateStmt->bindParam(1, $title);
        $updateStmt->bindParam(2, $total, PDO::PARAM_INT);
        $updateStmt->bindParam(3, $paymentDeadline);
        $updateStmt->bindParam(4, $dateOfIssue);
        $updateStmt->bindParam(5, $status, PDO::PARAM_INT);
        $updateStmt->bindParam(6, $id, PDO::PARAM_INT);
        $updateStmt->execute();
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
    <title>請求編集</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="edit-page">
        <div class="container">
            <div class="heading">
                <h1>請求編集</h1>
                <a href="index.php?id=<?= h($invoiceData['company_id'])?>" class="btn-back">戻る</a>
            </div>
            <form action="edit.php?no=<?= h($no)?>" method="POST">
                <div class="form-items">
                    <div class="item">
                        <h3 class="item-title">請求名</h3>
                        <div class="form-wrapper"><input type="text" name="title" value=<?= h($title) ?>></div>
                    </div>
                    <?php if (isset($error['title'])) :?>
                        <p class="error"><?= $error['title'] ?></p>
                    <?php endif?>
                    <div class="item">
                        <h3 class="item-title">会社名</h3>
                        <div class="form-wrapper"><p><?= $companyData['name']?></p></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">金額<span>(半角数字)</span></h3>
                        <div class="form-wrapper"><input type="text" name="total" value=<?= h($total) ?>>円</div>
                    </div>
                    <?php if (isset($error['total'])) :?>
                        <p class="error"><?= $error['total'] ?></p>
                    <?php endif?>
                    <div class="item">
                        <h3 class="item-title">支払い期限<span>(本日以降)</span></h3>
                        <div class="form-wrapper"><input type="date" class="icon-del" name="payment_deadline" value=<?= h($paymentDeadline) ?>></div>
                    </div>
                    <?php if (isset($error['payment_deadline'])) :?>
                        <p class="error"><?= $error['payment_deadline'] ?></p>
                    <?php endif?>
                    <div class="item">
                        <h3 class="item-title">請求日</h3>
                        <div class="form-wrapper"><input type="date" class="icon-del" name="date_of_issue" value=<?= h($dateOfIssue) ?>></div>
                    </div>
                    <?php if (isset($error['date_of_issue'])) :?>
                        <p class="error"><?= $error['date_of_issue'] ?></p>
                    <?php endif?>
                    <div class="item">
                        <h3 class="item-title">見積番号<span>(半角数字)</span></h3>
                        <div class="q-no-wrapper">
                            <p><?=$companyData['prefix'] . '-q-'?></p>
                            <input type="text" name="quotation_no" maxlength="8" value=<?= h($invoiceData['quotation_no'])?> >
                        </div>
                    </div>
                    <?php if (isset($error['quotation_no'])) :?>
                        <p class="error"><?= $error['quotation_no'] ?></p>
                    <?php endif?>
                    <div class="item">
                        <h3 class="item-title">状態</h3>
                        <div class="form-wrapper">
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
                        </div>
                    </div>
                    <?php if (isset($error['status'])) :?>
                        <p class="error"><?= $error['status'] ?></p>
                    <?php endif?>
                </div>
                <input class="btn btn-form" type="submit" value="更新">
            </form>
        </div>
    </main>
</body>
</html>