<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');

if (!isset($_GET['no']) || !preg_match('/^[a-zA-Z0-9]{1,8}?(-q-)[0-9]{8}$/', $_GET['no'])) {
    header('Location: ../companies/index.php');
    exit();
}

$no = $_GET['no'];

$quotationCntStmt = $db->prepare('SELECT COUNT(*) AS cnt FROM quotations WHERE no=?');
$quotationCntStmt->execute(array($no));
$quotationCnt = $quotationCntStmt->fetch();
if ($quotationCnt['cnt'] < 1) {
    header('Location: ../companies/index.php');
    exit();
}

$quotationDataStmt = $db->prepare('SELECT id, title, company_id, total, validity_period, due_date, status FROM quotations WHERE no=?');
$quotationDataStmt->execute(array($no));
$quotationData = $quotationDataStmt->fetch();

$companyNameStmt = $db->prepare('SELECT name FROM companies WHERE id = ?');
$companyNameStmt->execute(array($quotationData['company_id']));
$companyName = $companyNameStmt->fetch();

$title = $quotationData['title'];
$total = $quotationData['total'];
$validityPeriod = $quotationData['validity_period'];
$dueDate = $quotationData['due_date'];
$status = $quotationData['status'];
$id = $quotationData['id'];

if (!empty($_POST)) {
    $title = $_POST['title'];
    $total = $_POST['total'];
    $validityPeriod = $_POST['validity_period'];
    $dueDate = $_POST['due_date'];
    $status = $_POST['status'];

    if (preg_match('/^[\s\n\t]*$/', $_POST['title'])) {
        $error['title'] = '見積名を入力してください';
    } elseif (mb_strlen($_POST['title']) > 64) {
        $error['title'] = '見積名は64以下で入力してください';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['total'])) {
        $error['total'] = '金額を入力してください';
    } elseif (!preg_match('/^[1-9]+[0-9]*/', $_POST['total']) || strlen($_POST['total']) > 10) {
        $error['total'] = '金額は10桁以下の半角数字のみで入力してください';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['validity_period'])) {
        $error['validity_period'] = '見積有効期限を入力してください';
    } elseif (mb_strlen($_POST['validity_period']) > 32) {
        $error['validity_period'] = '見積有効期限を入力しなおしてください';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['due_date'])) {
        $error['due_date'] = '納期を入力してください';
    } elseif ($_POST['due_date'] <= date("Y-m-d")) {
        $error['due_date'] = '本日以降の日付を入力してください';
    }
    if (preg_match('/^[\s\n\t]*$/', $_POST['status'])) {
        $error['status'] = '状態を入力してください';
    } elseif (!preg_match('/^[129]$/', $_POST['status'])) {
        $error['status'] = '状態をもう一度選択してください';
    }

    if (empty($error)) {
        $updateStmt = $db->prepare('UPDATE quotations SET
        title=?, total=?, validity_period=?, due_date=?, status=?, modified=NOW()
        WHERE id=?');
        $updateStmt->bindParam(1, $title);
        $updateStmt->bindParam(2, $total, PDO::PARAM_INT);
        $updateStmt->bindParam(3, $validityPeriod);
        $updateStmt->bindParam(4, $dueDate);
        $updateStmt->bindParam(5, $status, PDO::PARAM_INT);
        $updateStmt->bindParam(6, $id, PDO::PARAM_INT);
        $updateStmt->execute();
        header("Location: index.php?id={$quotationData['company_id']}");
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
    <title>見積編集</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="edit-page">
        <div class="container">
            <div class="heading">
                <h1>見積編集</h1>
                <a href="index.php?id=<?= h($quotationData['company_id'])?>" class="btn-back">戻る</a>
            </div>
            <form action="edit.php?no=<?= h($no)?>" method="POST">
                <div class="form-items">
                    <div class="item">
                        <h3 class="item-title">見積名</h3>
                        <div class="form-wrapper"><input type="text" name="title" value=<?= h($title) ?>></div>
                    </div>
                    <?php if (isset($error['title'])) :?>
                        <p class="error"><?= $error['title'] ?></p>
                    <?php endif?>
                    <div class="item">
                        <h3 class="item-title">会社名</h3>
                        <div class="form-wrapper"><p><?= h($companyName['name'])?></p></div>
                    </div>
                    <div class="item">
                        <h3 class="item-title">金額<span>(半角数字)</span></h3>
                        <div class="form-wrapper"><input type="text" name="total" value=<?= h($total) ?>>円</div>
                    </div>
                    <?php if (isset($error['total'])) :?>
                        <p class="error"><?= $error['total'] ?></p>
                    <?php endif?>
                    <div class="item">
                        <h3 class="item-title">見積有効期限</h3>
                        <div class="form-wrapper"><input class="icon-del" type="date" name="validity_period" value=<?= h($validityPeriod) ?>></div>
                    </div>
                    <?php if (isset($error['validity_period'])) :?>
                        <p class="error"><?= $error['validity_period'] ?></p>
                    <?php endif?>
                    <div class="item">
                        <h3 class="item-title">納期<span>(本日以降)</span></h3>
                        <div class="form-wrapper"><input class="icon-del" type="date" name="due_date" value=<?= h($dueDate) ?>></div>
                    </div>
                    <?php if (isset($error['due_date'])) :?>
                        <p class="error"><?= $error['due_date'] ?></p>
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