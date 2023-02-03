<?php
require_once('../utils/functions.php');
require_once('../dbconnect.php');

session_start();
if (empty($_SESSION['new_quotation'])) {
    header('Location: index.php');
    exit();
} else {
    $newQuotation = $_SESSION['new_quotation'];
    $lastIdStatement = $db->prepare('SELECT id FROM quotations WHERE company_id =? ORDER BY id DESC LIMIT 1');
    $lastIdStatement->bindParam(1, $newQuotation['company_id'], PDO::PARAM_INT);
    $lastIdStatement->execute();
    $lastId = $lastIdStatement->fetch();
    $nextNo = $lastId['id'] + 1;
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
</head>
    <body>
        <main>
            <div class="container">
                <div class="heding">
                    <h1>見積内容確認</h1>
                </div>
                <form action="check.php" method="POST">
                    <input type="hidden" name="id" value=<?= $newQuotation['company_id']?>>
                    <input type="hidden" name="prefix" value=<?= $newQuotation['prefix']?>>
                    <table class="form-items">
                        <tr>
                            <th>見積名</th>
                            <td><?= h($newQuotation['title']) ?></td>
                        </tr>
                        <tr>
                            <th>会社名</th>
                            <td><?= h($newQuotation['name'])?></td>
                        </tr>
                        <tr>
                            <th>金額</th>
                            <td><?= h($newQuotation['total']) ?></td>
                        </tr>
                        <tr>
                            <th>見積有効期限</th>
                            <td><?= h($newQuotation['validity_period']) ?></td>
                        </tr>
                        <tr>
                            <th>納期</th>
                            <td><?= h($newQuotation['due_date']) ?></td>
                        </tr>
                        <tr>
                            <th>状態</th>
                            <?php if ($newQuotation['status'] == 1) :?>
                                <td>下書き</td>
                            <?php elseif ($newQuotation['status'] == 2) :?>
                                <td>発行済み</td>
                            <?php else :?>
                                <td>破棄</td>
                            <?php endif ?>
                        </tr>
                    </table>
                    <a href="create.php?id=<?= h($newQuotation['company_id'])?>&action=rewrite">&laquo;&nbsp;書き直す</a> || <input type="submit" value="見積作成">
                </form>
            </div>
        </main>
    </body>
</html>