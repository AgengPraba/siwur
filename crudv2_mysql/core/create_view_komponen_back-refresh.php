<?php
$string = "<script>
    window.addEventListener('popstate', () => {
        window.location.reload();
    });
</script>";

$hasil_view_form = createFile($string, "../resources/views/components/back-refresh.blade.php");
