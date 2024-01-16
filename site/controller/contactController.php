<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
class ContactController {
	// hiển thị form liên hệ
	function form()
	{
		require ABSPATH_SITE . 'view/contact/form.php';
	}

	// gửi mail đến chủ shop
	public function sendEmail(){
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
		Dưới đây là thông tin khách hàng liên hệ <br>
		Tên: $fullname; <br>
		Email: $email; <br>
		Mobile: $mobile, <br>
		Nội dung: $message, <br>
		-------------------------------
		Được gửi từ website $website
		";
		$emailService->send($to, $subject, $content);
		echo 'Đã gửi email liên hệ thành công';
	}
}