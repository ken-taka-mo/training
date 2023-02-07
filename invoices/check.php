<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');
session_start();

if (empty($_SESSION['new_invoice'])) {
    header('Location: index.php');
    exit();
} else {
    $new_invoice = $_SESSION['new_invoice'];
    $lastIdStatement = $db->prepare('SELECT no FROM invoices WHERE company_id=? ORDER BY id DESC LIMIT 1');
    $lastIdStatement->bindParam(1, $new_invoice['company_id'], PDO::PARAM_INT);
    $lastIdStatement->execute();
    $lastId = $lastIdStatement->fetch();
    if (isset($lastId['no'])) {
        $nextNo = intval(substr($lastId['no'], -8)) + 1;
    } else {
        $nextNo = 1;
    }
    $tailNumber = sprintf('%08d', $nextNo);
    $no = $new_invoice['prefix'] . '-i-' . $tailNumber;
}

if (!empty($_POST)) {
    $statement = $db->prepare('INSERT INTO invoices SET company_id=?, no=?, title=?, total=?, payment_deadline=?, date_of_issue=?, quotation_no=?, status=?, created=NOW(), modified=NOW()');
    $statement->bindParam(1, $new_invoice['company_id']);
    $statement->bindParam(2, $no);
    $statement->bindParam(3, $new_invoice['title']);
    $statement->bindParam(4, $new_invoice['total'], PDO::PARAM_INT);
    $statement->bindParam(5, $new_invoice['payment_deadline']);
    $statement->bindParam(6, $new_invoice['date_of_issue']);
    $statement->bindParam(7, $new_invoice['quotation_no']);
    $statement->bindParam(8, $new_invoice['status'], PDO::PARAM_INT);
    $statement->execute();
    unset($_SESSION['new_quotation']);
    header("Location: index.php?id={$new_invoice['company_id']}");
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
            <form action="check.php?id=<?= $new_invoice['company_id']?>" method="POST">
                <input type="hidden" name="company_id" value=<?= $new_invoice['company_id']?>>
                <input type="hidden" name="prefix" value=<?= $new_invoice['prefix']?>>
                <div class="form-items">
                    <div class="item">
                        <h3 class="item-title">請求名</h3>
                        <div class="form-wrapper"><?= h($new_invoice['title']) ?></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">会社名</h3>
                        <div class="form-wrapper"><?= $new_invoice['name']?></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">金額<span>(半角数字)</span></h3>
                        <div class="form-wrapper"><?= number_format(h($new_invoice['total'])) ?>円</div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">支払い期限<span>(本日以降)</span></h3>
                        <div class="form-wrapper"><?= h($new_invoice['payment_deadline']) ?></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">請求日</h3>
                        <div class="form-wrapper"><?= h($new_invoice['date_of_issue']) ?></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">見積番号<span>(半角数字)</span></h3>
                        <div class="q-no-wrapper">
                            <p><?= $new_invoice['prefix'] . '-q-' . h($new_invoice['quotation_no']) ?></p>
                        </div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">状態</h3>
                        <div class="form-wrapper">
                            <?php if ($new_invoice['status'] == 1) :?>
                                <td>下書き</td>
                            <?php elseif ($new_invoice['status'] == 2) :?>
                                <td>発行済み</td>
                            <?php else :?>
                                <td>破棄</td>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
                <a href="create.php?id=<?= h($new_invoice['company_id'])?>&action=rewrite">&laquo;&nbsp;書き直す</a> || <input class="btn btn-form" type="submit" value="作成">
            </form>
        </div>
    </main>
</body>
</html>