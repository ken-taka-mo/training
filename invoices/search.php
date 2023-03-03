<?php
require_once('../databese/dbconnect.php');
require_once('../utils/functions.php');
require_once('../utils/data_per_page.php');

// パラメータのバリデーション
if (!is_exact_id($_GET['id'])) {
    header('Location: ../companies/index.php');
    exit();
}
$companyId = $_GET['id'];

// 会社名・担当者名を取得　なければ会社一覧ページに遷移
$companyDataStmt = $db->prepare('SELECT name, manager_name FROM companies WHERE id=:id');
$companyDataStmt->execute([':id' => $companyId]);
$companyData = $companyDataStmt->fetch(PDO::FETCH_ASSOC);
if (!$companyData) {
    header('Location: ../companies/index.php');
    exit();
}

// パラメータmin,maxのバリデーション
$min = mb_convert_kana($_GET['min'], 'n', 'UTF-8');
$max = mb_convert_kana($_GET['max'], 'n', 'UTF-8');
if (!preg_match('/^[0-9]+$/', $min) || !preg_match('/^[0-9]+$/', $max)) {
    header("Location: index.php?id={$companyId}");
    exit();
}

// 検索範囲内の金額の見積データを全て取得
$invoicesStmt = $db->prepare('SELECT no, title, total, payment_deadline, date_of_issue, quotation_no, status FROM invoices WHERE company_id=:company_id AND total>=:min AND total<=:max AND deleted is NULL');
$invoicesStmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
$invoicesStmt->bindParam(':min', $min, PDO::PARAM_INT);
$invoicesStmt->bindParam(':max', $max, PDO::PARAM_INT);
$invoicesStmt->execute();
$invoices = $invoicesStmt->fetchAll(PDO::FETCH_ASSOC);
// 見積データがいくつあるか
$invoicesCount = count($invoices) ?? 0;

