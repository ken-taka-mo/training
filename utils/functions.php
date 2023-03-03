<?php
require_once('../config/columns.php');
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

function is_exact_id($id)
{
    return preg_match('/^[1-9]+\d*$/', $id);
}

function get_address($items)
{
    $json = file_get_contents("http://zipcloud.ibsnet.co.jp/api/search?zipcode={$items['postal_code']}");
    $json = mb_convert_encoding($json, 'UTF8');
    $jsonArr = json_decode($json, true);
    if ($jsonArr['results']) {
        $addressArr = $jsonArr['results'][0];
        return ['prefecture_code' => $addressArr['prefcode'], 'address' => $addressArr['address2'] . $addressArr['address3']];
    }
    return false;
}

?>
<script type="text/javascript">
    function confirm_delete() {
        let answer = confirm("本当に削除しますか");
        return answer;
    }
</script>