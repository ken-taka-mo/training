<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');

$no = $_GET['no'];

if (!isset($no) || !preg_match('/^[a-zA-Z0-9]{1,8}?(-q-)[0-9]{8}$/', $no)) {
    header('Location: ../companies/index.php');
    exit();
}



$quotationCntStmt = $db->prepare('SELECT COUNT(*) AS cnt FROM quotations WHERE no=:no');
$quotationCntStmt->execute([':no' => $no]);
$quotationCnt = $quotationCntStmt->fetch();
if ($quotationCnt['cnt'] < 1) {
    header('Location: ../companies/index.php');
    exit();
}

$quotationDataStmt = $db->prepare('SELECT id, title, company_id, total, validity_period, due_date, status FROM quotations WHERE no=:no');
$quotationDataStmt->execute([':no' => $no]);
$quotationData = $quotationDataStmt->fetch();

$companyNameStmt = $db->prepare('SELECT name FROM companies WHERE id=:id');
$companyNameStmt->execute([':id' => $quotationData['company_id']]);
$companyName = $companyNameStmt->fetch();

$title = $quotationData['title'];
$total = $quotationData['total'];
$validityPeriod = $quotationData['validity_period'];
$dueDate = $quotationData['due_date'];
$status = $quotationData['status'];
$id = $quotationData['id'];

$post = $_POST;
$items = [];
$error = [];

if (!empty($post)) {
    $items = convert_half_width($post);
    $error = check_quotation($items);

    $title = $items['title'];
    $total = $items['total'];
    $validityPeriod = $items['validity_period'];
    $dueDate = $items['due_date'];
    $status = $items['status'];

    if (empty($error)) {
        $updateStmt = $db->prepare('UPDATE quotations SET
        title=:title, total=:total, validity_period=:validity_period, due_date=:due_date, status=:status, modified=NOW()
        WHERE id=:id');
        $updateStmt->bindParam(':title', $title);
        $updateStmt->bindParam(':total', $total, PDO::PARAM_INT);
        $updateStmt->bindParam(':validity_period', $validityPeriod);
        $updateStmt->bindParam(':due_date', $dueDate);
        $updateStmt->bindParam(':status', $status, PDO::PARAM_INT);
        $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
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
                        <h3 class="item-title">金額<span>(半角9桁以下)</span></h3>
                        <div class="form-wrapper"><input type="text" name="total" class="total-input" maxlength="9" value=<?= h($total) ?>>円</div>
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