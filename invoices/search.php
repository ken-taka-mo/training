<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');

if (empty($_GET['id'])) {
    header('Location: ../companies/index.php');
    exit();
}

if (!preg_match('/^[0-9]+$/', $_GET['id']) || preg_match('/^[0]*$/', $_GET['id'])) {
    header('Location: ../companies/index.php');
    exit();
}
$id = $_GET['id'];
$min = mb_convert_kana($_GET['min'], 'n', 'UTF-8');
$max = mb_convert_kana($_GET['max'], 'n', 'UTF-8');

if (!preg_match('/^[0-9]+$/', $min) || !preg_match('/^[0-9]+$/', $max)) {
    header("Location: index.php?id={$id}");
    exit();
}

$countStmt = $db->prepare('SELECT COUNT(*) AS cnt FROM invoices WHERE company_id=? AND total>=? AND total<=? AND deleted is NULL');
$countStmt->bindParam(1, $id, PDO::PARAM_INT);
$countStmt->bindParam(2, $min, PDO::PARAM_INT);
$countStmt->bindParam(3, $max, PDO::PARAM_INT);
$countStmt->execute();
$count = $countStmt->fetch();

if (!$count['cnt'] > 0) {
    $invoicesExist = false;
} else {
    $invoicesExist = true;
    $listStmt = $db->prepare('SELECT no, title, total, payment_deadline, date_of_issue, quotation_no, status FROM invoices WHERE company_id=? AND total>=? AND total<=? AND deleted is NULL');
    $listStmt->bindParam(1, $id, PDO::PARAM_INT);
    $listStmt->bindParam(2, $min, PDO::PARAM_INT);
    $listStmt->bindParam(3, $max, PDO::PARAM_INT);
    $listStmt->execute();
    $invoices = $listStmt->fetchAll();
}

$page = 1;
$maxPage = ceil($count['cnt'] / 10);
if ($maxPage == 0) {
    $maxPage = 1;
}

