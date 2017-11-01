<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use Webpatser\Uuid\Uuid;

class ProductController extends Controller
{
    private $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $products = Product::orderBy('likes', 'desc')->get();

        return view('admin.products.index', compact('products'));
    }


    public function create()
    {
        $categories = $this->categoryService->getTransformCategories();

        return view('admin.products.create', compact('categories'));
    }


    public function store(ProductRequest $request)
    {
        list(
            $product_data,
            $product_detail_data,
            $product_images_data,
            $product_attributes_data
            ) = $this->getRequestParam($request);


        $product = Product::create($product_data);
        // add product details data
        $product->productDetails()->create($product_detail_data);
        // add product images data
        $product->productImages()->createMany($product_images_data);
        // add product attributes data
        $product->productAttribute()->createMany($product_attributes_data);

        return back()->with('status', '添加商品成功');
    }

    public function show(Product $product)
    {
        //
    }


    public function edit(Product $product)
    {
        //
    }

    public function update(ProductRequest $request, Product $product)
    {
        //
    }

    public function destroy(Product $product)
    {
        //
    }

    private function getChangeAttrField(array $data)
    {
        $data = collect($data);
        $collects = [];

        foreach ($data->first() as $key => $value) {
            $collects[] = [
                'attribute' => $data['attribute'][$key],
                'items' => $data['items'][$key],
                'markup' => $data['markup'][$key],
            ];
        }

        return $collects;
    }

    private function keyToIndex(array $data)
    {
        $collects = [];

        foreach ($data as $key => $value) {

            foreach ($value as $v) {
                $collects[] = [$key => $v];
            }

        }

        return $collects;
    }

    /**
     * get format request param
     * @param ProductRequest $request
     * @return array
     */
    private function getRequestParam(ProductRequest $request)
    {
        // product table field
        $product_data = $request->only(['category_id', 'name', 'price', 'price_original']);
        // product thumb use image list first
        $product_data['thumb'] = $request->input('link')[0];
        $product_data['uuid'] = Uuid::generate()->hex;

        $product_detail_data = $request->only(['count', 'unit', 'description']);

        $product_images_data = $request->only(['link']);
        $product_images_data = $this->keyToIndex($product_images_data);

        $product_attributes_data = $request->only(['attribute', 'items', 'markup']);
        $product_attributes_data = $this->getChangeAttrField($product_attributes_data);

        return [
            $product_data,
            $product_detail_data,
            $product_images_data,
            $product_attributes_data
        ];
    }
}
