<?php

namespace App\Repository\Product;

use App\Models\Product;
use App\Services\ProductService;
use App\Services\SpuService;
use Illuminate\Http\Request;

interface ProductRepositoryInterface
{
    public function display($name, $maincategoryid, $categoryid, $subcategoryid, $pagination, $user, $section, $lang, $price1, $price2, $colors, $sizes, $brands);
    public function admindisplay($name, $maincategoryid, $categoryid, $subcategoryid, $pagination);
    public function displaybyid($id, $user, ProductService $service);
    public function create(Request $request, SpuService $service);
    public function notactive($id);
    public function active($id);
}