if (isset($_GET['page'])) {
    if (!preg_match('/^[0-9]+$/', $_GET['page']) || preg_match('/^[0]*$/', $_GET['page'])) {
        header("Location: index.php?id={$id}&min={$min}&max={$max}");
        exit();
    }

    if ($_GET['page'] > $maxPage) {
        header("Location: index.php?id={$id}&page={$maxPage}&min={$min}&max={$max}");
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
if ($maxPage > 1) {
    $showButton = true;
}

$companyDataStmt = $db->prepare('SELECT name, manager_name FROM companies WHERE id=?');
$companyDataStmt->execute(array($id));
$companyData = $companyDataStmt->fetch();

if (isset($_GET['order'])) {
    if ($_GET['order'] == 'desc') {
        $desc = true;
        $invoices = array_reverse($invoices);
    } else {
        header("search.php?id={$id}&min={$min}&max={$max}");
        exit();
    }
} else {
    $desc = false;
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
    <main class="list">
        <div class="container">
            <div class="heading">
                <h1>請求一覧</h1>
                <div class="heading-right">
                    <h2><?= $companyData['name']?></h2>
                    <a class="btn-back" href="../companies/">会社一覧へ戻る</a>
                </div>
            </div>
            <div class="menu">
                <a href="create.php?id=<?= $id ?>" class="btn">新規登録</a>
                <div>
                    <form action="search.php" method="GET">
                        <input type="hidden" name="id" value=<?= $id?>>
                        <span>金額検索</span>
                        <input type="text" class="search-total" name="min"placeholder="下限" value=<?= $min?>>
                        <span>~</span>
                        <input type="text" class="search-total" name="max" placeholder="上限" value=<?= $max?>>
                        <input class="btn-search" type="submit" value="検索">
                        <a class="btn-back" href="index.php?id=<?= $id?>">条件クリア</a>
                    </form>
                </div>
            </div>
            <?php if ($invoicesExist) :?>
            <table>
                <tr class="title list-title">
                    <?php if ($desc) :?>
                        <th class="order th-i-id"><a href="search.php?id=<?= h($id) ?>&min=<?= $min?>&max=<?= $max?>">請求番号</a></th>
                    <?php else :?>
                        <th class="order th-i-id"><a href="search.php?id=<?= h($id) ?>&min=<?= $min?>&max=<?= $max?>&order=desc">請求番号</a></th>
                    <?php endif ?>
                    <th class="th-name">請求名</th>
                    <th class="manager">担当者名</th>
                    <th class="th-total">金額</th>
                    <th class="th-date">支払期限</th>
                    <th class="th-date">請求日</th>
                    <th class="th-no">見積番号</th>
                    <th class="th-status">状態</th>
                    <th class="link">編集</th>
                    <th class="link">削除</th>
                </tr>
                <?php for ($i = $start; $i <= $end; $i++) :?>
                    <tr>
                        <th><?= h($invoices[$i]['no']) ?></th>
                        <th><?= h($invoices[$i]['title']) ?></th>
                        <th><?= h($companyData['manager_name']) ?></th>
                        <th><?= number_format(h($invoices[$i]['total'])) . '円'?></th>
                        <th><?= h($invoices[$i]['payment_deadline']) ?></th>
                        <th><?= h($invoices[$i]['date_of_issue']) ?></th>
                        <th><?= h($invoices[$i]['quotation_no'])?></th>
                        <?php if ($invoices[$i]['status'] == 1) :?>
                            <th>下書き</th>
                        <?php elseif ($invoices[$i]['status'] == 2) :?>
                            <th>発行済み</th>
                        <?php else :?>
                            <th>破棄</th>
                        <?php endif?>
                        <th class="link"><a href="edit.php?no=<?= h($invoices[$i]['no'])?>">編集</a></th>
                        <form action="delete.php" method="POST" onsubmit="return confirmDelete()">
                            <input type="hidden" name="min" value=<?= $min?>>
                            <input type="hidden" name="max" value=<?= $max?>>
                            <input type="hidden" name="no" value=<?= h($invoices[$i]['no'])?>>
                            <th class="link btn-delete"><input type="submit" value="削除" ></th>
                        </form>
                    </tr>
                <?php endfor ?>
            </table>
            <div class="page-navigation">
                <?php if ($showButton) :?>
                    <?php if ($desc) :?>
                        <?php if ($page <= 1) :?>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page +1?>&min=<?= $min?>&max=<?= $max?>&order=desc" class="next p-nav">次へ<span>&rarr;</span></a>
                        <?php elseif ($page >= $maxPage) :?>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page -1?>&min=<?= $min?>&max=<?= $max?>&order=desc" class="prev p-nav"><span>&larr;</span>前へ</a>
                        <?php elseif ($page == $maxPage) :?>
                        <?php else :?>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page -1?>&min=<?= $min?>&max=<?= $max?>&order=desc" class="prev p-nav"><span>&larr;</span>前へ</a>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page +1?>&min=<?= $min?>&max=<?= $max?>&order=desc" class="next p-nav">次へ<span>&rarr;</span></a>
                        <?php endif?>
                    <?php else :?>
                        <?php if ($page <= 1) :?>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page +1?>&min=<?= $min?>&max=<?= $max?>" class="next p-nav">次へ<span>&rarr;</span></a>
                        <?php elseif ($page >= $maxPage) :?>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page -1?>&min=<?= $min?>&max=<?= $max?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                        <?php else :?>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page -1?>&min=<?= $min?>&max=<?= $max?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page +1?>&min=<?= $min?>&max=<?= $max?>" class="next p-nav">次へ<span>&rarr;</span></a>
                        <?php endif?>
                    <?php endif?>
                <?php endif?>
            </div>
            <?php else :?>
            <table>
            <tr class="title list-title">
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