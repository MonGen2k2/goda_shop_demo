<?php
class HomeController
{
    public function index()
    {
        $conds = [];
        $page = 1;
        $item_per_page = 4;
        $productRepository = new ProductRepository();
        // lấy 4 sản phẩm nổi bật
        $sorts = ['featured' => 'DESC']; //giảm dần
        $featuredProducts = $productRepository->getBy($conds, $sorts, $page, $item_per_page);
        // SELECT * FROM view_product ORDER BY featured DESC LIMIT 0, 4

        // lấy 4 sản phẩm mới nhất
        $sorts = ['created_date' => 'DESC']; //giảm dần
        $latestProducts = $productRepository->getBy($conds, $sorts, $page, $item_per_page);
        // SELECT * FROM view_product ORDER BY created_date DESC LIMIT 0, 4

        // Lấy tất cả các danh mục
        $categoryRepository = new CategoryRepository();
        $categories = $categoryRepository->getAll();
        // Duyệt từng danh mục để lấy sản phẩm theo danh mục đó
        $sorts = [];
        $categoryProducts = [];
        foreach ($categories as $category) {
            $conds = [
                'category_id' => [
                    'type' => '=',
                    'val' => $category->getId(), //3
                ],
            ];
            $products = $productRepository->getBy($conds, $sorts, $page, $item_per_page);
            // SELECT * FROM view_product WHERE category_id=3
            $categoryProducts[] = [
                'categoryName' => $category->getName(),
                'products' => $products,
            ];
        }

        require 'view/home/index.php';

    }
}
