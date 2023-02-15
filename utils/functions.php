<?php
require_once('columns.php');
// htmlタグの入力を変換する
function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES);
}

// 配列の値の全角スペース、全角数字を半角にする
function convert_half_width($array)
{
    $halvedArray = [];
    foreach ($array as $key => $value) {
        $halvedArray[$key] = preg_replace("/(^\s+)|(\s+$)/u", "", $value);
        $halvedArray[$key] = mb_convert_kana($halvedArray[$key], "n");
    }
    return $halvedArray;
}

function check_empty($array)
{
    $chekedArray = [];
    foreach ($array as $key => $value) {
        if (preg_match('/^[\s]*$/', $value)) {
            $chekedArray[$key] = COLUMNS[$key] . 'を入力してください';
        }
    }
    return $chekedArray;
}
?>

<script type="text/javascript">
    function confirm_delete() {
        let answer = confirm("本当に削除しますか");
        return answer;
    }
</script>