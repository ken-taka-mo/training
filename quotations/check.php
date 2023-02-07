<?php
require_once('../utils/functions.php');
require_once('../dbconnect.php');

session_start();
if (empty($_SESSION['new_quotation'])) {
    header('Location: index.php');
    exit();
} else {
    $newQuotation = $_SESSION['new_quotation'];
    $lastIdStatement = $db->prepare('SELECT no FROM quotations WHERE company_id =? ORDER BY id DESC LIMIT 1');
    $lastIdStatement->bindParam(1, $newQuotation['company_id'], PDO::PARAM_INT);
    $lastIdStatement->execute();
    $lastId = $lastIdStatement->fetch();
    if (isset($lastId['no'])) {
        $nextNo = intval(substr($lastId['no'], -8)) + 1;
    } else {
        $nextNo = 1;
    }
    $tailNumber = sprintf('%08d', $nextNo);
    $no = $newQuotation['prefix'] . '-q-' . $tailNumber;
}

if (!empty($_POST)) {
    $statement = $db->prepare('INSERT INTO quotations SET company_id=?, no=?, title=?, total=?, validity_period=?, due_date=?, status=?, created=NOW(), modified=NOW()');
    $statement->bindParam(1, $newQuotation['company_id']);
    $statement->bindParam(2, $no);
    $statement->bindParam(3, $newQuotation['title']);
    $statement->bindParam(4, $newQuotation['total'], PDO::PARAM_INT);
    $statement->bindParam(5, $newQuotation['validity_period']);
    $statement->bindParam(6, $newQuotation['due_date']);
    $statement->bindParam(7, $newQuotation['status'], PDO::PARAM_INT);
    $statement->execute();
    unset($_SESSION['new_quotation']);
    header("Location: index.php?id={$newQuotation['company_id']}");
    exit();
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>見積確認ページ</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
    <body>
        <main class="create-page">
            <div class="container">
                <div class="heading">
                    <h1>見積内容確認</h1>
                </div>
                <form action="check.php" method="POST">
                    <input type="hidden" name="id" value=<?= $newQuotation['company_id']?>>
                    <input type="hidden" name="prefix" value=<?= $newQuotation['prefix']?>>
                    <div class="form-items">
                        <div class="item">
                            <h3 class="item-title">見積名</h3>
                            <div class="form-wrapper"><?= h($newQuotation['title']) ?></div>
                        </div>
                        <div class="item">
                            <h3 class="item-title">会社名</h3>
                            <div class="form-wrapper"><?= h($newQuotation['name'])?></div>
                        </div>
                        <div class="item">
                            <h3 class="item-title">金額<span>(半角数字)</span></h3>
                            <div class="form-wrapper"><?= number_format(h($newQuotation['total'])) . '円'?></div>
                        </div>
                        <div class="item">
                            <h3 class="item-title">見積有効期限</h3>
                            <div class="form-wrapper"><?= h($newQuotation['validity_period']) ?></div>
                        </div>
                        <div class="item">
                            <h3 class="item-title">納期<span>(本日以降)</span></h3>
                            <div class="form-wrapper"><?= h($newQuotation['due_date']) ?></div>
                        </div>
                        <div class="item">
                            <h3 class="item-title">状態</h3>
                            <div class="form-wrapper">
                                <?php if ($newQuotation['status'] == 1) :?>
                                    <p>下書き</p>
                                <?php elseif ($newQuotation['status'] == 2) :?>
                                    <p>発行済み</p>
                                <?php else :?>
                                    <p>破棄</p>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                    <a href="create.php?id=<?= h($newQuotation['company_id'])?>&action=rewrite">&laquo;&nbsp;書き直す</a> || <input class="btn btn-form" type="submit" value="作成">
                </form>
            </div>
        </main>
    </body>
</html>