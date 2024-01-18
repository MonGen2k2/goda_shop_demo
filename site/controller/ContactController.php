<?php
class ContactController
{
    // hiển thị form liên hệ
    public function form()
    {
        require 'view/contact/form.php';
    }

    // Gởi mail đến chủ shop
    public function sendEmail()
    {
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $message = $_POST['content'];
        $website = get_domain();
        $emailService = new EmailService();
        $to = SHOP_OWNER;
        $subject = APP_NAME . ' - Liên hệ';
        $content = "
        Chào chủ shop, <br>
        Dưới đây là thông tin khách hàng liên hệ: <br>
        Tên: $fullname, <br>
        Email: $email, <br>
        Mobile: $mobile, <br>
        Nội dung: $message, <br>
        ------------------- <br>
        Được gởi từ website $website
        ";
        $emailService->send($to, $subject, $content);
        echo 'Đã gởi mail liên hệ thành công';
    }
}
