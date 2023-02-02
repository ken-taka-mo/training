<?php
session_start();
if (empty($_SESSION['new_quotation'])) {
    echo "";
    header('Location: index.php');
    exit();
} else {
    $newQuotation = $_SESSION['new_quotation'];
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
                            <td><?= $newQuotation['title'] ?></td>
                        </tr>
                        <tr>
                            <th>会社名</th>
                            <td><?= $newQuotation['name']?></td>
                        </tr>
                        <tr>
                            <th>金額</th>
                            <td><?= $newQuotation['total'] ?></td>
                        </tr>
                        <tr>
                            <th>見積有効期限</th>
                            <td><?= $newQuotation['validity_period'] ?></td>
                        </tr>
                        <tr>
                            <th>納期</th>
                            <td><?= $newQuotation['due_date'] ?></td>
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
                    <input type="submit" value="見積作成">
                </form>
            </div>
        </main>
    </body>
</html>