// 見積データが１つ以上ある場合の処理
if ($invoicesCount) {
    // 最大ページ = データの総数 / 1ページの表示データ数 小数点以下切り上げる
    $maxPage = ceil($invoicesCount / DATA_PER_PAGE);
    // パラメータ(page)のバリデーション
    if (isset($_GET['page'])) {
        if (!preg_match('/^[0-9]+$/', $_GET['page']) || preg_match('/^[0]*$/', $_GET['page'])) {
            header("Location: index.php?id={$companyId}&min={$min}&max={$max}");
            exit();
        }
        if ($_GET['page'] > $maxPage) {
            header("Location: index.php?id={$companyId}&page={$maxPage}&min={$min}&max={$max}");
            exit();
        }
    }
    $page = $_GET['page'] ?? 1;
    $start = ($page - 1) * DATA_PER_PAGE;
    $end = $start + (DATA_PER_PAGE - 1);
    if ($end >= $invoicesCount) {
        $end = $invoicesCount - 1;
    }
    $showButton = $maxPage > 1 ? true : false;

    $desc = false;
    if (isset($_GET['order'])) {
        if (!$_GET['order'] == 'desc') {
            header("search.php?id={$companyId}&min={$min}&max={$max}");
            exit();
        }
        $desc = true;
        $invoices = array_reverse($invoices);
    }
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
                <a href="create.php?id=<?= $companyId ?>" class="btn">新規登録</a>
                <form action="search.php" method="GET" class="search-total">
                    <input type="hidden" name="id" value=<?= $companyId?>>
                    <span>金額検索(半角)</span>
                    <input type="text" class="search-total" name="min" maxlength="9" placeholder="下限" value=<?= $min?>>
                    <span>~</span>
                    <input type="text" class="search-total" name="max" maxlength="9" placeholder="上限" value=<?= $max?>>
                    <input class="btn-search" type="submit" value="検索">
                    <a class="btn-back clear" href="index.php?id=<?= $companyId?>">条件クリア</a>
                </form>
            </div>
            <?php if ($invoicesCount) :?>
            <div class="table-wrapper">
                <table>
                    <tr class="title list-title">
                        <?php if ($invoicesCount == 1) :?>
                            <th class="order i-id">請求番号</th>
                        <?php else :?>
                            <?php if ($desc) :?>
                                <th class="order i-id"><a href="search.php?id=<?= h($companyId) ?>&min=<?= $min?>&max=<?= $max?>">請求番号  ▼</a></th>
                            <?php else :?>
                                <th class="order i-id"><a href="search.php?id=<?= h($companyId) ?>&min=<?= $min?>&max=<?= $max?>&order=desc">請求番号  ▲</a></th>
                            <?php endif ?>
                        <?php endif?>
                        <th class="i-name">請求名</th>
                        <th class="i-manager">担当者名</th>
                        <th class="i-total">金額</th>
                        <th class="i-date">支払期限</th>
                        <th class="i-date">請求日</th>
                        <th class="i-q-no">見積番号</th>
                        <th class="i-status">状態</th>
                        <th class="link">編集</th>
                        <th class="link">削除</th>
                    </tr>
                    <?php for ($i = $start; $i <= $end; $i++) :?>
                        <tr>
                            <td class="i-id"><?= h($invoices[$i]['no']) ?></td>
                            <td class="i-title"><?= h($invoices[$i]['title']) ?></td>
                            <td class="i-manager"><?= h($companyData['manager_name']) ?></td>
                            <td class="i-total"><?= number_format(h($invoices[$i]['total'])) . '円'?></td>
                            <td class="i-date"><?= h($invoices[$i]['payment_deadline']) ?></td>
                            <td class="i-date"><?= h($invoices[$i]['date_of_issue']) ?></td>
                            <td class="i-q-date"><?= h($invoices[$i]['quotation_no'])?></td>
                            <?php if ($invoices[$i]['status'] == 1) :?>
                                <td class="i-status">下書き</td>
                            <?php elseif ($invoices[$i]['status'] == 2) :?>
                                <td class="i-status">発行済み</td>
                            <?php else :?>
                                <td class="i-status">破棄</td>
                            <?php endif?>
                            <td class="link"><a href="edit.php?no=<?= h($invoices[$i]['no'])?>">編集</a></td>
                            <form action="delete.php" method="POST" onsubmit="return confirm_delete()">
                                <input type="hidden" name="min" value=<?= $min?>>
                                <input type="hidden" name="max" value=<?= $max?>>
                                <input type="hidden" name="no" value=<?= h($invoices[$i]['no'])?>>
                                <td class="link btn-delete"><input type="submit" value="削除" ></td>
                            </form>
                        </tr>
                    <?php endfor ?>
                </table>
            </div>
            <div class="page-navigation">
                <?php if ($showButton) :?>
                    <?php if ($desc) :?>
                        <?php if ($page <= 1) :?>
                            <a href="search.php?id=<?= h($companyId) ?>&page=<?= $page +1?>&min=<?= $min?>&max=<?= $max?>&order=desc" class="next p-nav">次へ<span>&rarr;</span></a>
                        <?php elseif ($page >= $maxPage) :?>
                            <a href="search.php?id=<?= h($companyId) ?>&page=<?= $page -1?>&min=<?= $min?>&max=<?= $max?>&order=desc" class="prev p-nav"><span>&larr;</span>前へ</a>
                        <?php elseif ($page == $maxPage) :?>
                        <?php else :?>
                            <a href="search.php?id=<?= h($companyId) ?>&page=<?= $page -1?>&min=<?= $min?>&max=<?= $max?>&order=desc" class="prev p-nav"><span>&larr;</span>前へ</a>
                            <a href="search.php?id=<?= h($companyId) ?>&page=<?= $page +1?>&min=<?= $min?>&max=<?= $max?>&order=desc" class="next p-nav">次へ<span>&rarr;</span></a>
                        <?php endif?>
                    <?php else :?>
                        <?php if ($page <= 1) :?>
                            <a href="search.php?id=<?= h($companyId) ?>&page=<?= $page +1?>&min=<?= $min?>&max=<?= $max?>" class="next p-nav">次へ<span>&rarr;</span></a>
                        <?php elseif ($page >= $maxPage) :?>
                            <a href="search.php?id=<?= h($companyId) ?>&page=<?= $page -1?>&min=<?= $min?>&max=<?= $max?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                        <?php else :?>
                            <a href="search.php?id=<?= h($companyId) ?>&page=<?= $page -1?>&min=<?= $min?>&max=<?= $max?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                            <a href="search.php?id=<?= h($companyId) ?>&page=<?= $page +1?>&min=<?= $min?>&max=<?= $max?>" class="next p-nav">次へ<span>&rarr;</span></a>
                        <?php endif?>
                    <?php endif?>
                <?php endif?>
            </div>
            <?php else :?>
                <?php include("../common/no_data.php") ?>
            <?php endif ?>
        </div>
    </main>
</body>
</html>