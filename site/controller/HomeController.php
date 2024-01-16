<?php 
	class HomeController
	{
		public function index()
		{
			$conds = [];
			$page = 1;
			$item_per_page = 4;
			$productRepository = new ProductRepository();
			// Lấy 4 sản phẩm nổi bật
			$sorts = ['featured' => 'DESC']; //giảm dần
			$featuredProducts = $productRepository->getBy($conds, $sorts, $page, $item_per_page);

			// Lấy 4 sản phẩm mới nhất
			$sorts = ['created_date' => 'DESC']; //giảm dần
			$latestProducts = $productRepository->getBy($conds, $sorts, $page, $item_per_page);

			// Lấy tất cả các danh mục
			$categoryRepository = new CategoryRepository();
			$categories = $categoryRepository->getAll();
			$sorts = [];
			$categoryProducts = [];
			foreach($categories as $category){
				$conds = [
					'category_id' => [
						'type' => '=',
						'val' => $category->getId(),
					],
				];
				$products = $productRepository->getBy($conds, $sorts, $page, $item_per_page);
				// SELECT * FROM view_product WHERE category_id = 3
				$categoryProducts[] = [
					'categoryName' => $category->getName(),
					'products' => $products,
				];
			}

			require ABSPATH_SITE . 'view/home/index.php';
		}
	}
?>