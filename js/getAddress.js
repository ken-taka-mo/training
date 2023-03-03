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