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

// 入力が空かチェック
function check_empty($array)
{
    $emptyArray = [];
    foreach ($array as $key => $value) {
        if (preg_match('/^[\s]*$/', $value)) {
            $emptyArray[$key] = COLUMNS[$key] . 'を入力してください';
        }
    }
    return $emptyArray;
}

// 値の長さチェック
function check_length_over($array, $key, $length)
{
    $overArray = [];
    if (mb_strlen($array[$key]) > $length) {
        $overArray[$key] = COLUMNS[$key] . 'は' . $length . '字以内で入力してください';
    }
    return $overArray;
}

// 会社テーブルバリデーション
function check_company($array)
{
    $invalidValArray = [];
    if (!preg_match('/^\d{1,11}$/', $array['phone_number'])) {
        $invalidValArray['phone_number'] = '電話番号は11桁以下の整数で入力してください';
    }

    if (!preg_match('/^\d{7}$/', $array['postal_code'])) {
        $invalidValArray['postal_code'] = '郵便番号は7桁の半角数字で入力してください';
    }

    if ($array['prefecture_code'] < 1 && $array['prefecture_code'] > 47) {
        $invalidValArray['prefecture_code'] = 'もう一度都道府県を選択してください';
    }

    if (!preg_match('/^[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*@[a-zA-Z0-9_+-]+(\.[a-zA-Z0-9_+-]+)*$/', $array['mail_address'])) {
        $invalidValArray['mail_address'] = '正しいメールアドレスを入力してください';
    }

    if (isset($array['prefix'])) {
        if (!preg_match('/^[a-zA-Z0-9]{1,8}$/', $array['prefix'])) {
            $invalidValArray['prefix'] = 'プレフィックスは8字以内の半角英数字で入力してください';
        }
    }
    $invalidValArray += check_length_over($array, 'name', 64);
    $invalidValArray += check_length_over($array, 'manager_name', 32);
    $invalidValArray += check_length_over($array, 'address', 100);
    $invalidValArray += check_length_over($array, 'mail_address', 100);

    $checkedArray = [];
    $checkedArray += check_empty($array);
    $checkedArray += $invalidValArray;
    return $checkedArray;
}

function check_quotation($array)
{
    $invalidValArray = [];
    if (!preg_match('/^[1-9]\d{0,8}$/', $array['total'])) {
        $invalidValArray['total'] = '金額は9桁以下の半角数字のみで入力してください';
    }
    if ($array['due_date'] <= date("Y-m-d")) {
        $invalidValArray['due_date'] = '本日以降の日付を入力してください';
    }
    if (!preg_match('/^[129]$/', $array['status'])) {
        $invalidValArray['status'] = '状態をもう一度選択してください';
    }

    $invalidValArray += check_length_over($array, 'title', 64);
    $invalidValArray += check_length_over($array, 'validity_period', 32);

    $checkedArray = [];
    $checkedArray += check_empty($array);
    $checkedArray += $invalidValArray;
    return $checkedArray;
}

function check_invoice($array)
{
    $invalidValArray = [];

    if (!preg_match('/^[1-9]{1}\d{0,8}$/', $array['total'])) {
        $invalidValArray['total'] = '金額は9桁以下の半角数字のみで入力してください';
    }
    if ($array['payment_deadline'] <= date("Y-m-d")) {
        $invalidValArray['payment_deadline'] = '本日以降の日付を入力してください';
    }
    if (isset($array['quotation_no'])) {
        if (!preg_match('/^\d{8}$/', $array['quotation_no'])) {
            $invalidValArray['quotation_no'] = '見積番号は8桁の半角数字で入力して下さい';
        }
    }
    if (!preg_match('/^[129]$/', $array['status'])) {
        $invalidValArray['status'] = '状態をもう一度選択してください';
    }
    $invalidValArray += check_length_over($array, 'title', 64);
    $checkedArray = [];
    $checkedArray += check_empty($array);
    $checkedArray += $invalidValArray;
    return $checkedArray;
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

    function getAddress() {
        const postalCodeInput = document.getElementById('postal_code');
        postalCodeInput.addEventListener("input",function () {
            let postalCode = postalCodeInput.value;
            let stringValue = postalCode.toString();
            let numberValue = Number(postalCode);
            if (stringValue.length === 7 && Number.isInteger(numberValue)) {
                async function callAddressApi() {
                    let res = await fetch(`http://zipcloud.ibsnet.co.jp/api/search?zipcode=${postalCode}`);
                    let resJson = await res.json();
                    if (resJson.results) {
                        let addressData = resJson.results[0];
                        let prefectureCode = addressData.prefcode;
                        let address = addressData.address2 + addressData.address3;
                        console.log(resJson);
                        document.getElementById('prefecture_code').value = prefectureCode
                        document.getElementById('address').value = address;
                    }
                }
                callAddressApi();
            }
        });
    };
</script>