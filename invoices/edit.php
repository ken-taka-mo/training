<?php
require_once('../databese/dbconnect.php');
require_once('../utils/functions.php');
require_once('../utils/validation.php');

// パラメータのバリデーション(no)
$no = $_GET['no'];
if (!isset($no) || !preg_match('/^[a-zA-Z0-9]{1,8}?(-i-)[0-9]{8}$/', $no)) {
    header('Location: ../companies/index.php');
    exit();
}
// $noを持つ請求データの取得
$invoiceDataStmt = $db->prepare('SELECT id, title, company_id, total, payment_deadline, date_of_issue, no, quotation_no, status FROM invoices WHERE no=:no AND deleted is NULL');
$invoiceDataStmt->execute([':no' => $no]);
$invoiceData = $invoiceDataStmt->fetch(PDO::FETCH_ASSOC);
// なければ会社一覧ページに遷移
if (!$invoiceData) {
    header('Location: ../companies/index.php');
    exit();
}
// company_idをidに持つ会社データの取得
$companyDataStmt = $db->prepare('SELECT name, prefix FROM companies WHERE id=:company_id AND deleted is NULL');
$companyDataStmt->execute(['company_id' => $invoiceData['company_id']]);
$companyData = $companyDataStmt->fetch(PDO::FETCH_ASSOC);
// なければ会社一覧ページに遷移
if (!$companyData) {
    header('Location: ../companies/index.php');
    exit();
}

// フォームの初期値
$title = $invoiceData['title'];
$total = $invoiceData['total'];
$paymentDeadline = $invoiceData['payment_deadline'];
$dateOfIssue = $invoiceData['date_of_issue'];
$status = $invoiceData['status'];
$id = $invoiceData['id'];

$post = $_POST;
if (!empty($post)) {
    // 全角スペース、全角数字を半角に
    $items = convert_half_width($post);
    // バリデーションにかける
    $error = check_invoice($items);

    // フォームの値に$itemsを代入
    $title = $items['title'];
    $total = $items['total'];
    $paymentDeadline = $items['payment_deadline'];
    $dateOfIssue = $items['date_of_issue'];
    $status = $items['status'];

    // 問題なければupdate
    if (empty($error)) {
        $updateStmt = $db->prepare('UPDATE invoices SET
        title=:title, total=:total, payment_deadline=:payment_deadline, date_of_issue=:date_of_issue, status=:status, modified=NOW()
        WHERE id=:id');
        $updateStmt->bindParam(':title', $title);
        $updateStmt->bindParam(':total', $total, PDO::PARAM_INT);
        $updateStmt->bindParam(':payment_deadline', $paymentDeadline);
        $updateStmt->bindParam(':date_of_issue', $dateOfIssue);
        $updateStmt->bindParam(':status', $status, PDO::PARAM_INT);
        $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
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
                        <h3 class="item-title">金額<span>(半角9桁以下)</span></h3>
                        <div class="form-wrapper"><input type="text" name="total" class="total-input" maxlength="9" value=<?= h($total) ?>>円</div>
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
                            <p><?=h($companyData['prefix']) . '-q-' . h($invoiceData['quotation_no'])?></p>
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