<?php
class ProductController
{
    // Hiển thị danh sách sản phẩm
    public function index()
    {
        // Lấy 10 sản phẩm từ database đổ ra view
        $productRepository = new ProductRepository();
        $conds = [];
        $sorts = [];
        $page = $_GET['page'] ?? 1;
        $item_per_page = 10;

        $category_id = $_GET['category_id'] ?? null;
        if ($category_id) {
            $conds = [
                'category_id' => [
                    'type' => '=',
                    'val' => $category_id,
                ],
            ];
            // SELECT * FROM view_product WHERE category_id=3
        }
        // price-range=200000-300000
        $priceRange = $_GET['price-range'] ?? null;
        if ($priceRange) {
            $temp = explode('-', $priceRange);
            $start_price = $temp[0];
            $end_price = $temp[1];
            $conds = [
                'sale_price' => [
                    'type' => 'BETWEEN',
                    'val' => "$start_price AND $end_price",
                ],
            ];
            // SELECT * FROM view_product WHERE sale_price BETWEEN 200000 AND 300000
            // price-range=1000000-greater
            if ($end_price == 'greater') {
                $conds = [
                    'sale_price' => [
                        'type' => '>=',
                        'val' => $start_price,
                    ],
                ];
                // SELECT * FROM view_product WHERE sale_price >= 1000000
            }
        }

        // sort=price-asc
        $sort = $_GET['sort'] ?? null;
        if ($sort) {
            $temp = explode('-', $sort);
            $dummyCol = $temp[0]; //price
            $order = $temp[1]; //asc
            $order = strtoupper($order); //ASC
            $map = ['price' => 'sale_price', 'alpha' => 'name', 'created' => 'created_date'];
            $columnName = $map[$dummyCol];
            $sorts = [$columnName => $order];
            // SELECT * FROM view_product ORDER BY sale_price ASC
        }

        $search = $_GET['search'] ?? '';
        if ($search) {
            $conds = [
                'name' => [
                    'type' => 'LIKE',
                    'val' => "'%$search%'",
                ],

            ];
            // SELECT * FROM view_product WHERE name LIKE '%kem%'
        }

        $products = $productRepository->getBy($conds, $sorts, $page, $item_per_page);

        $totalProducts = $productRepository->getBy($conds, $sorts);
        // tìm số trang
        $totalPage = ceil(count($totalProducts) / $item_per_page);

        // Lấy tất cả danh mục đổ ra view
        $categoryRepository = new CategoryRepository();
        $categories = $categoryRepository->getAll();
        //
        require 'view/product/index.php';
    }

    public function detail()
    {
        $id = $_GET['id'];
        $productRepository = new ProductRepository();
        $product = $productRepository->find($id);
        // sản phẩm có liên quan là sản phẩm cùng danh mục
        $category_id = $product->getCategoryId();
        $conds = [
            'category_id' => [
                'type' => '=',
                'val' => $category_id,
            ],
            'id' => [
                'type' => '!=',
                'val' => $id,
            ],
        ];
        $relatedProducts = $productRepository->getBy($conds);

        // Lấy tất cả danh mục đổ ra view
        $categoryRepository = new CategoryRepository();
        $categories = $categoryRepository->getAll();
        
        require 'view/product/detail.php';
    }

    public function storeComment()
    {
        $data = [];
        $data["email"] = $_POST['email'];
        $data["fullname"] = $_POST['fullname'];
        $data["star"] = $_POST['rating'];
        $data["created_date"] = date('Y-m-d H:i:s');
        $data["description"] = $_POST['description'];
        $data["product_id"] = $_POST['product_id'];

        $commentRepository = new CommentRepository();
        $commentRepository->save($data);

        $productRepository = new ProductRepository();
        $product = $productRepository->find($data["product_id"]);

        require 'view/product/comments.php';
    }
}