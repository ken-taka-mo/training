<?php
require_once('../databese/dbconnect.php');
require_once('../utils/functions.php');
require_once('../utils/prefectures.php');
require_once('../config/data_per_page.php');

if (!isset($_GET['name']) || preg_match('/^[\s]*$/', $_GET['name'])) {
    header('Location: index.php');
    exit();
}

// 全角数字を半角数字に変換後getNameに代入
$getName = mb_convert_kana($_GET['name'], "n");
// sql文用にgetNameを変換
$keyword = "%{$getName}%";
// 検索ワードを含んだ会社名を持つデータ数を求める
$hasKeywordcounts = $db->prepare('SELECT COUNT(*) AS cnt FROM companies WHERE deleted is NULL AND name LIKE :keyword ');
$hasKeywordcounts->execute([':keyword' => $keyword]);
$companyCount = $hasKeywordcounts->fetch(PDO::FETCH_ASSOC);

$companyExist = false;
if ($companyCount['cnt']) {
    $companyExist = true;
    $maxPage = ceil($companyCount['cnt'] / DATA_PER_PAGE);
    // pageクエリのバリデーション
    if (isset($_GET['page'])) {
        if (!preg_match('/^[1-9]+\d*$/', $_GET['page'])) {
            header('Location: serach.php?name=' . h($getName));
            exit();
        }
        if ($_GET['page'] > $maxPage) {
            header('Location: search.php?name=' . h($getName) . '&page=' . h($maxPage));
            exit();
        }
    }

    $page = $_GET['page'] ?? 1;
    $start = ($page - 1) * DATA_PER_PAGE;
    $showButton = $maxPage > 1 ? true : false;

    $desc = false;
    $companiesStmt = $db->prepare('SELECT id, name, manager_name, phone_number, postal_code, prefecture_code, address, mail_address FROM companies WHERE deleted is NULL AND name LIKE :keyword LIMIT :start,10');
    // 降順切り替えの処理
    if (isset($_GET['order'])) {
        if (!$_GET['order'] == 'desc') {
            header('Location: search.php?name=' . h($getName));
            exit();
        }
        $desc = true;
        $companiesStmt = $db->prepare('SELECT id, name, manager_name, phone_number, postal_code, prefecture_code, address, mail_address FROM companies WHERE deleted is NULL AND name LIKE :keyword ORDER BY id DESC LIMIT :start,10');
    }
    // 会社データ取得
    $companiesStmt->bindParam(':keyword', $keyword);
    $companiesStmt->bindParam(':start', $start, PDO::PARAM_INT);
    $companiesStmt->execute();
    $companies = $companiesStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会社一覧</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="list">
        <div class="container">
            <div class="heading">
                <h1>会社一覧</h1>
            </div>
            <div class="menu">
                <a href="register.php" class="btn">新規登録</a>
                <form action="search.php" method="GET">
                    <input type="text" class="search-form" name="name" value=<?= h($getName)?>>
                    <input class="btn-search" type="submit" value="検索">
                    <a class="btn-back clear" href="index.php">条件クリア</a>
                </form>
            </div>
            <?php if ($companyExist) :?>
                <div class="table-wrapper">
                    <table>
                        <tr class="list-title title">
                            <?php if ($companyCount['cnt'] == 1) :?>
                                <th class="order t-id">会社番号</th>
                            <?php else :?>
                                <?php if ($desc) :?>
                                    <th class="order t-id"><a href="search.php?name=<?= h($getName)?>">会社番号  ▼</a></th>
                                <?php else :?>
                                    <th class="order t-id"><a href="search.php?name=<?= h($getName)?>&order=desc">会社番号  ▲</a></th>
                                <?php endif ?>
                            <?php endif?>
                            <th class="t-name">会社名</th>
                            <th class="t-manager">担当者名</th>
                            <th class="t-tel">電話番号</th>
                            <th class="t-address">住所</th>
                            <th class="t-mail">メールアドレス</th>
                            <th class="link">見積一覧</th>
                            <th class="link">請求一覧</th>
                            <th class="link">編集</th>
                            <th class="link">削除</th>
                        </tr>
                        <?php foreach ($companies as $company) :?>
                            <tr>
                                <td class="t-id"><?= h($company['id']) ?></td>
                                <td class="t-name"><?= h($company['name']) ?></td>
                                <td class="t-manager"><?= h($company['manager_name']) ?></td>
                                <td class="t-tel"><?= h($company['phone_number']) ?></td>
                                <td class="t-address"><?= '〒' . h(substr_replace($company['postal_code'], '-', 3, 0)) . "<br>" . PREFECTURES[h($company['prefecture_code'])] . h($company['address'])?></td>
                                <td class="t-mail"><?= h($company['mail_address'])?></td>
                                <td class="link to-list"><a href="../quotations/index.php?id=<?= h($company['id'])?>">見積一覧</a></td>
                                <td class="link to-list"><a href="../invoices/index.php?id=<?= h($company['id'])?>">請求一覧</a></td>
                                <td class="link"><a href="edit.php?id=<?= h($company['id'])?>">編集</a></td>
                                <form action="delete.php" method="POST" onsubmit= "return confirm_delete()">
                                    <input type="hidden" name="id" value=<?= h($company['id'])?>>
                                    <td class="link btn-delete"><input type="submit" value="削除" ></td>
                                </form>
                            </tr>
                        <?php endforeach?>
                    </table>
                </div>
                <div class="page-navigation">
                    <?php if ($showButton) :?>
                        <?php if ($desc) :?>
                            <?php if ($page <= 1) :?>
                                <a href="search.php?name=<?= h($getName)?>&page=<?= $page +1?>&order=desc" class="next p-nav">次へ<span>&rarr;</span></a>
                            <?php elseif ($page >= $maxPage) :?>
                                <a href="search.php?name=<?= h($getName)?>&page=<?= $page -1?>&order=desc" class="prev p-nav"><span>&larr;</span>前へ</a>
                            <?php elseif ($page == $maxPage) :?>
                            <?php else :?>
                                <a href="search.php?name=<?= h($getName)?>&page=<?= $page -1?>&order=desc" class="prev p-nav"><span>&larr;</span>前へ</a>
                                <a href="search.php?name=<?= h($getName)?>&page=<?= $page +1?>&order=desc" class="next p-nav">次へ<span>&rarr;</span></a>
                            <?php endif?>
                        <?php else :?>
                            <?php if ($page <= 1) :?>
                                <a href="search.php?name=<?= h($getName)?>&page=<?= $page +1?>" class="next p-nav">次へ<span>&rarr;</span></a>
                            <?php elseif ($page >= $maxPage) :?>
                                <a href="search.php?name=<?= h($getName)?>&page=<?= $page -1?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                            <?php else :?>
                                <a href="search.php?name=<?= h($getName)?>&page=<?= $page -1?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                                <a href="search.php?name=<?= h($getName)?>&page=<?= $page +1?>" class="next p-nav">次へ<span>&rarr;</span></a>
                            <?php endif?>
                        <?php endif?>
                    <?php endif?>
                </div>
            <?php else :?>
                <?php include("../common/no_data.php"); ?>
            <?php endif ?>
        </div>
    </main>
</body>
</html>
