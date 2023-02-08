<?php
function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES);
}
?>

<script type="text/javascript">
    function confirmDelete() {
        let answer = confirm("本当に削除しますか");
        return answer;
    }
</script>