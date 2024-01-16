<?php 
        // !empty($_SESSION['success']) nghĩa là tồn tại phần tử có key là success và giá trị khác rỗng
        // !empty đọc là có
            $message = '';
            $classType = '';

            if(!empty($_SESSION['success'])){
                $message = $_SESSION['success'];
                // unset: xóa phần tử
                unset($_SESSION['success']);
                $classType = 'alert-success';
            }
            else if(!empty($_SESSION['error'])){
                $message = $_SESSION['error'];
                // unset: xóa phần tử
                unset($_SESSION['error']);
                $classType = 'alert-danger';
            }
        ?>

<?php 
            if($message):
 ?>
<div class="alert text-center <?=$classType?> mt-3">
	<?=$message?>
</div>

<?php endif ?>