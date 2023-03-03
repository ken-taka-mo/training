<?php
require_once('../utils/functions.php');
require_once('../databese/dbconnect.php');
session_start();

if (empty($_SESSION['new_quotation'])) {
    header('Location: index.php');
    exit();
}

$newQuotation = $_SESSION['new_quotation'];
// :company_idを持つ見積データの末尾データの見積番号を取得
$lastNoStmt = $db->prepare('SELECT no FROM quotations WHERE company_id =:company_id ORDER BY id DESC LIMIT 1');
$lastNoStmt->bindParam(':company_id', $newQuotation['company_id'], PDO::PARAM_INT);
$lastNoStmt->execute();
$lastNo = $lastNoStmt->fetch();
// 見積番号の下8桁(数字部分)に+1して$nextNoに代入する $lastNo存在しない場合1を代入
$nextNo = !empty($lastNo) ? $nextNo = intval(substr($lastNo['no'], -8)) + 1 : 1;
// 八桁になるまで0を先頭に代入
$tailNumber = sprintf('%08d', $nextNo);
// prefixと指定記号と$tailNumberで見積番号の作成
$no = $newQuotation['prefix'] . '-q-' . $tailNumber;

// 作成ボタンクリック後見積テーブルにinsert
if (!empty($_POST)) {
    $insertStmt = $db->prepare('INSERT INTO quotations SET company_id=:company_id, no=:no, title=:title, total=:total, validity_period=:validity_period, due_date=:due_date, status=:status, created=NOW(), modified=NOW()');
    $insertStmt->bindParam(':company_id', $newQuotation['company_id']);
    $insertStmt->bindParam(':no', $no);
    $insertStmt->bindParam(':title', $newQuotation['title']);
    $insertStmt->bindParam(':total', $newQuotation['total'], PDO::PARAM_INT);
    $insertStmt->bindParam(':validity_period', $newQuotation['validity_period']);
    $insertStmt->bindParam(':due_date', $newQuotation['due_date']);
    $insertStmt->bindParam(':status', $newQuotation['status'], PDO::PARAM_INT);
    $insertStmt->execute();
    // insert後セッションの値を消去する
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