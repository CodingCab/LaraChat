<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductsMergeIndexRequest;
use App\Services\ProductService;

class ProductsMergeController extends Controller
{
    public function index(ProductsMergeIndexRequest $request): string
    {
        ProductService::merge($request->input('sku1'), $request->input('sku2'));

        return 'ok';
    }
}
