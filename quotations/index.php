<?php
require_once('../databese/dbconnect.php');
require_once('../utils/functions.php');
require_once('../utils/data_per_page.php');
// クエリパラメータのidをバリデーション後受け取る
if (!is_exact_id($_GET['id'])) {
    header('Location: ../companies/index.php');
    exit();
}
$id = $_GET['id'];

// 会社データの取得
$companyDataStmt = $db->prepare('SELECT name, manager_name FROM companies WHERE id=:id');
$companyDataStmt->execute([':id' => $id]);
$companyData = $companyDataStmt->fetch(PDO::FETCH_ASSOC);
if (!$companyData) {
    header('Location: ../companies/index.php');
    exit();
}

$quotationsStmt = $db->prepare('SELECT no, title, total, validity_period, due_date, status FROM quotations WHERE company_id=:company_id AND deleted is NULL');
$quotationsStmt->bindParam(':company_id', $id, PDO::PARAM_INT);
$quotationsStmt->execute();
$quotations = $quotationsStmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($quotations) ?? 0;

$quotationExist = false;
// 見積データが存在する場合の処理
if ($count) {
    $quotationExist = true;
    // 最大ページ = データの総数 / 1ページの表示データ数 小数点以下切り上げる
    $maxPage = ceil($count / DATA_PER_PAGE);
    // ページクエリのバリデーション
    if (isset($_GET['page'])) {
        if (!preg_match('/^[1-9]+\d*$/', $_GET['page'])) {
            header("Location: index.php?id={$id}");
            exit();
        }

        if ($_GET['page'] > $maxPage) {
            header("Location: index.php?id={$id}&page={$maxPage}");
            exit();
        }
    }
    $page = $_GET['page'] ?? 1;
    // 各ページの先頭のデータのインデックス
    $start = ($page - 1) * DATA_PER_PAGE;
    // 各ページの最後のデータのインデックス
    $end = $start + (DATA_PER_PAGE - 1);
    // 見積データの最大数より$endの数が大きかった場合$endの数を最後のデータのインデックスにする
    if ($end >= $count) {
        $end = $count - 1;
    }

    $showButton = $maxPage > 1 ? true : false;

    $desc = false;
    if (isset($_GET['order'])) {
        if (!$_GET['order'] == 'desc') {
            header('Location: index.php');
            exit();
        }
        // noを基準に配列を降順にする処理
        $desc = true;
        // $descQuotations(並び替え後の配列)に$quotations(見積データ)の最初のデータを挿入
        $descQuotations = [$quotations[0]];
        for ($x = 1; $x < count($quotations); $x++) {
            // $descQuotationsの先頭のnoの値より大きければ先頭に挿入
            if ($descQuotations[0]['no'] <= $quotations[$x]['no']) {
                array_unshift($descQuotations, $quotations[$x]);
            // $descQuotationsの最後のnoの値より小さければ末尾に挿入
            } elseif (end($descQuotations)['no'] > $quotations[$x]['no']) {
                array_push($descQuotations, $quotations[$x]);
            } else {
                // descQuotationsの２番目のデータから順番に比べ$quotationsよりも小さい値を見つけたらそのデータの前に挿入
                for ($y = 1; $y < count($descQuotations); $y++) {
                    if ($descQuotations[$y]['no'] <= $quotations[$x]['no']) {
                        array_splice($descQuotations, $y, 0, [$quotations[$x]]);
                        break;
                    }
                }
            }
        }
        $quotations = $descQuotations;
    }
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
                <?php if ($quotationExist) :?>
                    <form action="search.php" method="GET">
                        <input type="hidden" name="id" value=<?= $id?>>
                        <select name="status" id="">
                            <option value="">全て</option>
                            <option value="下書き">下書き</option>
                            <option value="発行済み">発行済み</option>
                            <option value="破棄">破棄</option>
                        </select>
                        <input class="btn-search" type="submit" value="検索">
                    </form>
                <?php endif?>
            </div>
            <?php if ($quotationExist) :?>
            <div class="table-wrapper">
                <table>
                    <tr class="title list-title">
                        <?php if ($count == 1) :?>
                            <th class="order q-id">見積番号</th>
                        <?php else :?>
                            <?php if ($desc) :?>
                                <th class="order q-id"><a href="index.php?id=<?= h($id) ?>">見積番号  ▼</a></th>
                            <?php else :?>
                                <th class="order q-id"><a href="index.php?id=<?= h($id) ?>&order=desc">見積番号  ▲</a></th>
                            <?php endif ?>
                        <?php endif?>
                        <th class="q-name">見積名</th>
                        <th class="q-manager">担当者名</th>
                        <th class="q-total">金額</th>
                        <th class="q-date">見積書有効期限</th>
                        <th class="q-date">納期</th>
                        <th class="q-status">状態</th>
                        <th class="link">編集</th>
                        <th class="link">削除</th>
                    </tr>
                    <?php for ($i = $start; $i <= $end; $i++) :?>
                        <tr>
                            <td class="q-no"><?= h($quotations[$i]['no']) ?></td>
                            <td class="q-name"><?= h($quotations[$i]['title']) ?></td>
                            <td class="q-manager"><?= h($companyData['manager_name']) ?></td>
                            <td class="q-total"><?= number_format(h($quotations[$i]['total'])) . '円'?></td>
                            <td class="q-date"><?= h($quotations[$i]['validity_period']) ?></td>
                            <td class="q-date"><?= h($quotations[$i]['due_date']) ?></td>
                            <?php if ($quotations[$i]['status'] == 1) :?>
                                <td class="q-status">下書き</td>
                            <?php elseif ($quotations[$i]['status'] == 2) :?>
                                <td class="q-status">発行済み</td>
                            <?php else :?>
                                <td class="q-status">破棄</td>
                            <?php endif?>
                            <td class="link"><a href="edit.php?no=<?= h($quotations[$i]['no'])?>">編集</a></td>
                            <form action="delete.php" method="POST" onsubmit="return confirm_delete()">
                                <input type="hidden" name="no" value=<?= h($quotations[$i]['no'])?>>
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
                            <a href="index.php?id=<?= h($id) ?>&page=<?= $page +1?>&order=desc" class="next p-nav">次へ<span>&rarr;</span></a>
                        <?php elseif ($page >= $maxPage) :?>
                            <a href="index.php?id=<?= h($id) ?>&page=<?= $page -1?>&order=desc" class="prev p-nav"><span>&larr;</span>前へ</a>
                        <?php elseif ($page == $maxPage) :?>
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
                <?php endif?>
            </div>
            <?php else :?>
                <?php include("../no_data.php") ?>
            <?php endif ?>
        </div>
    </main>
</body>
</html>