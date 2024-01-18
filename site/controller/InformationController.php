<?php
class InformationController
{
    // chính sách thanh toán
    public function paymentPolicy()
    {
        require 'view/information/paymentPolicy.php';
    }

    // chính sách đổi trả
    public function returnPolicy()
    {
        require 'view/information/returnPolicy.php';
    }

    // chính sách giao hàng
    public function deliveryPolicy()
    {
        require 'view/information/deliveryPolicy.php';
    }
}
