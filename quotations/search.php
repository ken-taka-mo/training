<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');

if (empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];
$status = $_GET['status'];

switch ($status) {
    case "下書き":
        $sqlStatus = 1;
        break;
    case "発行済み":
        $sqlStatus = 2;
        break;
    case "破棄":
        $sqlStatus = 9;
        break;
    default:
        $sqlStatus = "";
        break;
}

if (!preg_match('/^[129]$/', $sqlStatus)) {
    header("Location: index.php?id={$id}");
    exit();
}

$companyStatement = $db->prepare('SELECT name, manager_name FROM companies WHERE id=?');
$companyStatement->execute(array($id));
$companyData = $companyStatement->fetch();

$countStatement = $db->prepare('SELECT COUNT(*) AS cnt FROM quotations WHERE company_id=? AND status=? AND deleted is NULL');
$countStatement->bindParam(1, $id, PDO::PARAM_INT);
$countStatement->bindParam(2, $sqlStatus, PDO::PARAM_INT);
$countStatement->execute();
$count = $countStatement->fetch();

if ($count['cnt'] < 1) {
    $quotationExist = false;
} else {
    $quotationExist = true;
    $quotationStatement = $db->prepare('SELECT no, title, total, validity_period, due_date, status FROM quotations WHERE company_id=? AND status=? AND deleted is NULL');
    $quotationStatement->bindParam(1, $id, PDO::PARAM_INT);
    $quotationStatement->bindParam(2, $sqlStatus, PDO::PARAM_INT);
    $quotationStatement->execute();
    $quotations = $quotationStatement->fetchAll();
}

$page = 1;
$maxPage = ceil($count['cnt'] / 10);
if ($maxPage == 0) {
    $maxPage = 1;
}

if (isset($_GET['page'])) {
    if (!preg_match('/^[0-9]+$/', $_GET['page']) || preg_match('/^[0]*$/', $_GET['page'])) {
        header("Location: index.php?id={$id}&status={$status}");
        exit();
    }

    if ($_GET['page'] > $maxPage) {
        header("Location: index.php?id={$id}&page={$maxPage}&status={$status}");
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

if (isset($_GET['order'])) {
    if ($_GET['order'] == 'desc') {
        $desc = true;
        $quotations = array_reverse($quotations);
    } else {
        header("Location: search.php?id={$id}&status={$status}");
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
    <title>見積一覧</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="list">
        <div class="container">
            <div class="heading">
                <h1>見積一覧</h1>
                <div class="heading-right">
                    <h2><?= h($companyData['name'])?></h2>
                    <a class="btn-back" href="../companies/">会社一覧へ戻る</a>
                </div>
            </div>
            <div class="menu">
                <a href="create.php?id=<?= h($id)?>" class="btn">新規作成</a>
                <form action="search.php" method="GET">
                    <input type="hidden" name="id" value=<?= $id?>>
                    <select name="status" id="">
                        <option value="">全て</option>
                        <?php if ($status == "下書き") :?>
                            <option value="下書き" selected>下書き</option>
                            <option value="発行済み">発行済み</option>
                            <option value="破棄">破棄</option>
                        <?php elseif ($status == "発行済み") :?>
                            <option value="下書き">下書き</option>
                            <option value="発行済み" selected>発行済み</option>
                            <option value="破棄">破棄</option>
                        <?php elseif ($status == "破棄") :?>
                            <option value="下書き">下書き</option>
                            <option value="発行済み">発行済み</option>
                            <option value="破棄" selected>破棄</option>
                        <?php endif?>
                    </select>
                    <input class="btn-search" type="submit" value="検索">
                </form>
            </div>
            <?php if ($quotationExist) :?>
            <table>
                <tr class="title list-title">
                    <?php if ($desc) :?>
                        <th class="order th-q-id"><a href="search.php?id=<?= h($id) ?>&status=<?= $status?>">見積番号</a></th>
                    <?php else :?>
                        <th class="order th-q-id"><a href="search.php?id=<?= h($id) ?>&status=<?= $status?>&order=desc">見積番号</a></th>
                    <?php endif ?>
                    <th class="th-q-name">見積名</th>
                    <th class="th-manager">担当者名</th>
                    <th class="th-total">金額</th>
                    <th class="th-validity">見積書有効期限</th>
                    <th class="th-due">納期</th>
                    <th class="th-status">状態</th>
                    <th class="link">編集</th>
                    <th class="link">削除</th>
                </tr>
                <?php for ($i = $start; $i <= $end; $i++) :?>
                    <tr>
                        <th><?= h($quotations[$i]['no']) ?></th>
                        <th><?= h($quotations[$i]['title']) ?></th>
                        <th><?= h($companyData['manager_name']) ?></th>
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
                        <form action="delete.php" method="POST" onsubmit="return confirmDelete()">
                            <input type="hidden" name="status" value=<?= $status ?>>
                            <input type="hidden" name="no" value=<?= h($quotations[$i]['no'])?>>
                            <th class="link btn-delete"><input type="submit" value="削除" ></th>
                        </form>
                    </tr>
                <?php endfor ?>
            </table>
            <div class="page-navigation">
                <?php if ($showButton) :?>
                    <?php if ($desc) :?>
                        <?php if ($page <= 1) :?>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page +1?>&status=<?= $status?>&order=desc" class="next p-nav">次へ<span>&rarr;</span></a>
                        <?php elseif ($page >= $maxPage) :?>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page -1?>&status=<?= $status?>&order=desc" class="prev p-nav"><span>&larr;</span>前へ</a>
                        <?php elseif ($page == $maxPage) :?>
                        <?php else :?>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page -1?>&status=<?= $status?>&order=desc" class="prev p-nav"><span>&larr;</span>前へ</a>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page +1?>&status=<?= $status?>&order=desc" class="next p-nav">次へ<span>&rarr;</span></a>
                        <?php endif?>
                    <?php else :?>
                        <?php if ($page <= 1) :?>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page +1?>&status=<?= $status?>" class="next p-nav">次へ<span>&rarr;</span></a>
                        <?php elseif ($page >= $maxPage) :?>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page -1?>&status=<?= $status?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                        <?php else :?>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page -1?>&status=<?= $status?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                            <a href="search.php?id=<?= h($id) ?>&page=<?= $page +1?>&status=<?= $status?>" class="next p-nav">次へ<span>&rarr;</span></a>
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