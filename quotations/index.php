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

$countStatement = $db->prepare('SELECT COUNT(*) AS cnt FROM quotations WHERE company_id=?');
$countStatement->bindParam(1, $id, PDO::PARAM_INT);
$countStatement->execute();
$count = $countStatement->fetch();

if (!$count['cnt'] > 0) {
    $quotationExist = false;
} else {
    $quotationExist = true;
    $statement = $db->prepare('SELECT c.name, c.manager_name, q.no, q.title, q.total, q.validity_period, q.due_date, q.status FROM companies c, quotations q WHERE c.id=?');
    $statement->bindParam(1, $id, PDO::PARAM_INT);
    $statement->execute();
    $quotations = $statement->fetchAll();
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
$end = ($page) * 10 - 1;
if ($end > $count['cnt']) {
    $end = $count['cnt'] - 1;
}

$nameStatement = $db->prepare('SELECT name FROM companies WHERE id=?');
$nameStatement->execute(array($id));
$name = $nameStatement->fetch();

if (isset($_GET['order'])) {
    if ($_GET['order'] == 'desc') {
        $_SESSION['desc'] = true;
        $quotations = array_reverse($quotations);
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
    <title>見積一覧</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="list">
        <div class="container">
            <div class="heading">
                <h1>見積一覧</h1>
                <div class="heading-right">
                    <h2><?= h($name['name'])?></h2>
                    <a href="../companies/">会社一覧へ戻る</a>
                </div>
            </div>
            <div class="menu">
                <a href="create.php?id=<?= h($id)?>" class="create">新規作成</a>
                <form action="">
                    <select name="" id="">
                        <option value="1">下書き</option>
                        <option value="2">発行済み</option>
                        <option value="3">破棄</option>
                    </select>
                    <input class="search" type="submit" value="検索">
                </form>
            </div>
            <?php if ($quotationExist) :?>
            <table>
                <tr class="table-title">
                    <?php if ($_SESSION['desc']) :?>
                        <th class="th-id"><a href="index.php?id=<?= h($id) ?>">見積番号</a></th>
                    <?php else :?>
                        <th class="th-id"><a href="index.php?id=<?= h($id) ?>&order=desc">見積番号</a></th>
                    <?php endif ?>
                    <th>見積名</th>
                    <th>担当者名</th>
                    <th>金額</th>
                    <th>見積書有効期限</th>
                    <th>納期</th>
                    <th>状態</th>
                    <th class="link">編集</th>
                    <th class="link">削除</th>
                </tr>
                <?php for ($i = $start; $i <= $end; $i++) :?>
                    <tr>
                        <th><?= h($quotations[$i]['no']) ?></th>
                        <th><?= h($quotations[$i]['title']) ?></th>
                        <th><?= h($quotations[$i]['manager_name']) ?></th>
                        <th><?= number_format(h($quotations[$i]['total'])) . '円'?></th>
                        <th><?= h($quotations[$i]['validity_period']) ?></th>
                        <th><?= h($quotations[$i]['due_date']) ?></th>
                        <?php if ($quotations[$i]['status'] == 1) :?>
                            <th>下書き</th>
                        <?php elseif ($quotations[$i]['status'] == 2) :?>
                            <th>発行済み</th>
                        <?php else :?>
                            <th>破棄</th>
                        <?php endif?>
                        <th class="link"><a href="edit.php?no=<?= h($quotations[$i]['no'])?>">編集</a></th>
                        <th class="link"><a href="delete.php?no=<?= h($quotations[$i]['no'])?>">削除</a></th>
                    </tr>
                <?php endfor ?>
            </table>
            <div class="page-navigation">
                <?php if ($_SESSION['desc']) :?>
                    <?php if ($page <= 1) :?>
                        <a href="index.php?id=<?= h($id) ?>&page=<?= $page +1?>&order=desc" class="next p-nav">次へ<span>&rarr;</span></a>
                    <?php elseif ($page >= $maxPage) :?>
                        <a href="index.php?id=<?= h($id) ?>&page=<?= $page -1?>&order=desc" class="prev p-nav"><span>&larr;</span>前へ</a>
                    <?php else :?>
                        <a href="index.php?id=<?= h($id) ?>&page=<?= $page -1?>&order=desc" class="prev p-nav"><span>&larr;</span>前へ</a>
                        <a href="index.php?id=<?= h($id) ?>&page=<?= $page +1?>&order=desc" class="next p-nav">次へ<span>&rarr;</span></a>
                    <?php endif?>
                <?php else :?>
                    <?php if ($page <= 1) :?>
                        <a href="index.php?id=<?= h($id) ?>&page=<?= $page +1?>" class="next p-nav">次へ<span>&rarr;</span></a>
                    <?php elseif ($page >= $maxPage) :?>
                        <a href="index.php?id=<?= h($id) ?>&page=<?= $page -1?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                    <?php else :?>
                        <a href="index.php?id=<?= h($id) ?>&page=<?= $page -1?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                        <a href="index.php?id=<?= h($id) ?>&page=<?= $page +1?>" class="next p-nav">次へ<span>&rarr;</span></a>
                    <?php endif?>
                <?php endif?>
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