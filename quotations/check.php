<?php
require_once('../utils/functions.php');
require_once('../dbconnect.php');

session_start();
if (empty($_SESSION['new_quotation'])) {
    header('Location: index.php');
    exit();
} else {
    $newQuotation = $_SESSION['new_quotation'];
    $countStatement = $db->prepare('SELECT COUNT(*) AS cnt FROM quotations WHERE company_id =?');
    $countStatement->bindParam(1, $newQuotation['company_id'], PDO::PARAM_INT);
    $countStatement->execute();
    $countQuotation = $countStatement->fetch();
    $nextNo = $countQuotation['cnt'] + 1;
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
    $statement->bindParam(7, $newQuotation['total'], PDO::PARAM_INT);
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
                            <td>
                                <select name="status" id="">
                                    <?php if ($newQuotation['status'] == 1) :?>
                                        <option value="1" selected>下書き</option>
                                        <option value="2">発行済み</option>
                                        <option value="9">破棄</option>
                                    <?php elseif ($newQuotation['status'] == 2) :?>
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
                    <a href="create.php?id=<?= h($newQuotation['company_id'])?>&action=rewrite">&laquo;&nbsp;書き直す</a> || <input type="submit" value="見積作成">
                </form>
            </div>
        </main>
    </body>
</html>