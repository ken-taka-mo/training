<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');
session_start();
if (empty($_GET['id']) || !preg_match('/^\d*[1-9]+$/', $_GET['id'])) {
    header('Location: ../companies/index.php');
    exit();
}

$countStmt = $db->prepare('SELECT COUNT(*) AS cnt FROM companies WHERE id=? AND deleted is NULL');
$countStmt->execute(array($_GET['id']));
$count = $countStmt->fetch();
if ($count['cnt']) {
    $companyId = $_GET['id'];
} else {
    header('Location: ../companies/index.php');
    exit();
}

$companyDataStmt = $db->prepare('SELECT name, manager_name, prefix FROM companies WHERE id=?');
$companyDataStmt->execute(array($companyId));
$companyData = $companyDataStmt->fetch();

if (!empty($_POST)) {
    $preQuotationNo = "{$companyData['prefix']}-q-{$_POST['quotation_no']}";
    $quotaionCntStmt = $db->prepare('SELECT COUNT(*) AS cnt FROM quotations WHERE no=?');
    $quotaionCntStmt->execute(array($preQuotationNo));
    $quotationCount = $quotaionCntStmt->fetch();

    if (preg_match('/^[\s\n\t]*$/', $_POST['title'])) {
        $error['title'] = '請求名を入力してください';
    } elseif (mb_strlen($_POST['title']) > 64) {
        $error['title'] = '請求名は64字以下で入力してください';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['total'])) {
        $error['total'] = '金額を入力してください';
    } elseif (!preg_match('/^[1-9]{1}\d{0,8}$/', $_POST['total'])) {
        $error['total'] = '金額は9桁以下の半角数字のみで入力してください';
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
    } elseif (!preg_match('/^\d{8}$/', $_POST['quotation_no'])) {
        $error['quotation_no'] = '見積番号は8桁の半角数字で入力して下さい';
    } elseif (!$quotationCount['cnt']) {
        $error['quotation_no'] = '入力された見積番号は存在しません';
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
$paymentDeadline = '';
$dateOfIssue = '';
$quotationNo = '';
$status = '';

if (!empty($_POST)) {
    $title = $_POST['title'];
    $total = $_POST['total'];
    $paymentDeadline = $_POST['payment_deadline'];
    $dateOfIssue = $_POST['date_of_issue'];
    $quotationNo = $_POST['quotation_no'];
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
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="create-page">
        <div class="container">
            <div class="heading">
                <h1>請求作成</h1>
                <a href="index.php?id=<?= $companyId?>" class="btn-back">戻る</a>
            </div>
            <form action="create.php?id=<?= $companyId?>" method="POST">
                <input type="hidden" name="company_id" value=<?= $companyId?>>
                <input type="hidden" name="prefix" value=<?= $companyData['prefix']?>>
                <input type="hidden" name="name" value=<?= $companyData['name']?>>
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
                            <input type="text" name="quotation_no" maxlength="8" value=<?= h($quotationNo)?> >
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
                                <?php elseif ($status == 9) :?>
                                    <option value="1">下書き</option>
                                    <option value="2">発行済み</option>
                                    <option value="9" selected>破棄</option>
                                <?php else :?>
                                    <option value="">選択してください</option>
                                    <option value="1">下書き</option>
                                    <option value="2">発行済み</option>
                                    <option value="9">破棄</option>
                                <?php endif ?>
                            </select>
                        </div>
                    </div>
                    <?php if (isset($error['status'])) :?>
                        <p class="error"><?= $error['status'] ?></p>
                    <?php endif?>
                    </div>
                <input class="btn btn-form" type="submit" value="請求作成">
            </form>
        </div>
    </main>
</body>
</html>