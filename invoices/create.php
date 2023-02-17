<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');
session_start();

// パラメータidのバリデーション
if (!is_exact_id($_GET['id'])) {
    header('Location: ../companies/index.php');
    exit();
}
$companyId = $_GET['id'];

// 会社名・担当者名・プレフィックスを取得
$companyDataStmt = $db->prepare('SELECT name, manager_name, prefix FROM companies WHERE id=:company_id AND deleted is NULL');
$companyDataStmt->execute([':company_id' => $companyId]);
$companyData = $companyDataStmt->fetch(PDO::FETCH_ASSOC);
if (!$companyData) {
    header('Location: ../companies/index.php');
    exit();
}

// フォームの初期値
$title = '';
$total = '';
$paymentDeadline = '';
$dateOfIssue = '';
$quotationNo = '';
$status = '';

// 見積番号セレクトボックスの値を取得し配列に代入
$quotationsNoStmt = $db->prepare('SELECT no FROM quotations WHERE company_id=:company_id AND deleted is NULL');
$quotationsNoStmt->execute(['company_id' => $companyId]);
$quotationsNoArray = $quotationsNoStmt->fetchAll(PDO::FETCH_ASSOC);
$qNoArray = [];
for ($x = 0; $x < count($quotationsNoArray); $x++) {
    array_push($qNoArray, substr($quotationsNoArray[$x]['no'], -8));
}

$post = $_POST;
if (!empty($post)) {
    // 全角スペース、全角数字を半角に
    $items = convert_half_width($post);
    // 請求データバリデーションにかける
    $error = check_invoice($items);
    // 問題がなければ確認ページにセッションで値を渡す
    if (empty($error)) {
        $_SESSION['new_invoice'] = $items;
        header('Location: check.php');
        exit();
    }
}
// 書き戻しで遷移してきた場合セッションの値を$itemsに代入
if (isset($_GET['action']) && $_GET['action'] == 'rewrite') {
    $items = $_SESSION['new_invoice'];
}
// $itemsに値がある場合フォームの初期値に$itemsを代入
if (!empty($items)) {
    $title = $items['title'];
    $total = $items['total'];
    $paymentDeadline = $items['payment_deadline'];
    $dateOfIssue = $items['date_of_issue'];
    $quotationNo = $items['quotation_no'];
    $status = $items['status'];
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
                            <?php if (!$qNoArray) :?>
                                <p class="error">見積が作成されていません</p>
                            <?php else :?>
                                <p><?=$companyData['prefix'] . '-q-'?></p>
                                <select name="quotation_no" class="no-select" id="">
                                    <option value="">選択してください</option>
                                    <?php for ($y = 0; $y < count($qNoArray); $y++) :?>
                                        <?php if ($quotationNo == $qNoArray[$y]) :?>
                                            <option value=<?=$qNoArray[$y]?> selected><?=$qNoArray[$y]?></option>
                                        <?php else :?>
                                            <option value=<?=$qNoArray[$y]?>><?=$qNoArray[$y]?></option>
                                        <?php endif?>
                                    <?php endfor ?>
                                </select>
                            <?php endif?>
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
                <?php if (count($qNoArray) > 0) :?>
                    <input class="btn btn-form" type="submit" value="請求作成">
                <?php endif?>
            </form>
        </div>
    </main>
</body>
</html>