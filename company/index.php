<?php
require_once('../dbconnect.php');
require_once('../utils/functions.php');
require_once('../utils/prefectures.php');

$page = 1;
$counts = $db->query('SELECT COUNT(*) AS cnt FROM companies');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 10);
if ($maxPage == 0) {
    $maxPage = 1;
}
if (isset($_GET['page'])) {
    if (!preg_match('/^[0-9]+$/', $_GET['page']) || $_GET['page'] == 0) {
        header('Location: index.php');
        exit();
    } else {
        if ($_GET['page'] > $maxPage) {
            header("Location: index.php?page={$maxPage}");
            exit();
        } else {
            $page = $_GET['page'];
            $page = max($page, 1);
            $page = min($page, $maxPage);
        }
    }
}
$start = ($page - 1) * 10;

$statement = $db->prepare('SELECT id, name, manager_name, phone_number, postal_code, prefecture_code, address, mail_address FROM companies LIMIT ?,10');
$statement->bindParam(1, $start, PDO::PARAM_INT);
$statement->execute();
$companies = $statement->fetchAll();
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
    <main class="list li-company">
        <div class="container">
            <div class="heading">
                <h1>会社一覧</h1>
            </div>
            <div class="menu">
                <a href="register.php" class="create">新規登録</a>
                <form action="" method="GET">
                    <input type="text" class="search-form">
                    <input class="search" type="submit" value="検索">
                </form>
            </div>
            <table>
                <tr class="table-title">
                    <?php if (isset($_GET["order"])) :?>
                        <th class="th-id"><a href="index.php">会社番号</a></th><!-- クリック時昇順降順変更 -->
                    <?php else :?>
                        <th class="th-id"><a href="index.php?order=DESC">会社番号</a></th>
                    <?php endif ?>
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
                <!-- 会社テーブルのプレフィックス、作成日時、更新日時以外を10行ずつ一覧表示 -->
                <!-- デフォルトは会社番号昇順で表示 -->
                <!-- テーブルヘッダーの会社番号クリック時に会社番号降順で表示 -->
                <?php foreach ($companies as $company) :?>
                    <tr>
                        <th><?= h($company['id']) ?></th>
                        <th><?= h($company['name']) ?></th>
                        <th><?= h($company['manager_name']) ?></th>
                        <th><?= h(insertHyphen($company['phone_number'])) ?></th>
                        <th><?= '〒' . h(substr_replace($company['postal_code'], '-', 3, 0)) . "<br>" . $prefectures[h($company['prefecture_code'])] . h($company['address'])?></th>
                        <th><?= h($company['mail_address'])?></th>
                        <th class="link to-list"><a href="../quotations/index.php?id=<?= h($company['id'])?>">見積一覧</a></th>
                        <th class="link to-list"><a href="../invoices/index.php?id=<?= h($company['id'])?>">請求一覧</a></th>
                        <th class="link"><a href="edit.php?id=<?= h($company['id'])?>">編集</a></th>
                        <th class="link"><a href="delete.php?id=<?= h($company['id'])?>">削除</a></th><!-- where id = 会社番号でDELETE -->
                    </tr>
                <?php endforeach?>
            </table>
            <div class="page-navigation">
                <?php if ($page <= 1) :?>
                    <a href="index.php?page=<?= $page + 1?>" class="next p-nav">次へ<span>&rarr;</span></a>
                <?php elseif ($page >= $maxPage) :?>
                    <a href="index.php?page=<?= $page - 1?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                <?php else :?>
                    <a href="index.php?page=<?= $page - 1?>" class="prev p-nav"><span>&larr;</span>前へ</a>
                    <a href="index.php?page=<?= $page + 1?>" class="next p-nav">次へ<span>&rarr;</span></a>
                <?php endif?>
            </div>
        </div>
    </main>
</body>
</html>