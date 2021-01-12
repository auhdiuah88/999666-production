<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Services\Admin\ProductService;

class ProductController extends Controller
{

    protected $ProductService;

    public function __construct
    (
        ProductService $productService
    )
    {
        $this->ProductService = $productService;
    }



}
