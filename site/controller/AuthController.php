<?php 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
	// authentication & authorization (xác thực & phân quyền)
	class AuthController {
		function login()
		{
			$email=$_POST['email'];
			$customerRepository = new CustomerRepository();
			$customer = $customerRepository->findEmail($email);
			// check email
			if(empty($customer)){
				$_SESSION['error'] = "Email không tồn tại";
				header('location: /');
				exit;
			}

			// check password
			$password = $_POST['password'];
			// passworrd_verify trả về true nếu mậu khẩu chưa mã hóa và mật khẩu đã mã hóa có phải là một không?
			if(!password_verify($password, $customer->getPassword())){
				$_SESSION['error'] = "Mật khẩu không đúng";
				header('location: /');
				exit;
			}

			// check account có active hay không
			if(!$customer->getIsActive()){
				$_SESSION['error'] = "Tài khoản chưa được kích hoạt";
				header('location: /');
				exit;
			}

			// đã login thành công dùng để lưu thông tin
			$_SESSION['email'] = $email;
			$_SESSION['name'] = $customer->getName();

			// Lưu thông tin người dùng đã mã hóa vào cookie
			$payload = [
				'email' => $email,
				'name' => $customer->getName()
			];

			$key = JWT_KEY;
			$jwt = JWT::encode($payload, $key, 'HS256');
			$file_day_num = 7;
			setcookie("token_remember_me", $jwt, time() + 24 * 60 * 60 * $file_day_num);

			
			
			// điều hướng về trang tài khoản người dùng
			header('location: ?c=customer&a=show'); //update later
		}

		function logout() 
		{
			// hủy session
			session_destroy();
			// hủy cookie
			// cho thời gian hết hạn của cookie lùi lại 1 ngày
			setcookie("token_remember_me", null, time() - 24 * 60 * 60);

			// điều hướng về trang chủ
			header('location: /');
		}


	}
?>