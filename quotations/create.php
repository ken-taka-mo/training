<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');
session_start();

if (empty($_GET['id']) || !preg_match('/^[1-9]+[0]*$/', $_GET['id'])) {
    header('Location: ../companies/index.php');
    exit();
}

$countStmt = $db->prepare('SELECT COUNT(*) AS cnt FROM companies WHERE id=? AND deleted is NULL');
$countStmt->execute(array($_GET['id']));
$count = $countStmt->fetch();
if ($count['cnt']) {
    $companyId = $_GET['id'];
} else {
    header('Location: ../companies/index.php');
    exit();
}

$companyDataStmt = $db->prepare('SELECT name, manager_name, prefix FROM companies WHERE id=?');
$companyDataStmt->execute(array($companyId));
$companyData = $companyDataStmt->fetch();

$post = $_POST;
$items = [];
$error = [];

if (!empty($post)) {
    $items = convert_half_width($post);
    $error = check_quotation($items);

    if (empty($error)) {
        $_SESSION['new_quotation'] = $items;
        header('Location: check.php');
        exit();
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'rewrite') {
    $items = $_SESSION['new_quotation'];
}

$title = '';
$total = '';
$validityPeriod = '';
$dueDate = '';
$status = '';

if (!empty($items)) {
    $title = $items['title'];
    $total = $items['total'];
    $validityPeriod = $items['validity_period'];
    $dueDate = $items['due_date'];
    $status = $items['status'];
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>見積作成</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="create-page">
        <div class="container">
            <div class="heading">
                <h1>見積作成</h1>
                <a href="index.php?id=<?= $companyId?>" class="btn-back">戻る</a>
            </div>
            <form action="create.php?id=<?= $companyId?>" method="POST">
                <input type="hidden" name="company_id" value=<?= $companyId?>>
                <input type="hidden" name="prefix" value=<?= $companyData['prefix']?>>
                <input type="hidden" name="name" value=<?= $companyData['name']?>>
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
                        <div class="form-wrapper"><p><?= $companyData['name']?></p></div>
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
                                <?php elseif ($status == 9) :?>
                                    <option value="1">下書き</option>
                                    <option value="2">発行済み</option>
                                    <option value="9" selected>破棄</option>
                                <?php else :?>
                                    <option value="">選択してください</option>
                                    <option value="1">下書き</option>
                                    <option value="2">発行済み</option>
                                    <option value="9">破棄</option>
                                <?php endif ?>
                            </select>
                        </div>
                    </div>
                    <?php if (isset($error['status'])) :?>
                        <p class="error"><?= $error['status'] ?></p>
                    <?php endif?>
                </div>
                <input class="btn btn-form" type="submit" value="見積作成">
            </form>
        </div>
    </main>
</body>
</html>