<?php 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class CustomerController {

	protected function checkLogin(){
		if(empty($_SESSION['email'])){
			// nếu chưa login điều hướng người dùng về trang chủ
			header('location: /');
			exit;
		}

	}

	// hiển thị thông tin tài khoản
	function show(){
		$this->checkLogin();
		// phải có dòng số 8 vì nó dùng để lưu biến email trên web server
		$email = $_SESSION['email'];
		$customerRepository = new CustomerRepository();
		$customer = $customerRepository->findEmail($email);
		
		require ABSPATH_SITE . 'view/customer/show.php';
	}

	// hiển thông tin địa chỉ giao hàng mặc định
	function shippingDefault(){
		require ABSPATH_SITE . 'view/customer/shippingDefault.php';
	}

	function updateAccount(){
		$this->checkLogin();

		$email = $_SESSION['email'];
		$customerRepository = new CustomerRepository();
		$customer = $customerRepository->findEmail($email);	
		$customer->setName($_POST['fullname']);
		$customer->setMobile($_POST['mobile']);

		// xử lý mật khẩu hiện tại
		$current_password = $_POST['current_password'];
		$password = $_POST['password'];
		// nếu có password hiện tại và password mới 
		if($current_password && $password){
			// kiểm tra mật khẩu hiện tại có đúng trong database không
			// password_verify(password chưa mã hóa, password mã hóa) có phải là 1 hay không
			if(!password_verify($current_password, $customer->getPassword())){
				$_SESSION['error'] = "Sai mật khẩu hiện tại";
				header('location: ?c=customer&a=show');
				exit;	
			}

			// mã hóa mật khẩu mới
			$encode_password = password_hash($password, PASSWORD_BCRYPT);
			$customer->setPassword($encode_password);

		}

		if($customerRepository->update($customer)){
			// cập nhật $_SESSION['name']
			$_SESSION['name'] = $_POST['fullname'];
			$_SESSION['success'] = "Đã cập nhật tài khoản thành công";
			// đi vào trang thông tin cá nhân
			header('location: ?c=customer&a=show');
			exit;	
		}

		$_SESSION['error'] = $customerRepository->getError();
		header('location: /');
	}

	// hiển thị chi tiết đơn hàng
	function orders(){
		$this->checkLogin();

		$email = $_SESSION['email'];
		$customerRepository = new CustomerRepository();
		$customer = $customerRepository->findEmail($email);	
		// Lấy đơn hàng từ database
		$orderRepository = new OrderRepository();
		$orders = $orderRepository->getByCustomerId($customer->getId());

		require ABSPATH_SITE . 'view/customer/orders.php';
	}

	// hiển thị chi tiết đơn hàng
	function orderDetail(){
		$id = $_GET['id'];
		$OrderRepository = new OrderRepository();
		$order = $OrderRepository->find($id);

		require ABSPATH_SITE . 'view/customer/orderDetail.php';
	}

	function notExistingEmail(){

		$email = $_GET['email'];
		$customerRepository = new CustomerRepository();
		$customer = $customerRepository->findEmail($email);
		if($customer){
			// tại sao không đặt return false là bởi vì echo trả về chuỗi còn return nó trả về cho 1 cái hàm
			echo('false');
			return;
		}
		echo('true');
	}
	
	// hạn chế việc tạo tài khoản bằng tool
	function register(){

		$secret = GOOGLE_RECAPTCHA_SITE;
		$recaptcha = new \ReCaptcha\ReCaptcha($secret);
		$gRecaptchaResponse = $_POST['g-recaptcha-response'];
		$remoteIp = '127.0.0.1';
		$resp = $recaptcha->setExpectedHostname('godashop.com')
						  ->verify($gRecaptchaResponse, $remoteIp);
		if ($resp->isSuccess()) {
			// chuyển lỗi array thành chuỗi
			$error = implode('<br>', $resp->getErrorCodes());
			$_SESSION['error'] = 'Error: ' . $error;
			header('location: /');
			exit;
		} 
		// thành công tạo account mới trong database
		$data = [];
		$data["name"] = $_POST['fullname'];
		// mã hóa mật khẩu 1 chiều (không thể giải mã theo mặt lý thuyết)
		$data["password"] = password_hash($_POST['password'], PASSWORD_BCRYPT);
		$data["mobile"] = $_POST['mobile'];
		$data["email"] = $_POST['email'];
		$data["login_by"] = 'form';
		$data["shipping_name"] = $_POST['fullname'];
		$data["shipping_mobile"] = $_POST['mobile'];
		$data["ward_id"] = null;
		$data["is_active"] = 0;
		$data["housenumber_street"] = '';

		$customerRepository = new CustomerRepository();
		if(!$customerRepository->save($data)){
			$_SESSION['error'] = $customerRepository->getError();
			header('location: /');
			exit;
		}
		$name = $_POST['fullname'];
		$to = $_POST['email'];
		$website = get_domain();
		// Lưu thông tin người dùng đã mã hóa vào cookie
		$payload = [
			'email' => $to,
		];

		$key = JWT_KEY;
		$token = JWT::encode($payload, $key, 'HS256');
		$linkActive = get_domain_site() . '?c=customer&a=active&token=' . $token ;//later
		$emailService = new EmailService();

		$subject = 'Active account';
		$content = 
		"
		Dear $name, <br>
		Vui lòng click vào link bên dưới để active account <br>
		<a href='$linkActive'> Active Account</a> <br>
		------------------------------------<br>
		Được gởi từ $website;
		"
		;
		$emailService->send($to, $subject, $content);	

		
		$_SESSION['success'] = 'Đã tạo tài khoản thành công. Vui lòng vào email để kích hoạt';
		header('location: /');
		exit;	
	}

	function active(){
		$token = $_GET['token'];
		$key = JWT_KEY;
		$decoded = JWT::decode($token, new key($key, 'HS256'));
		// check email có tồn tại hay không
		$email = $decoded->email;
		$customerRepository = new CustomerRepository();
		$customer = $customerRepository->findEmail($email);
		if(!$customer){
			$_SESSION['success'] = "Email $email không tồn tại trong hệ thống";
			header('location: /');
			exit;	
		}

		$customer->setIsActive(1);
		if($customerRepository->update($customer)){
			$_SESSION['success'] = "Đã kích hoạt tài khoản $email thành công";
			// đi vào trang thông tin cá nhân
			$_SESSION['email'] = $email;
			$_SESSION['name'] = $customer->getName();
			header('location: ?c=customer&a=show');
			exit;	
		}

		$_SESSION['error'] = $customerRepository->getError();
		header('location: /');
	}

}