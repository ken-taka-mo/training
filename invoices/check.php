<?php
require_once('../databese/dbconnect.php');
require_once('../utils/functions.php');
session_start();
// セッションに値がなければ会社一覧ページに遷移
if (empty($_SESSION['new_invoice'])) {
    header('Location: ../companeies/index.php');
    exit();
}
$newInvoice = $_SESSION['new_invoice'];
// 請求番号の下８桁を作成
// 同じcompany_idを持つ末尾の請求データのnoを取得する
$lastIdStmt = $db->prepare('SELECT no FROM invoices WHERE company_id=:company_id ORDER BY id DESC LIMIT 1');
$lastIdStmt->bindParam('company_id', $newInvoice['company_id'], PDO::PARAM_INT);
$lastIdStmt->execute();
$lastId = $lastIdStmt->fetch(PDO::FETCH_ASSOC);
// noの下８桁を切り取る　まだ請求データが存在しなければ１を代入
$nextNo = isset($lastId['no']) ? intval(substr($lastId['no'], -8)) + 1 : 1;
// ８桁になるまで0を先頭に代入
$tailNumber = sprintf('%08d', $nextNo);
// prefixと指定記号と$tailNumberで見積番号の作成
$no = $newInvoice['prefix'] . '-i-' . $tailNumber;

// 作成ボタンが押されたらinvoiceテーブルにinsert
if (!empty($_POST)) {
    $insertStmt = $db->prepare('INSERT INTO invoices SET company_id=:company_id, no=:no, title=:title, total=:total, payment_deadline=:payment_deadline, date_of_issue=:date_of_issue, quotation_no=:quotation_no, status=:status, created=NOW(), modified=NOW()');
    $insertStmt->bindParam(':company_id', $newInvoice['company_id']);
    $insertStmt->bindParam(':no', $no);
    $insertStmt->bindParam(':title', $newInvoice['title']);
    $insertStmt->bindParam(':total', $newInvoice['total'], PDO::PARAM_INT);
    $insertStmt->bindParam(':payment_deadline', $newInvoice['payment_deadline']);
    $insertStmt->bindParam(':date_of_issue', $newInvoice['date_of_issue']);
    $insertStmt->bindParam(':quotation_no', $newInvoice['quotation_no']);
    $insertStmt->bindParam(':status', $newInvoice['status'], PDO::PARAM_INT);
    $insertStmt->execute();
    // セッションを削除
    unset($_SESSION['new_quotation']);
    header("Location: index.php?id={$newInvoice['company_id']}");
    exit();
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
                <h1>請求入力確認</h1>
            </div>
            <form action="check.php?id=<?= $newInvoice['company_id']?>" method="POST">
                <input type="hidden" name="company_id" value=<?= $newInvoice['company_id']?>>
                <input type="hidden" name="prefix" value=<?= $newInvoice['prefix']?>>
                <div class="form-items">
                    <div class="item">
                        <h3 class="item-title">請求名</h3>
                        <div class="form-wrapper"><?= h($newInvoice['title']) ?></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">会社名</h3>
                        <div class="form-wrapper"><?= h($newInvoice['name'])?></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">金額<span>(半角数字)</span></h3>
                        <div class="form-wrapper"><?= number_format(h($newInvoice['total'])) ?>円</div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">支払い期限<span>(本日以降)</span></h3>
                        <div class="form-wrapper"><?= h($newInvoice['payment_deadline']) ?></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">請求日</h3>
                        <div class="form-wrapper"><?= h($newInvoice['date_of_issue']) ?></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">見積番号<span>(半角数字)</span></h3>
                        <div class="q-no-wrapper">
                            <p><?= $newInvoice['prefix'] . '-q-' . h($newInvoice['quotation_no']) ?></p>
                        </div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">状態</h3>
                        <div class="form-wrapper">
                            <?php if ($newInvoice['status'] == 1) :?>
                                <td>下書き</td>
                            <?php elseif ($newInvoice['status'] == 2) :?>
                                <td>発行済み</td>
                            <?php else :?>
                                <td>破棄</td>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
                <a href="create.php?id=<?= h($newInvoice['company_id'])?>&action=rewrite">&laquo;&nbsp;書き直す</a> || <input class="btn btn-form" type="submit" value="作成">
            </form>
        </div>
    </main>
</body>
</html>