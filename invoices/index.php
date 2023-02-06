<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');
session_start();

if (empty($_GET['id'])) {
    header('Location: ../companies/index.php');
    exit();
}

if (!preg_match('/^[0-9]+$/', $_GET['id']) || preg_match('/^[0]*$/', $_GET['id'])) {
    header('Location: ../companies/index.php');
    exit();
}

$id = $_GET['id'];

$countStatement = $db->prepare('SELECT COUNT(*) AS cnt FROM invoices WHERE company_id=? AND deleted is NULL');
$countStatement->bindParam(1, $id, PDO::PARAM_INT);
$countStatement->execute();
$count = $countStatement->fetch();
if (!$count['cnt'] > 0) {
    $invoicesExist = false;
} else {
    $invoicesExist = true;
    $statement = $db->prepare('SELECT no, title, total, payment_deadline, date_of_issue, quotation_no, status FROM invoices WHERE company_id=? AND deleted is NULL');
    $statement->bindParam(1, $id, PDO::PARAM_INT);
    $statement->execute();
    $invoices = $statement->fetchAll();
}

$page = 1;
$maxPage = ceil($count['cnt'] / 10);
if ($maxPage == 0) {
    $maxPage = 1;
}

if (isset($_GET['page'])) {
    if (!preg_match('/^[0-9]+$/', $_GET['page']) || preg_match('/^[0]*$/', $_GET['page'])) {
        header("Location: index.php?id={$id}");
        exit();
    }

    if ($_GET['page'] > $maxPage) {
        header("Location: index.php?id={$id}&page={$maxPage}");
        exit();
    } else {
        $page = $_GET['page'];
        $page = max($page, 1);
        $page = min($page, $maxPage);
    }
}

$start = ($page - 1) * 10;
$end = $start + 9;
if ($end >= $count['cnt']) {
    $end = $count['cnt'] - 1;
}

$showButton = false;
if (!$end <= 9 && $maxPage > 1) {
    $showButton = true;
}

$companyStatement = $db->prepare('SELECT name, manager_name FROM companies WHERE id=?');
$companyStatement->execute(array($id));
$companyData = $companyStatement->fetch();

if (isset($_GET['order'])) {
    if ($_GET['order'] == 'desc') {
        $_SESSION['desc'] = true;
        $invoices = array_reverse($invoices);
    } else {
        $_SESSION['desc'] = false;
    }
} else {
    $_SESSION['desc'] = false;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>請求一覧</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="list li-company">
        <div class="container">
            <div class="heading">
                <h1>請求一覧</h1>
                <div class="heading-right">
                    <h2><?= $companyData['name']?></h2>
                    <a href="../companies/">会社一覧へ戻る</a>
                </div>
            </div>
            <div class="menu">
                <a href="create.php?id=<?= $id ?>" class="create">新規登録</a>
                <form action="">
                    <input type="text">
                    <input class="search" type="submit" value="検索">
                </form>
            </div>
            <?php if ($invoicesExist) :?>
            <table>
                <tr class="table-title">
                    <th>請求番号</th>
                    <th>請求名</th>
                    <th>担当者名</th>
                    <th>金額</th>
                    <th>支払期限</th>
                    <th>請求日</th>
                    <th>見積番号</th>
                    <th>状態</th>
                    <th class="link">編集</th>
                    <th class="link">削除</th>
                </tr>
                <?php for ($i = $start; $i <= $end; $i++) :?>
                    <tr>
                        <th><?= h($invoices['no']) ?></th>
                        <th><?= h($invoices['title']) ?></th>
                        <th><?= h($companyData['manager_name']) ?></th>
                        <th><?= number_format(h($invoices['total'])) . '円'?></th>
                        <th><?= h($invoices['payment_deadline']) ?></th>
                        <th><?= h($invoices['date_of_issue']) ?></th>
                        <th><?= h($invoices['quotation_no'])?></th>
                        <?php if ($invoices['status'] == 1) :?>
                            <th>下書き</th>
                        <?php elseif ($invoices['status'] == 2) :?>
                            <th>発行済み</th>
                        <?php else :?>
                            <th>破棄</th>
                        <?php endif?>
                        <th class="link"><a href="edit.php?no=<?= h($invoices['no'])?>">編集</a></th>
                        <th class="link"><a href="delete.php?no=<?= h($invoices['no'])?>">削除</a></th>
                    </tr>
                <?php endfor ?>
            </table>
            <div class="page-navigation">
                <a href="" class="prev p-nav"><span>&larr;</span>前へ</a>
                <a href="" class="next p-nav">次へ<span>&rarr;</span></a>
            </div>
            <?php else :?>
            <table>
            <tr class="table-title">
                <th>見積番号</th>
            </tr>
            <tr>
                <th style="">見積はありません</th>
            </tr>
            </table>
            <?php endif ?>
        </div>
    </main>
</body>
</html>