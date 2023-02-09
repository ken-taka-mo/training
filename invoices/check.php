<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');
session_start();

if (empty($_SESSION['new_invoice'])) {
    header('Location: index.php');
    exit();
} else {
    $newInvoice = $_SESSION['new_invoice'];
    $lastIdStatement = $db->prepare('SELECT no FROM invoices WHERE company_id=? ORDER BY id DESC LIMIT 1');
    $lastIdStatement->bindParam(1, $newInvoice['company_id'], PDO::PARAM_INT);
    $lastIdStatement->execute();
    $lastId = $lastIdStatement->fetch();
    if (isset($lastId['no'])) {
        $nextNo = intval(substr($lastId['no'], -8)) + 1;
    } else {
        $nextNo = 1;
    }
    $tailNumber = sprintf('%08d', $nextNo);
    $no = $newInvoice['prefix'] . '-i-' . $tailNumber;
}

if (!empty($_POST)) {
    $statement = $db->prepare('INSERT INTO invoices SET company_id=?, no=?, title=?, total=?, payment_deadline=?, date_of_issue=?, quotation_no=?, status=?, created=NOW(), modified=NOW()');
    $statement->bindParam(1, $newInvoice['company_id']);
    $statement->bindParam(2, $no);
    $statement->bindParam(3, $newInvoice['title']);
    $statement->bindParam(4, $newInvoice['total'], PDO::PARAM_INT);
    $statement->bindParam(5, $newInvoice['payment_deadline']);
    $statement->bindParam(6, $newInvoice['date_of_issue']);
    $statement->bindParam(7, $newInvoice['quotation_no']);
    $statement->bindParam(8, $newInvoice['status'], PDO::PARAM_INT);
    $statement->execute();
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