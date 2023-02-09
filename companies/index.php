<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');
require_once('../utils/prefectures.php');

const DATA_PER_PAGE = 10;
$page = 1;
$counts = $db->query('SELECT COUNT(*) AS cnt FROM companies WHERE deleted is NULL');
$cnt = $counts->fetch();
if ($cnt['cnt'] < 1) {
    $companiesExist = false;
} else {
    $companiesExist = true;
    $maxPage = ceil($cnt['cnt'] / DATA_PER_PAGE);
    if ($maxPage == 0) {
        $maxPage = 1;
    }
    if (isset($_GET['page'])) {
        if (!preg_match('/^[0-9]+$/', $_GET['page']) || preg_match('/^[0]*$/', $_GET['page'])) {
            header('Location: index.php');
            exit();
        }

        if ($_GET['page'] > $maxPage) {
            header("Location: index.php?page={$maxPage}");
            exit();
        } else {
            $page = $_GET['page'];
            $page = max($page, 1);
            $page = min($page, $maxPage);
        }
    }
    $start = ($page - 1) * 10;

    $showButton = false;
    if ($maxPage > 1) {
        $showButton = true;
    }

    if (isset($_GET['order'])) {
        if ($_GET['order'] == 'desc') {
            $desc = true;
            $statement = $db->prepare('SELECT id, name, manager_name, phone_number, postal_code, prefecture_code, address, mail_address FROM companies WHERE deleted is NULL ORDER BY id DESC LIMIT ?,10');
        } else {
            header('Location: index.php');
            exit();
        }
    } else {
        $desc = false;
        $statement = $db->prepare('SELECT id, name, manager_name, phone_number, postal_code, prefecture_code, address, mail_address FROM companies WHERE deleted is NULL LIMIT ?,10');
    }

    $statement->bindParam(1, $start, PDO::PARAM_INT);
    $statement->execute();
    $companies = $statement->fetchAll();
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
                    <input type="text" class="search-form" name="name">
                    <input class="btn-search" type="submit" value="検索">
                </form>
            </div>
            <?php if ($companiesExist) :?>
                <table>
                    <tr class="list-title title">
                        <?php if ($desc) :?>
                            <th class="order th-id"><a href="index.php">会社番号</a></th>
                        <?php else :?>
                            <th class="order th-id"><a href="index.php?order=desc">会社番号</a></th>
                        <?php endif ?>
                        <th class="th-name">会社名</th>
                        <th class="th-manager">担当者名</th>
                        <th class="th-tel">電話番号</th>
                        <th class="th-address">住所</th>
                        <th class="th-mail">メールアドレス</th>
                        <th class="link">見積一覧</th>
                        <th class="link">請求一覧</th>
                        <th class="link">編集</th>
                        <th class="link">削除</th>
                    </tr>
                    <?php foreach ($companies as $company) :?>
                        <tr>
                            <th><?= h($company['id']) ?></th>
                            <th><?= h($company['name']) ?></th>
                            <th><?= h($company['manager_name']) ?></th>
                            <th><?= h($company['phone_number']) ?></th>
                            <th><?= '〒' . h(substr_replace($company['postal_code'], '-', 3, 0)) . "<br>" . $prefectures[h($company['prefecture_code'])] . h($company['address'])?></th>
                            <th><?= h($company['mail_address'])?></th>
                            <th class="link to-list"><a href="../quotations/index.php?id=<?= h($company['id'])?>">見積一覧</a></th>
                            <th class="link to-list"><a href="../invoices/index.php?id=<?= h($company['id'])?>">請求一覧</a></th>
                            <th class="link"><a href="edit.php?id=<?= h($company['id'])?>">編集</a></th>
                            <form action="delete.php" method="POST" onsubmit= "return confirmDelete()">
                                <input type="hidden" name="id" value=<?= $company['id']?>>
                                <th class="link btn-delete"><input type="submit" value="削除" ></th>
                            </form>
                        </tr>
                    <?php endforeach?>
                </table>
                <div class="page-navigation">
                    <?php if ($showButton) :?>
                        <?php if ($desc) :?>
                            <?php if ($page <= 1) :?>
                                <a href="index.php?page=<?= $page +1?>&order=desc" class="next p-nav">次へ<span>&rarr;</span></a>
                            <?php elseif ($page >= $maxPage) :?>
                                <a href="index.php?page=<?= $page -1?>&order=desc" class="prev p-nav"><span>&larr;</span>前へ</a>
                            <?php elseif ($page == $maxPage) :?>
                            <?php else :?>
                                <a href="index.php?page=<?= $page -1?>&order=desc" class="prev p-nav"><span>&larr;</span>前へ</a>
                                <a href="index.php?page=<?= $page +1?>&order=desc" class="next p-nav">次へ<span>&rarr;</span></a>
                            <?php endif?>
                        <?php else :?>
                            <?php if ($page <= 1) :?>
                                <a href="index.php?page=<?= $page +1?>" class="next p-nav">次へ<span>&rarr;</span></a>
                            <?php elseif ($page >= $maxPage) :?>
                                <a href="index.php?page=<?= $page -1?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                            <?php else :?>
                                <a href="index.php?page=<?= $page -1?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                                <a href="index.php?page=<?= $page +1?>" class="next p-nav">次へ<span>&rarr;</span></a>
                            <?php endif?>
                        <?php endif?>
                    <?php endif?>
                </div>
            <?php else :?>
                <table>
                    <tr class="list-title title">
                        <th>会社番号</th>
                        <th>会社名</th>
                        <th>担当者名</th>
                        <th>電話番号</th>
                        <th>住所</th>
                        <th>メールアドレス</th>
                        <th class="link">見積一覧</th>
                        <th class="link">請求一覧</th>
                        <th class="link">編集</th>
                        <th class="link">削除</th>
                    </tr>
                    <tr>
                        <th>会社データはありません</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </table>
            <?php endif ?>
        </div>
    </main>
</body>
</html>