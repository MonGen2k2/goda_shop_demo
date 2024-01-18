<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start();
// router
// import config và connectdb
require '../config.php';
require '../connectDb.php';

//Load Composer's autoloader
require '../vendor/autoload.php';

$c = $_GET['c'] ?? 'home';
$a = $_GET['a'] ?? 'index';

// ucfirst() là chữ hoa ký tự đầu tiên
$strController = ucfirst($c) . 'Controller'; //StudentController

// import model
require '../bootstrap.php';

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


// import file chứa class controller tương ứng
require "controller/$strController.php";

// Cuối cùng là muốn gọi hàm của controller tương ứng
$controller = new $strController(); //new StudentController()
$controller->$a(); //$controller->index();