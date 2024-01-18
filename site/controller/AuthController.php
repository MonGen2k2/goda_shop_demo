<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// authentication & authorization (xác thực & phân quyền)
class AuthController
{
    public function login()
    {
        $email = $_POST['email'];
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        // check email
        if (empty($customer)) {
            $_SESSION['error'] = "Email không tồn tại";
            header('location: /');
            exit;
        }

        // check password
        $password = $_POST['password'];
        // password_verify trả về true nếu mật khẩu chưa mã hóa và mật khẩu đã mã hóa có phải là một không?
        if (!password_verify($password, $customer->getPassword())) {
            $_SESSION['error'] = "Mật khẩu không đúng";
            header('location: /');
            exit;
        }

        // check account is active or not?
        if (!$customer->getIsActive()) {
            $_SESSION['error'] = "Tài khoản chưa được kích hoạt";
            header('location: /');
            exit;
        }

        // đã login thành công
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $customer->getName();

        // lưu thông tin người đã mã hóa vào cookie
        $payload = [
            'email' => $email,
            'name' => $customer->getName()
        ];
        $key = JWT_KEY;
        $jwt = JWT::encode($payload, $key, 'HS256');
        $life_day_num = 7;
        setcookie("token_remember_me", $jwt, time() + 24 * 60 * 60 * $life_day_num);

        // điều hướng vể trang tài khoản người dùng
        header('location: ?c=customer&a=show');
    }

    public function logout()
    {
        // hủy session
        session_destroy();
        // hủy cookie
        // cho thời gian hết hạn của cookie lùi lại 1  ngày so với hiện tại
        setcookie("token_remember_me", null, time() - 24 * 60 * 60);
        // điều hướng về trang chủ
        header('location: /');
    }
    //mã hóa
    function test1()
    {
        $payload = [
            'email' => 'abc@gmail.com'
        ];
        $key = 'con bò đang gặm cỏ sau hè';
        $jwt = JWT::encode($payload, $key, 'HS256');
        echo $jwt;
    }

    // giải mã
    function test2()
    {
        $jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6ImFiY0BnbWFpbC5jb20ifQ.Fg45W2t1u6hu6rH6XvS1x4vf8Fodwo9KlMa1hPkAWQM';
        $key = 'con bò đang gặm cỏ sau hè';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        var_dump($decoded);
    }
}
