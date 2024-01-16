<?php 
class InformationController {
	// chinhs sachs thanh toasn
	public function paymentPolicy(){
		require ABSPATH_SITE . 'view/information/paymentPolicy.php';
	}

	// chính sách đổi trả
	public function returnPolicy(){
		require ABSPATH_SITE . 'view/information/returnPolicy.php';
	}
	
	// chính sách giao hàng
	public function deliveryPolicy(){
		require ABSPATH_SITE . 'view/information/deliveryPolicy.php';
	}
}
?>