<?php

// if (isset($_GET("search"))) {
//     $companies = searchの値によってSQLを変える
// } else {
//     if($_GET["order"] = "DESC") {
//         $companies = 降順
//     } else {
//         $companies = 昇順
//     }
// }

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
                <a href="company/register_company.php" class="create">新規登録</a><!-- company/register_company.phpに遷移 -->
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
                <tr>
                    <!-- 会社テーブルのプレフィックス、作成日時、更新日時以外を10行ずつ一覧表示 -->
                    <!-- デフォルトは会社番号昇順で表示 -->
                    <!-- テーブルヘッダーの会社番号クリック時に会社番号降順で表示 -->
                    <th class="link to-list"><a href="../quotations/">見積一覧</a></th><!-- estimation/index.php?=会社番号(ID)で遷移 -->
                    <th class="link to-list"><a href="../invoices/">請求一覧</a></th><!-- bill/index.php?=会社番号(ID)で遷移 -->
                    <th class="link"><a href="#">編集</a></th><!-- company/edit_company.php?=会社番号(ID)で遷移-->
                    <th class="link"><a href="#">削除</a></th><!-- where id = 会社番号でDELETE -->
                </tr>
            </table>
            <div class="page-navigation">
                <a href="" class="prev p-nav"><span>&larr;</span>前へ</a><!-- 前の10行を表示。なければクリック不可。 -->
                <a href="" class="next p-nav">次へ<span>&rarr;</span></a> <!-- 次の10行を表示。なければクリック不可。 -->
            </div>
        </div>
    </main>
</body>
</html>