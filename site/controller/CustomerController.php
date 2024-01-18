<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CustomerController
{
    protected function checkLogin()
    {
        // nếu chưa login điều hướng người dùng về trang chủ
        if (empty($_SESSION['email'])) {
            header('location: /');
            exit;
        }
    }

    // hiển thị thông tin tài khoản
    function show()
    {
        $this->checkLogin();
        $email = $_SESSION['email'];
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        require 'view/customer/show.php';
    }

    function updateAccount()
    {
        $this->checkLogin();
        $email = $_SESSION['email'];
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        $customer->setName($_POST['fullname']);
        $customer->setMobile($_POST['mobile']);

        $current_password = $_POST['current_password'];
        $password = $_POST['password'];
        // nếu có password hiện tại và password mới
        if ($current_password && $password) {
            //kiểm tra mật khẩu hiện tại có đúng trong database không
            // password_verify(pasword chưa mã hóa, password đã mã hóa) có phải là 1 không?
            if (!password_verify($current_password, $customer->getPassword())) {
                $_SESSION['error'] = "Sai mật khẩu hiện tại";
                header('location: ?c=customer&a=show');
                exit;
            }

            // mã hóa mật khẩu mới
            $encode_password = password_hash($password, PASSWORD_BCRYPT);
            $customer->setPassword($encode_password);
        }

        if ($customerRepository->update($customer)) {
            // cập nhật $_SESSION['name'];
            $_SESSION['name'] = $_POST['fullname'];
            $_SESSION['success'] = "Đã cập nhật tài khoản thành công";
            header('location: ?c=customer&a=show');
            exit;
        }
        $_SESSION['error'] = $customerRepository->getError();
        header('location: /');
    }

    // hiển thị thông tin địa chỉ giao hàng mặc định
    function shippingDefault()
    {
        $this->checkLogin();
        require 'view/customer/shippingDefault.php';
    }

    // hiển thị thông tin danh sách đơn hàng của người đăng nhập
    function orders()
    {
        $this->checkLogin();
        $email = $_SESSION['email'];
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        $orderRepository = new OrderRepository();
        $orders = $orderRepository->getByCustomerId($customer->getId());
        require 'view/customer/orders.php';
    }

    // hiển thị thông tin chi tiết đơn hàng
    function orderDetail()
    {
        $this->checkLogin();
        $id = $_GET['id'];
        $orderRepository = new OrderRepository();
        $order = $orderRepository->find($id);
        require 'view/customer/orderDetail.php';
    }

    function notExistingEmail()
    {
        $email = $_GET['email'];
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        if ($customer) {
            echo 'false';
            return;
        }
        echo 'true';
    }

    function register()
    {
        $secret = GOOGLE_RECAPTCHA_SECRET;
        $recaptcha = new \ReCaptcha\ReCaptcha($secret);
        $gRecaptchaResponse = $_POST['g-recaptcha-response'];
        $remoteIp = '127.0.0.1';
        $resp = $recaptcha->setExpectedHostname('godashop.com')
            ->verify($gRecaptchaResponse, $remoteIp);
        if (!$resp->isSuccess()) {
            // chuyển lỗi array thành chuỗi 
            $error = implode('<br>', $resp->getErrorCodes());
            $_SESSION['error'] = 'Error: ' . $error;
            header('location: /');
            exit;
        }

        //thành công. Tạo account mới trong database
        $data = [];
        $data["name"] = $_POST['fullname'];
        // mã hóa mật khẩu 1 chiều (không thể giải mã theo lý thuyết)
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
        if (!$customerRepository->save($data)) {
            $_SESSION['error'] = $customerRepository->getError();
            header('location: /');
            exit;
        }

        $emailService = new EmailService();
        $to = $_POST['email'];
        $subject = 'Active account';
        $name = $_POST['fullname'];
        $website = get_domain();
        $payload = [
            'email' => $to,
        ];
        $key = JWT_KEY;
        $token = JWT::encode($payload, $key, 'HS256');

        $linkActive = get_domain_site() . '?c=customer&a=active&token=' . $token; //later
        $content = "
        Dear $name, <br>
        Vui lòng click vào link bên dưới để active account <br>
        <a href='$linkActive'>Active Account</a> <br>
        ------------------------<br>
        Được gởi từ $website;
        ";
        $emailService->send($to, $subject, $content);

        $_SESSION['success'] = 'Đã đăng ký thành công. Vui lòng vào email để kích thoạt tài khoản';
        header('location: /');
    }

    function active()
    {
        $token = $_GET['token'];
        $key = JWT_KEY;
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        // check email có tồn tại không
        $email = $decoded->email;
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        if (!$customer) {
            $_SESSION['error'] = "Email $email không tồn tại trong hệ thống";
            header('location: /');
            exit;
        }

        $customer->setIsActive(1);
        if ($customerRepository->update($customer)) {
            $_SESSION['success'] = "Đã kích hoạt tài khoàn $email thành công";
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
