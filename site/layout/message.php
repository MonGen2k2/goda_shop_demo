<?php

// !empty($_SESSION['success']) nghĩa là tồn tại phần tử có key là success và giá trị khác rỗng
// !empty đọc là có
$message = '';
$classType = '';
if (!empty($_SESSION['success'])) {
    $message = $_SESSION['success'];
    // xóa phần tử có key là success
    unset($_SESSION['success']);
    $classType = 'alert-success';
} else if (!empty($_SESSION['error'])) {
    $message = $_SESSION['error'];
    // xóa phần tử có key là success
    unset($_SESSION['error']);
    $classType = 'alert-danger';
}
?>
<?php
if ($message):
?>
<!-- .alert.alert-success -->
<div class="text-center alert <?=$classType?> mt-3"><?=$message?></div>
<?php endif?>