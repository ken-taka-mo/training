<?php
require_once('../databese/dbconnect.php');
require_once('../utils/functions.php');
require_once('../utils/validation.php');
require_once('../config/data_per_page.php');

$id = $_GET['id'];
$status = $_GET['status'];
// クエリパラメータのバリデーション
if (!is_exact_id($id)) {
    header('Location: ../companies/index.php');
    exit();
}
// 画面上の表示の値をクエリ文用に変換
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
        header("Location: index.php?id={$id}");
        exit();
        break;
}

// 会社名、担当者名を取得
$companyDataStmt = $db->prepare('SELECT name, manager_name FROM companies WHERE id=:id');
$companyDataStmt->execute([':id' => $id]);
$companyData = $companyDataStmt->fetch(PDO::FETCH_ASSOC);
if (!$companyData) {
    header('Location: ../companies/index.php');
    exit();
}

// 指定の状態に当てはまる見積データを取得
$quotationStmt = $db->prepare('SELECT no, title, total, validity_period, due_date, status FROM quotations WHERE company_id=:company_id AND status=:status AND deleted is NULL');
$quotationStmt->bindParam(':company_id', $id, PDO::PARAM_INT);
$quotationStmt->bindParam(':status', $sqlStatus, PDO::PARAM_INT);
$quotationStmt->execute();
$quotations = $quotationStmt->fetchAll(PDO::FETCH_ASSOC);
// データ数を取得なければ0を代入
$count = count($quotations) ?? 0;

$quotationExist = false;
if ($count) {
    $quotationExist = true;
    // 最大ページ = データの総数 / 1ページの表示データ数 小数点以下切り上げる
    $maxPage = ceil($count / DATA_PER_PAGE);

    if (isset($_GET['page'])) {
        if (!preg_match('/^[1-9]+\d*$/', $_GET['page'])) {
            header("Location: index.php?id={$id}&status={$status}");
            exit();
        }

        if ($_GET['page'] > $maxPage) {
            header("Location: index.php?id={$id}&page={$maxPage}&status={$status}");
            exit();
        }
    }

    $page = $_GET['page'] ?? 1;
    $start = ($page - 1) * DATA_PER_PAGE;
    $end = $start + (DATA_PER_PAGE - 1);
    if ($end >= $count) {
        $end = $count - 1;
    }
    $showButton = $maxPage > 1 ? true : false;

    $desc = false;
    if (isset($_GET['order'])) {
        if (!$_GET['order'] == 'desc') {
            header("Location: search.php?id={$id}&status={$status}");
            exit();
        }
        $desc = true;
        $quotations = array_reverse($quotations);
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
                                <input type="hidden" name="status" value=<?= $status ?>>
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
                <?php include("../common/no_data.php") ?>
            <?php endif ?>
        </div>
    </main>
</body>
</html>