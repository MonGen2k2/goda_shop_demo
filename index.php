<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Cocur\Slugify\Slugify;


session_start();

// nếu chưa login thì kiểm tra trong cookie xem có token_remember_me không?
if (empty($_SESSION['email']) && !empty($_COOKIE['token_remember_me'])) {
    // chuyển cookie sang session
    $key = JWT_KEY;
    $jwt = $_COOKIE['token_remember_me'];
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    $email = $decoded->email;
    $name = $decoded->name;
    $_SESSION['email'] = $email;
    $_SESSION['name'] = $name;
}

require 'vendor/autoload.php';
$router = new AltoRouter();
$slugify = new Slugify();


// import config và connectdb
require 'config.php';
require ABSPATH . 'connectDb.php';

// import model
require ABSPATH . 'bootstrap.php';

// import controller
require ABSPATH_SITE . 'load.php';

// trang chủ
// gọi hàm index() của HomeController
$router->map('GET', '/', function () {
    // Gọi hàm index của HomeController với không tham số
    $controller = new HomeController();
    $controller->index();
}, 'home');

// gọi hàm index() của ProductController
$router->map('GET', '/san-pham.html', function () {
    // Gọi hàm index của ProductController với không tham số
    $controller = new ProductController();
    $controller->index();
}, 'product');


// trang chi tiết sản phẩm
// vd: /san-pham/kem-chong-nang-la-roche-posay-kiem-soat-dau-spf50-50ml-12684.html
// gọi hàm detail() của ProductController

$router->map('GET', '/san-pham/[*:slug]-[i:id].html', function ($slug, $id) {
    // Gọi hàm detail của ProductController với tham số id

    $controller = new ProductController();
    $controller->detail($id);
}, 'productDetail');

$router->map('GET', '/chinh-sach-doi-tra.html', function () {
    $controller = new InformationController();
    $controller->returnPolicy();
}, 'returnPolicy');

// chính sách đổi trả
$router->map('GET', '/chinh-sach-giao-hang.html', function () {
    $controller = new InformationController();
    $controller->returnPolicy();
}, 'deliveryPolicy');

// trang chính sách thanh toán
$router->map('GET', '/chinh-sach-thanh-toan.html', function () {
    $controller = new InformationController();
    $controller->deliveryPolicy();
}, 'paymentPolicy');

// trang liên hệ
$router->map('GET', '/lien-he.html', function () {
    $controller = new ContactController();
    $controller->form();
}, 'contact');

// trang danh mục
// /danh-muc/duong-am-da-1863.html
$router->map('GET', '/danh-muc/[*:slug]-[i:categoryId].html', function ($slug, $categoryId) {
    $controller = new ProductController();
    $controller->index($categoryId);
}, 'category');

// match current request url
$match = $router->match();
$routeName = is_array($match) ? $match['name'] : '';

// call closure or throw 404 status
if (is_array($match) && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
    exit;
}

$c = $_GET['c'] ?? 'home';
$a = $_GET['a'] ?? 'index';

// ucfirst() là chữ hoa ký tự đầu tiên
$strController = ucfirst($c) . 'Controller'; //StudentController
// Cuối cùng là muốn gọi hàm của controller tương ứng
$controller = new $strController(); //new StudentController()
$controller->$a(); //$controller->index();